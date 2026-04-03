<?php

namespace Tests\Feature;

use App\Models\BankInformation;
use App\Models\CompanySettings;
use App\Models\User;
use App\Services\Payments\PayrollPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PayrollPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_options_include_primary_and_secondary_accounts(): void
    {
        $employee = User::create([
            'name' => 'Payment Service User',
            'email' => 'payment-service-user@example.com',
            'join_date' => now()->format('Y-m-d H:i:s'),
            'last_login' => now()->format('Y-m-d H:i:s'),
            'phone_number' => '+2348444444444',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Officer',
            'department' => 'Finance',
            'password' => Hash::make('password'),
        ]);

        BankInformation::query()->create([
            'user_id' => $employee->user_id,
            'primary_bank_name' => 'Access Bank',
            'primary_bank_account_no' => '1234567890',
            'secondary_bank_name' => 'Kuda Microfinance Bank',
            'secondary_bank_account_no' => '9876543210',
        ]);

        $service = app(PayrollPaymentService::class);
        $options = $service->accountOptionsForUser($employee->user_id);

        $this->assertArrayHasKey('primary', $options);
        $this->assertArrayHasKey('secondary', $options);
        $this->assertStringContainsString('Access Bank', $options['primary']);
        $this->assertStringContainsString('Kuda Microfinance Bank', $options['secondary']);
    }

    public function test_manual_payment_uses_selected_secondary_account_details(): void
    {
        CompanySettings::current()->update([
            'kuda_enabled' => true,
            'kuda_sandbox_mode' => true,
        ]);

        $employee = User::create([
            'name' => 'Secondary Account Employee',
            'email' => 'secondary-account-employee@example.com',
            'join_date' => now()->format('Y-m-d H:i:s'),
            'last_login' => now()->format('Y-m-d H:i:s'),
            'phone_number' => '+2348555555555',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Analyst',
            'department' => 'Payroll',
            'password' => Hash::make('password'),
        ]);

        BankInformation::query()->create([
            'user_id' => $employee->user_id,
            'primary_bank_name' => 'First Bank',
            'primary_bank_account_no' => '1111111111',
            'secondary_bank_name' => 'Kuda Bank',
            'secondary_bank_account_no' => '2222222222',
        ]);

        $service = app(PayrollPaymentService::class);

        $payment = $service->createManual(
            userId: $employee->user_id,
            payrollRunId: null,
            provider: 'kuda',
            accountSource: 'secondary',
            amount: 50000,
        );

        $this->assertSame('secondary', $payment->account_source);
        $this->assertSame('2222222222', $payment->account_number);
        $this->assertSame('Kuda Bank', $payment->bank_name);
        $this->assertSame('pending', $payment->status);
    }

    public function test_process_payment_payload_uses_selected_account_bank_code(): void
    {
        CompanySettings::current()->update([
            'opay_enabled' => true,
            'opay_sandbox_mode' => true,
        ]);

        $employee = User::create([
            'name' => 'Payload Mapping Employee',
            'email' => 'payload-mapping-employee@example.com',
            'join_date' => now()->format('Y-m-d H:i:s'),
            'last_login' => now()->format('Y-m-d H:i:s'),
            'phone_number' => '+2348666666666',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Payroll Officer',
            'department' => 'Finance',
            'password' => Hash::make('password'),
        ]);

        BankInformation::query()->create([
            'user_id' => $employee->user_id,
            'primary_bank_name' => 'GTBank',
            'primary_bank_account_no' => '3333333333',
            'primary_ifsc_code' => '058',
        ]);

        $service = app(PayrollPaymentService::class);

        $payment = $service->createManual(
            userId: $employee->user_id,
            payrollRunId: null,
            provider: 'opay',
            accountSource: 'primary',
            amount: 75000,
        );

        $result = $service->processPayment($payment);
        $payment->refresh();

        $this->assertArrayHasKey('success', $result);
        $this->assertSame('058', data_get($payment->request_payload, 'recipient.bank_code'));
        $this->assertSame('3333333333', data_get($payment->request_payload, 'recipient.account_number'));
        $this->assertSame('Salary payout for Payload Mapping Employee', data_get($payment->request_payload, 'narration'));
    }
}
