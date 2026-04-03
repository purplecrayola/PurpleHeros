<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanySettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FilamentAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_access_filament_resources(): void
    {
        $admin = User::where('email', 'admin@purplecrayola.com')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/admin')->assertOk();
        $this->get('/admin/departments')->assertOk();
        $this->get('/admin/position-types')->assertOk();
        $this->get('/admin/employees')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/company-settings')->assertRedirect();
        $settings = CompanySettings::current();
        $this->get('/admin/company-settings/' . $settings->id . '/edit')->assertOk();
        $this->get('/admin/holidays')->assertOk();
        $this->get('/admin/leaves-admins')->assertOk();
        $this->get('/admin/attendance-records')->assertOk();
        $this->get('/admin/timesheet-entries')->assertOk();
        $this->get('/admin/overtime-entries')->assertOk();
        $this->get('/admin/staff-salaries')->assertOk();
        $this->get('/admin/payroll-policy-sets')->assertOk();
        $this->get('/admin/payroll-runs')->assertOk();
        $this->get('/admin/payroll-payments')->assertOk();
        $this->get('/admin/payslip-import-batches')->assertOk();
        $this->get('/admin/payslips')->assertOk();
        $this->get('/admin/reports-hub')->assertOk();
        $this->get('/admin/export-audits')->assertOk();
    }

    public function test_non_admin_cannot_access_filament_panel(): void
    {
        $employee = User::create([
            'name' => 'Panel Restricted User',
            'email' => 'panel-restricted@example.com',
            'join_date' => now()->addMinutes(20)->format('Y-m-d H:i:s'),
            'last_login' => now()->addMinutes(21)->format('Y-m-d H:i:s'),
            'phone_number' => '+2348333333333',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Coordinator',
            'department' => 'Operations',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($employee);

        $this->get('/admin')->assertForbidden();
        $this->get('/admin/departments')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/holidays')->assertForbidden();
        $this->get('/admin/staff-salaries')->assertForbidden();
        $this->get('/admin/payroll-policy-sets')->assertForbidden();
        $this->get('/admin/payroll-runs')->assertForbidden();
        $this->get('/admin/payroll-payments')->assertForbidden();
        $this->get('/admin/payslip-import-batches')->assertForbidden();
        $this->get('/admin/payslips')->assertForbidden();
        $this->get('/admin/reports-hub')->assertForbidden();
        $this->get('/admin/export-audits')->assertForbidden();
    }
}
