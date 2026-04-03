<?php

namespace App\Services\Payments;

use App\Models\BankInformation;
use App\Models\PayrollPayment;
use App\Models\PayrollRun;
use App\Models\PayrollRunEmployee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayrollPaymentService
{
    public function __construct(private readonly PayrollProviderManager $providers)
    {
    }

    public function payRun(
        PayrollRun $run,
        string $provider,
        string $accountSource = 'primary',
        ?array $userIds = null,
        string $paymentType = 'salary',
        ?string $paymentNote = null,
    ): array {
        $query = PayrollRunEmployee::query()->where('payroll_run_id', $run->id);
        if (! empty($userIds)) {
            $query->whereIn('user_id', $userIds);
        }

        $rows = $query->get();
        $created = 0;
        $paid = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $payment = $this->createFromRunEmployee(
                    row: $row,
                    provider: $provider,
                    accountSource: $accountSource,
                    paymentType: $paymentType,
                    paymentNote: $paymentNote,
                );
                $created++;

                $result = $this->processPayment($payment);
                if ($result['success']) {
                    $paid++;
                } else {
                    $failed++;
                    $errors[] = $row->employee_name . ': ' . ($result['message'] ?? 'Failed');
                }
            } catch (\Throwable $exception) {
                $failed++;
                $errors[] = ($row->employee_name ?: $row->user_id) . ': ' . $exception->getMessage();
            }
        }

        return [
            'created' => $created,
            'paid' => $paid,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    public function createManual(
        string $userId,
        ?int $payrollRunId,
        string $provider,
        string $accountSource,
        float $amount,
        string $currency = 'NGN',
        string $paymentType = 'salary',
        ?string $paymentNote = null,
    ): PayrollPayment {
        $user = User::query()->where('user_id', $userId)->firstOrFail();
        $bank = BankInformation::query()->where('user_id', $userId)->first();
        [$bankName, $accountNumber] = $this->resolveAccount($bank, $accountSource);

        if (blank($accountNumber)) {
            throw new \RuntimeException('Selected account details are not configured for this employee.');
        }

        return DB::transaction(function () use ($userId, $user, $payrollRunId, $provider, $accountSource, $bankName, $accountNumber, $amount, $currency, $paymentType, $paymentNote): PayrollPayment {
            return PayrollPayment::query()->create([
                'payroll_run_id' => $payrollRunId,
                'user_id' => $userId,
                'employee_name' => $user->name,
                'provider' => strtolower($provider),
                'account_source' => strtolower($accountSource),
                'payment_type' => $this->normalizePaymentType($paymentType),
                'payment_note' => $this->sanitizePaymentNote($paymentNote),
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_name' => $user->name,
                'amount' => round($amount, 2),
                'currency' => strtoupper($currency),
                'status' => 'pending',
                'idempotency_key' => (string) Str::uuid(),
                'requested_by_user_id' => Auth::user()?->user_id,
            ]);
        });
    }

    public function processPayment(PayrollPayment $payment): array
    {
        $payment->update(['status' => 'processing']);
        $accountProfile = $this->resolveAccountProfileForPayment($payment);

        if (blank($accountProfile['account_number'])) {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => 'Selected account number is missing.',
                'processed_at' => now(),
            ]);

            return ['success' => false, 'message' => 'Selected account number is missing.'];
        }

        if (blank($accountProfile['bank_name'])) {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => 'Selected bank name is missing.',
                'processed_at' => now(),
            ]);

            return ['success' => false, 'message' => 'Selected bank name is missing.'];
        }

        $payload = [
            'reference' => $payment->idempotency_key ?: (string) Str::uuid(),
            'amount' => (float) $payment->amount,
            'amount_kobo' => (int) round(((float) $payment->amount) * 100),
            'currency' => $payment->currency ?: 'NGN',
            'narration' => $this->buildNarration($payment),
            'description' => $this->buildNarration($payment),
            'remark' => $this->buildNarration($payment),
            'recipient' => [
                'name' => $payment->account_name ?: $payment->employee_name,
                'account_number' => $accountProfile['account_number'],
                'bank_name' => $accountProfile['bank_name'],
                'bank_code' => $accountProfile['bank_code'],
                'account_source' => $payment->account_source,
            ],
            'meta' => [
                'user_id' => $payment->user_id,
                'payroll_run_id' => $payment->payroll_run_id,
                'payroll_run_employee_id' => $payment->payroll_run_employee_id,
                'payment_type' => $payment->payment_type ?: 'salary',
                'payment_note' => $payment->payment_note,
            ],
        ];

        $payment->update(['request_payload' => $payload]);

        $result = $this->providers->driver($payment->provider)->transfer($payload);

        if ($result->success) {
            $payment->update([
                'status' => 'paid',
                'provider_reference' => $result->reference,
                'provider_response' => $result->raw,
                'failure_reason' => null,
                'processed_at' => now(),
            ]);

            return ['success' => true, 'message' => $result->message];
        }

        $payment->update([
            'status' => 'failed',
            'provider_reference' => $result->reference,
            'provider_response' => $result->raw,
            'failure_reason' => $result->message,
            'processed_at' => now(),
        ]);

        return ['success' => false, 'message' => $result->message];
    }

    public function createFromRunEmployee(
        PayrollRunEmployee $row,
        string $provider,
        string $accountSource,
        string $paymentType = 'salary',
        ?string $paymentNote = null,
    ): PayrollPayment
    {
        $bank = BankInformation::query()->where('user_id', $row->user_id)->first();
        [$bankName, $accountNumber] = $this->resolveAccount($bank, $accountSource);

        if (blank($accountNumber)) {
            throw new \RuntimeException('No ' . $accountSource . ' bank account configured.');
        }

        $amount = (float) ($row->total_paid ?? $row->net_salary ?? 0);
        if ($amount <= 0) {
            throw new \RuntimeException('Payment amount must be greater than 0.');
        }

        return PayrollPayment::query()->create([
            'payroll_run_id' => $row->payroll_run_id,
            'payroll_run_employee_id' => $row->id,
            'user_id' => $row->user_id,
            'employee_name' => $row->employee_name,
            'provider' => strtolower($provider),
            'account_source' => strtolower($accountSource),
            'payment_type' => $this->normalizePaymentType($paymentType),
            'payment_note' => $this->sanitizePaymentNote($paymentNote),
            'bank_name' => $bankName,
            'account_number' => $accountNumber,
            'account_name' => $row->employee_name,
            'amount' => round($amount, 2),
            'currency' => 'NGN',
            'status' => 'pending',
            'idempotency_key' => (string) Str::uuid(),
            'requested_by_user_id' => Auth::user()?->user_id,
        ]);
    }

    /**
     * @return array<string,string>
     */
    public function accountOptionsForUser(?string $userId): array
    {
        if (blank($userId)) {
            return [];
        }

        $bank = BankInformation::query()->where('user_id', $userId)->first();

        if (! $bank) {
            return [];
        }

        $primaryBank = (string) ($bank->primary_bank_name ?: $bank->bank_name);
        $primaryAccount = (string) ($bank->primary_bank_account_no ?: $bank->bank_account_no);
        $secondaryBank = (string) ($bank->secondary_bank_name ?: '');
        $secondaryAccount = (string) ($bank->secondary_bank_account_no ?: '');

        $options = [];

        if ($primaryAccount !== '') {
            $options['primary'] = sprintf(
                'Primary (%s - %s)',
                $primaryBank !== '' ? $primaryBank : 'Bank',
                $this->maskAccountNumber($primaryAccount),
            );
        }

        if ($secondaryAccount !== '') {
            $options['secondary'] = sprintf(
                'Secondary (%s - %s)',
                $secondaryBank !== '' ? $secondaryBank : 'Bank',
                $this->maskAccountNumber($secondaryAccount),
            );
        }

        return $options;
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private function resolveAccount(?BankInformation $bank, string $accountSource): array
    {
        if (! $bank) {
            return [null, null];
        }

        $source = strtolower(trim($accountSource));

        if ($source === 'secondary') {
            return [
                $bank->secondary_bank_name,
                $bank->secondary_bank_account_no,
            ];
        }

        return [
            $bank->primary_bank_name ?: $bank->bank_name,
            $bank->primary_bank_account_no ?: $bank->bank_account_no,
        ];
    }

    /**
     * @return array{bank_name:?string,account_number:?string,bank_code:?string}
     */
    private function resolveAccountProfileForPayment(PayrollPayment $payment): array
    {
        $bank = BankInformation::query()->where('user_id', $payment->user_id)->first();

        if (! $bank) {
            return [
                'bank_name' => $payment->bank_name,
                'account_number' => $payment->account_number,
                'bank_code' => null,
            ];
        }

        $source = strtolower((string) $payment->account_source);

        if ($source === 'secondary') {
            $accountNumber = $bank->secondary_bank_account_no ?: $payment->account_number;
            $bankCode = $this->normalizeBankCode($bank->secondary_ifsc_code ?: null, $accountNumber);

            return [
                'bank_name' => $bank->secondary_bank_name ?: $payment->bank_name,
                'account_number' => $accountNumber,
                // We use existing IFSC field as provider bank code for payout APIs.
                'bank_code' => $bankCode,
            ];
        }

        $accountNumber = $bank->primary_bank_account_no ?: $bank->bank_account_no ?: $payment->account_number;
        $bankCode = $this->normalizeBankCode($bank->primary_ifsc_code ?: $bank->ifsc_code ?: null, $accountNumber);

        return [
            'bank_name' => $bank->primary_bank_name ?: $bank->bank_name ?: $payment->bank_name,
            'account_number' => $accountNumber,
            // We use existing IFSC field as provider bank code for payout APIs.
            'bank_code' => $bankCode,
        ];
    }

    private function maskAccountNumber(string $accountNumber): string
    {
        $value = trim($accountNumber);
        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', max(0, $length - 4)) . substr($value, -4);
    }

    private function normalizeBankCode(?string $bankCode, ?string $accountNumber): ?string
    {
        $code = trim((string) $bankCode);
        $account = preg_replace('/\D+/', '', (string) $accountNumber);

        if ($code === '') {
            return null;
        }

        $digitsOnly = preg_replace('/\D+/', '', $code);
        if ($digitsOnly !== '' && $digitsOnly === $account) {
            return null;
        }

        // Nigerian bank codes are typically short numeric codes (often 3-6 digits).
        if ($digitsOnly !== '' && strlen($digitsOnly) >= 3 && strlen($digitsOnly) <= 6) {
            return $digitsOnly;
        }

        // Avoid sending account-like long numeric values as bank codes.
        if ($digitsOnly !== '' && strlen($digitsOnly) >= 9) {
            return null;
        }

        return $code;
    }

    private function buildNarration(PayrollPayment $payment): string
    {
        $label = $this->paymentTypeLabel((string) $payment->payment_type);
        $employee = (string) ($payment->employee_name ?: $payment->user_id);
        $base = sprintf('%s payout for %s', $label, $employee);
        $note = $this->sanitizePaymentNote($payment->payment_note);

        return $note ? ($base . ' - ' . $note) : $base;
    }

    private function normalizePaymentType(string $value): string
    {
        $type = strtolower(trim($value));
        $allowed = array_keys($this->paymentTypeOptions());

        return in_array($type, $allowed, true) ? $type : 'other';
    }

    private function sanitizePaymentNote(?string $note): ?string
    {
        if ($note === null) {
            return null;
        }

        $value = trim($note);

        return $value === '' ? null : mb_substr($value, 0, 500);
    }

    /**
     * @return array<string,string>
     */
    public function paymentTypeOptions(): array
    {
        return [
            'salary' => 'Salary',
            'salary_advance' => 'Salary Advance',
            'iou' => 'IOU',
            'reimbursement' => 'Reimbursement',
            'bonus' => 'Bonus',
            'other' => 'Other',
        ];
    }

    private function paymentTypeLabel(string $type): string
    {
        return $this->paymentTypeOptions()[$this->normalizePaymentType($type)] ?? 'Other';
    }
}
