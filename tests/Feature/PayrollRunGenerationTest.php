<?php

namespace Tests\Feature;

use App\Models\PayrollPolicySet;
use App\Models\PayrollRun;
use App\Models\BankInformation;
use App\Models\Payslip;
use App\Models\PayslipImportBatch;
use App\Models\StaffSalary;
use App\Models\User;
use App\Services\Payroll\PayslipImportService;
use App\Services\Payroll\PayrollRunGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PayrollRunGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_run_generation_creates_run_lines_and_payslip(): void
    {
        $employee = User::create([
            'name' => 'Payroll Test Employee',
            'email' => 'payroll-test-employee@example.com',
            'join_date' => now()->format('Y-m-d H:i:s'),
            'last_login' => now()->format('Y-m-d H:i:s'),
            'phone_number' => '+2348000000000',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Analyst',
            'department' => 'Finance',
            'password' => Hash::make('password'),
        ]);

        StaffSalary::query()->create([
            'name' => $employee->name,
            'user_id' => $employee->user_id,
            'salary' => '300000',
            'tax_station' => 'Lagos',
            'worked_days' => 20,
            'total_working_days' => 22,
            'unpaid_days' => '2',
            'salary_advance' => '10000',
            'kpi_other_deductions' => '5000',
            'annual_rent' => '2400000',
            'non_taxable_reimbursement' => '50000',
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
        ]);

        BankInformation::query()->create([
            'user_id' => $employee->user_id,
            'primary_bank_name' => 'Kuda',
            'primary_bank_account_no' => '8106206063',
        ]);

        $policy = PayrollPolicySet::query()->create([
            'name' => 'Nigeria Policy 2026',
            'code' => 'NG-2026-V1',
            'country_code' => 'NG',
            'currency_code' => 'NGN',
            'effective_from' => '2026-01-01',
            'is_active' => true,
            'settings' => null,
        ]);

        $run = PayrollRun::query()->create([
            'payroll_policy_set_id' => $policy->id,
            'run_code' => 'RUN-2026-01',
            'period_year' => 2026,
            'period_month' => 1,
            'period_start' => '2026-01-01',
            'period_end' => '2026-01-31',
            'default_worked_days' => 21,
            'default_total_working_days' => 21,
            'status' => 'draft',
        ]);

        $generator = app(PayrollRunGeneratorService::class);
        $result = $generator->generate($run);

        $this->assertSame(1, $result['generated']);

        $run->refresh();
        $this->assertSame('calculated', $run->status);
        $this->assertNotNull($run->calculated_at);

        $this->assertDatabaseHas('payroll_run_employees', [
            'payroll_run_id' => $run->id,
            'user_id' => $employee->user_id,
            'tax_station' => 'Lagos',
        ]);

        $runEmployee = \App\Models\PayrollRunEmployee::query()
            ->where('payroll_run_id', $run->id)
            ->where('user_id', $employee->user_id)
            ->firstOrFail();

        $this->assertSame(20, (int) data_get($runEmployee->input_payload, 'worked_days'));
        $this->assertSame(22, (int) data_get($runEmployee->input_payload, 'total_working_days'));
        $this->assertSame(2.0, (float) data_get($runEmployee->input_payload, 'unpaid_days'));
        $this->assertSame(10000.0, (float) data_get($runEmployee->input_payload, 'salary_advance'));
        $this->assertSame(5000.0, (float) data_get($runEmployee->input_payload, 'kpi_other_deductions'));

        $this->assertDatabaseHas('payroll_line_items', [
            'line_type' => 'deduction',
            'code' => 'PAYE',
        ]);

        $this->assertDatabaseHas('payslips', [
            'payroll_run_id' => $run->id,
            'user_id' => $employee->user_id,
            'period_year' => 2026,
            'period_month' => 1,
            'source' => 'generated',
        ]);

        $generatedPayslip = \App\Models\Payslip::query()
            ->where('payroll_run_id', $run->id)
            ->where('user_id', $employee->user_id)
            ->firstOrFail();

        $this->assertSame('8106206063', (string) data_get($generatedPayslip->payload, 'employee.account_number'));
    }

    public function test_employee_can_access_self_service_payslip_portal(): void
    {
        $employee = User::create([
            'name' => 'Payslip Portal Employee',
            'email' => 'payslip-portal-employee@example.com',
            'join_date' => now()->format('Y-m-d H:i:s'),
            'last_login' => now()->format('Y-m-d H:i:s'),
            'phone_number' => '+2348111111111',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Associate',
            'department' => 'Operations',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($employee)
            ->get('/my/payslips')
            ->assertOk()
            ->assertSee('My Payslips');
    }

    public function test_employee_can_download_generated_payslip_pdf(): void
    {
        $employee = User::create([
            'name' => 'Payslip Download Employee',
            'email' => 'payslip-download-employee@example.com',
            'join_date' => now()->format('Y-m-d H:i:s'),
            'last_login' => now()->format('Y-m-d H:i:s'),
            'phone_number' => '+2348333333333',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Designer',
            'department' => 'Product',
            'password' => Hash::make('password'),
        ]);

        $payslip = Payslip::query()->create([
            'user_id' => $employee->user_id,
            'period_year' => 2026,
            'period_month' => 1,
            'source' => 'generated',
            'payload' => [
                'employee' => [
                    'employee_name' => $employee->name,
                    'designation' => 'Designer',
                    'department' => 'Product',
                    'tax_station' => 'Lagos',
                    'account_number' => '8106206063',
                ],
                'input' => [
                    'monthly_gross' => 125000,
                    'worked_days' => 21,
                    'total_working_days' => 21,
                    'unpaid_days' => 0,
                    'salary_advance' => 0,
                    'kpi_other_deductions' => 0,
                    'annual_rent' => 200000,
                    'non_taxable_reimbursement' => 0,
                ],
                'computed' => [
                    'daily_wage' => 5952.380952,
                    'total_taxable_earnings' => 125000,
                    'monthly_paye' => 8250,
                    'unpaid_time_deduction' => 0,
                    'total_deductions' => 8250,
                    'net_salary' => 116750,
                    'total_paid' => 116750,
                    'rent_relief' => 40000,
                    'annual_taxable' => 660000,
                ],
            ],
            'issued_at' => now(),
            'published_at' => now(),
            'is_locked' => true,
        ]);

        $this->actingAs($employee)
            ->get(route('my/payslips/download', $payslip))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_employee_cannot_download_another_users_payslip_pdf(): void
    {
        $owner = User::create([
            'name' => 'Payslip Owner Employee',
            'email' => 'payslip-owner-employee@example.com',
            'join_date' => now()->subMinute()->format('Y-m-d H:i:s'),
            'last_login' => now()->subSeconds(30)->format('Y-m-d H:i:s'),
            'phone_number' => '+2348444444444',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Officer',
            'department' => 'Finance',
            'password' => Hash::make('password'),
        ]);

        $otherEmployee = User::create([
            'name' => 'Different Employee',
            'email' => 'different-employee@example.com',
            'join_date' => now()->format('Y-m-d H:i:s'),
            'last_login' => now()->addSeconds(30)->format('Y-m-d H:i:s'),
            'phone_number' => '+2348555555555',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Officer',
            'department' => 'Finance',
            'password' => Hash::make('password'),
        ]);

        $payslip = Payslip::query()->create([
            'user_id' => $owner->user_id,
            'period_year' => 2026,
            'period_month' => 1,
            'source' => 'generated',
            'payload' => [
                'employee' => ['employee_name' => $owner->name],
                'input' => ['monthly_gross' => 100000],
                'computed' => ['total_taxable_earnings' => 100000],
            ],
        ]);

        $this->actingAs($otherEmployee)
            ->get(route('my/payslips/download', $payslip))
            ->assertForbidden();
    }

    public function test_payslip_import_batch_processes_csv_and_creates_imported_payslip(): void
    {
        Storage::fake('local');

        $employee = User::create([
            'name' => 'Joy Agbose-Akinwole',
            'email' => 'joy-import@example.com',
            'join_date' => now()->format('Y-m-d H:i:s'),
            'last_login' => now()->format('Y-m-d H:i:s'),
            'phone_number' => '+2348222222222',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Content Manager',
            'department' => 'Digital Experience Design',
            'password' => Hash::make('password'),
        ]);

        $csv = implode("\n", [
            'Employee Name,Account No,Tax Station,Pay Period (MMM-YY),Date of Joining,Designation,Department,Monthly Gross (₦),Worked Days,Total Working Days,Unpaid Days,Salary Advance (₦),KPI/Other Deductions (₦),Annual Rent (₦),Non-Taxable Reimbursement (₦)',
            'Joy Agbose-Akinwole,8106206063,Lagos,Jan-26,2024-11-11,Content & Social Media Experience Manager,Digital Experience Design,300000,21,21,0,0,0,0,50000',
        ]);

        $path = 'payslip-imports/test-import.csv';
        Storage::disk('local')->put($path, $csv);

        $batch = PayslipImportBatch::query()->create([
            'title' => 'Jan 2026 Backfill',
            'period_year' => 2026,
            'period_month' => 1,
            'source_file_name' => 'test-import.csv',
            'import_file_path' => $path,
            'uploaded_by_user_id' => $employee->user_id,
            'status' => 'draft',
        ]);

        $service = app(PayslipImportService::class);
        $result = $service->process($batch);

        $this->assertSame(1, $result['processed']);
        $this->assertSame(0, $result['failed']);

        $this->assertDatabaseHas('payslips', [
            'user_id' => $employee->user_id,
            'period_year' => 2026,
            'period_month' => 1,
            'source' => 'imported',
        ]);

        $this->assertDatabaseHas('payslip_import_rows', [
            'payslip_import_batch_id' => $batch->id,
            'user_id' => $employee->user_id,
            'row_status' => 'imported',
        ]);
    }
}
