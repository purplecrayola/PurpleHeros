<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CompanySettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UatRoleAccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_can_access_everything_in_matrix(): void
    {
        $user = User::factory()->create(['role_name' => 'Super Admin']);
        $this->actingAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/company-settings')->assertRedirect();
        $settings = CompanySettings::current();
        $this->get('/admin/company-settings/' . $settings->id . '/edit')->assertOk();
        $this->get('/admin/departments')->assertOk();
        $this->get('/admin/employees')->assertOk();
        $this->get('/admin/attendance-records')->assertOk();
        $this->get('/admin/staff-salaries')->assertOk();
        $this->get('/admin/reports-hub')->assertOk();
    }

    public function test_hr_manager_access_matrix(): void
    {
        $user = User::factory()->create(['role_name' => 'HR Manager']);
        $this->actingAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/departments')->assertOk();
        $this->get('/admin/employees')->assertOk();
        $this->get('/admin/attendance-records')->assertOk();
        $this->get('/admin/leaves-admins')->assertOk();
        $this->get('/admin/overtime-entries')->assertOk();
        $this->get('/admin/reports-hub')->assertOk();

        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/staff-salaries')->assertForbidden();
    }

    public function test_payroll_admin_access_matrix(): void
    {
        $user = User::factory()->create(['role_name' => 'Payroll Admin']);
        $this->actingAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/staff-salaries')->assertOk();
        $this->get('/admin/reports-hub')->assertOk();

        $this->get('/admin/departments')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/attendance-records')->assertForbidden();
    }

    public function test_operations_manager_access_matrix(): void
    {
        $user = User::factory()->create(['role_name' => 'Operations Manager']);
        $this->actingAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/attendance-records')->assertOk();
        $this->get('/admin/leaves-admins')->assertOk();
        $this->get('/admin/overtime-entries')->assertOk();

        $this->get('/admin/reports-hub')->assertForbidden();
        $this->get('/admin/staff-salaries')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_reports_analyst_access_matrix(): void
    {
        $user = User::factory()->create(['role_name' => 'Reports Analyst']);
        $this->actingAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/reports-hub')->assertOk();
        $this->get('/admin/export-audits')->assertOk();

        $this->get('/admin/attendance-records')->assertForbidden();
        $this->get('/admin/staff-salaries')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_employee_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['role_name' => 'Employee']);
        $this->actingAs($user);

        $this->get('/admin')->assertForbidden();
        $this->get('/admin/reports-hub')->assertForbidden();
        $this->get('/admin/attendance-records')->assertForbidden();
    }
}
