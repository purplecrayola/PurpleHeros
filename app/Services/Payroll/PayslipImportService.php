<?php

namespace App\Services\Payroll;

use App\Models\Payslip;
use App\Models\PayslipImportBatch;
use App\Models\PayslipImportRow;
use App\Models\StaffSalary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PayslipImportService
{
    public function __construct(private readonly PayrollCalculatorService $calculator)
    {
    }

    public function process(PayslipImportBatch $batch): array
    {
        if (! $batch->import_file_path) {
            return ['processed' => 0, 'failed' => 0, 'errors' => ['No import file attached to this batch.']];
        }

        $fullPath = $this->resolveReadableImportPath((string) $batch->import_file_path);

        if ($fullPath === null || ! is_file($fullPath)) {
            return ['processed' => 0, 'failed' => 0, 'errors' => ['Import file was not found on disk.']];
        }

        $sheets = Excel::toArray([], $fullPath);
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return ['processed' => 0, 'failed' => 0, 'errors' => ['No data rows found in import file.']];
        }

        $headers = $this->normalizeHeaders((array) $rows[0]);
        $processed = 0;
        $failed = 0;
        $errors = [];

        DB::transaction(function () use ($batch, $rows, $headers, &$processed, &$failed, &$errors): void {
            PayslipImportRow::query()->where('payslip_import_batch_id', $batch->id)->delete();

            for ($i = 1; $i < count($rows); $i++) {
                $raw = (array) $rows[$i];

                if ($this->isEmptyRow($raw)) {
                    continue;
                }

                $mapped = $this->mapRow($headers, $raw);

                $employeeName = trim((string) ($mapped['employee_name'] ?? ''));
                if ($employeeName === '') {
                    continue;
                }

                if ($this->shouldSkipMappedRow($mapped)) {
                    continue;
                }

                $user = $this->findUser($employeeName) ?? $this->createPlaceholderUser($mapped);

                [$year, $month] = $this->resolvePeriod($mapped['pay_period'] ?? null, (int) ($batch->period_year ?? 0), (int) ($batch->period_month ?? 0));

                $input = [
                    'monthly_gross' => $this->money($mapped['monthly_gross'] ?? 0),
                    'worked_days' => (int) ($mapped['worked_days'] ?? 0),
                    'total_working_days' => (int) ($mapped['total_working_days'] ?? 0),
                    'unpaid_days' => $this->money($mapped['unpaid_days'] ?? 0),
                    'salary_advance' => $this->money($mapped['salary_advance'] ?? 0),
                    'kpi_other_deductions' => $this->money($mapped['kpi_other_deductions'] ?? 0),
                    'annual_rent' => $this->money($mapped['annual_rent'] ?? 0),
                    'non_taxable_reimbursement' => $this->money($mapped['non_taxable_reimbursement'] ?? 0),
                ];

                $computed = $this->calculator->calculate($input, [
                    'tax_free_threshold' => 800000,
                    'rent_relief_percent' => 20,
                    'rent_relief_cap' => 500000,
                ]);

                $payload = [
                    'employee' => [
                        'employee_name' => $employeeName,
                        'designation' => $mapped['designation'] ?? null,
                        'department' => $mapped['department'] ?? null,
                        'tax_station' => $mapped['tax_station'] ?? null,
                        'account_number' => $mapped['account_no'] ?? null,
                    ],
                    'input' => $input,
                    'computed' => $computed,
                ];

                $rowStatus = 'imported';
                $errorMessage = null;

                if ($year === null || $month === null) {
                    $rowStatus = 'failed';
                    $errorMessage = 'Unable to determine payroll period from pay period column or batch period defaults.';
                    $failed++;
                    $errors[] = 'Row '.($i + 1).': '.$errorMessage;
                } else {
                    Payslip::query()->updateOrCreate(
                        [
                            'user_id' => (string) $user->user_id,
                            'period_year' => $year,
                            'period_month' => $month,
                            'source' => 'imported',
                        ],
                        [
                            'payroll_run_id' => $batch->payroll_run_id,
                            'file_path' => null,
                            'payload' => $payload,
                            'issued_at' => now(),
                            'published_at' => now(),
                            'is_locked' => true,
                        ]
                    );

                    $processed++;
                }

                PayslipImportRow::query()->create([
                    'payslip_import_batch_id' => $batch->id,
                    'user_id' => $user?->user_id,
                    'employee_name' => $employeeName,
                    'period_year' => $year,
                    'period_month' => $month,
                    'gross_salary' => $computed['total_taxable_earnings'],
                    'total_deductions' => $computed['total_deductions'],
                    'net_salary' => $computed['net_salary'],
                    'payload' => $payload,
                    'error_message' => $errorMessage,
                    'row_status' => $rowStatus,
                ]);
            }

            $batch->update([
                'status' => $failed > 0 ? 'failed' : 'processed',
                'processed_rows' => $processed,
                'failed_rows' => $failed,
            ]);
        });

        return [
            'processed' => $processed,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header): string {
            $header = is_string($header) ? $header : (string) $header;
            $header = strtolower(trim($header));
            $header = preg_replace('/\s+/', ' ', $header) ?? $header;

            return str_replace(['(₦)', '(mmm-yy)'], '', $header);
        }, $headers);
    }

    private function mapRow(array $headers, array $row): array
    {
        $result = [];

        foreach ($headers as $index => $header) {
            $result[$header] = $row[$index] ?? null;
        }

        return [
            'employee_name' => $result['employee name'] ?? null,
            'account_no' => $result['account no'] ?? null,
            'tax_station' => $result['tax station'] ?? null,
            'pay_period' => $result['pay period'] ?? null,
            'date_of_joining' => $result['date of joining'] ?? null,
            'designation' => $result['designation'] ?? null,
            'department' => $result['department'] ?? null,
            'monthly_gross' => $result['monthly gross'] ?? 0,
            'worked_days' => $result['worked days'] ?? 0,
            'total_working_days' => $result['total working days'] ?? 0,
            'unpaid_days' => $result['unpaid days'] ?? 0,
            'salary_advance' => $result['salary advance'] ?? 0,
            'kpi_other_deductions' => $result['kpi/other deductions'] ?? 0,
            'annual_rent' => $result['annual rent'] ?? 0,
            'non_taxable_reimbursement' => $result['non-taxable reimbursement'] ?? 0,
        ];
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function shouldSkipMappedRow(array $mapped): bool
    {
        $employeeName = mb_strtolower(trim((string) ($mapped['employee_name'] ?? '')));

        if ($employeeName === '' || str_starts_with($employeeName, 'notes:') || str_starts_with($employeeName, '•')) {
            return true;
        }

        return false;
    }

    private function findUser(string $employeeName): ?User
    {
        if ($employeeName === '') {
            return null;
        }

        $needle = mb_strtolower(trim(preg_replace('/\s+/', ' ', $employeeName) ?? $employeeName));

        return User::query()
            ->get()
            ->first(function (User $user) use ($needle): bool {
                $name = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $user->name) ?? (string) $user->name));

                return $name === $needle;
            });
    }

    private function createPlaceholderUser(array $mapped): User
    {
        $name = trim((string) ($mapped['employee_name'] ?? 'Imported Employee'));
        $slug = Str::slug($name);
        $suffix = random_int(1000, 9999);
        $email = "{$slug}.{$suffix}@import.local";

        while (User::query()->where('email', $email)->exists()) {
            $suffix = random_int(1000, 9999);
            $email = "{$slug}.{$suffix}@import.local";
        }

        $now = now();
        $joinDate = $now->copy()->addSeconds(random_int(1, 200000))->format('Y-m-d H:i:s.u');
        $lastLogin = $now->copy()->addSeconds(random_int(200001, 400000))->format('Y-m-d H:i:s.u');

        while (User::query()->where('join_date', $joinDate)->orWhere('last_login', $lastLogin)->exists()) {
            $joinDate = $now->copy()->addSeconds(random_int(1, 200000))->format('Y-m-d H:i:s.u');
            $lastLogin = $now->copy()->addSeconds(random_int(200001, 400000))->format('Y-m-d H:i:s.u');
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'join_date' => $joinDate,
            'last_login' => $lastLogin,
            'phone_number' => null,
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => $mapped['designation'] ?? null,
            'department' => $mapped['department'] ?? null,
            'password' => Hash::make(Str::random(24)),
        ]);

        StaffSalary::query()->updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'name' => $user->name,
                'salary' => (string) $this->money($mapped['monthly_gross'] ?? 0),
                'basic' => '0',
                'da' => '0',
                'hra' => '0',
                'conveyance' => '0',
                'allowance' => '0',
                'medical_allowance' => '0',
                'tds' => '0',
                'esi' => '0',
                'pf' => '0',
                'leave' => '0',
                'prof_tax' => '0',
                'labour_welfare' => '0',
            ]
        );

        return $user;
    }

    private function resolvePeriod(mixed $periodValue, int $fallbackYear, int $fallbackMonth): array
    {
        $text = trim((string) ($periodValue ?? ''));

        if ($text !== '') {
            try {
                $dt = Carbon::createFromFormat('M-y', $text);

                return [(int) $dt->format('Y'), (int) $dt->format('n')];
            } catch (\Throwable) {
                // fall through to batch defaults
            }

            try {
                $dt = Carbon::parse($text);

                return [(int) $dt->format('Y'), (int) $dt->format('n')];
            } catch (\Throwable) {
                // fall through to defaults
            }
        }

        if ($fallbackYear > 0 && $fallbackMonth > 0) {
            return [$fallbackYear, $fallbackMonth];
        }

        return [null, null];
    }

    private function money(mixed $value): float
    {
        $value = is_string($value) ? str_replace([',', '₦', 'N'], '', $value) : $value;

        return round((float) $value, 2);
    }

    private function resolveReadableImportPath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $response = Http::timeout(30)->get($path);
            if (! $response->ok()) {
                return null;
            }

            $extension = strtolower((string) pathinfo(parse_url($path, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $filename = 'payslip-import-' . now()->format('YmdHis') . '-' . Str::random(8) . ($extension !== '' ? '.' . $extension : '.xlsx');
            $relativePath = 'tmp/' . $filename;
            Storage::disk('local')->put($relativePath, $response->body());

            return Storage::disk('local')->path($relativePath);
        }

        if (is_file($path)) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        return Storage::disk('local')->exists($path)
            ? Storage::disk('local')->path($path)
            : null;
    }
}
