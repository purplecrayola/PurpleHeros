<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_page_loads_without_public_registration_cta(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Sign in to PurpleHeros');
        $response->assertSee('Secure access to people operations, payroll, leave, and internal workflow tools.');
        $response->assertSee('Use your issued work credentials.');
        $response->assertDontSee('Don\'t have an account yet?');
    }

    public function test_register_redirects_back_to_login_for_guests(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('login'));
    }

    public function test_admin_dashboard_entry_redirects_to_filament_admin(): void
    {
        $admin = User::where('email', 'admin@purplecrayola.com')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/home')->assertRedirect('/admin');
        $this->get('/admin')->assertOk();
    }

    public function test_admin_can_open_core_product_pages(): void
    {
        $admin = User::where('email', 'admin@purplecrayola.com')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/userManagement')->assertRedirect('/admin/users');
        $this->get('/change/password')->assertOk()->assertSee('Change Password');
        $this->get('/all/employee/card')->assertRedirect('/admin/employees');
        $this->get('/form/holidays/new')->assertRedirect('/admin/holidays');
        $this->get('/form/leaves/new')->assertRedirect('/admin/leaves-admins');
        $this->get('/form/leavesettings/page')->assertRedirect('/admin/leave-settings');
        $this->get('/admin/leave-balance-report')->assertOk();
        $this->get('/attendance/page')->assertRedirect('/admin/attendance-records');
        $this->get('/form/shiftscheduling/page')->assertRedirect('/admin/shift-scheduling');
        $this->get('/form/shiftlist/page')->assertRedirect('/admin/shift-scheduling');
        $this->get('/form/timesheet/page')->assertRedirect('/admin/timesheet-entries');
        $this->get('/form/overtime/page')->assertRedirect('/admin/overtime-entries');
        $this->get('/form/salary/page')->assertRedirect('/admin/staff-salaries');
        $this->get('/company/settings/page')->assertRedirect('/admin/company-settings');
        $this->get('/localization/page')->assertRedirect('/admin/company-settings');
        $this->get('/email/settings/page')->assertRedirect('/admin/email-settings');
        $this->get('/salary/settings/page')->assertRedirect('/admin/payroll-defaults');
        $this->get('/roles/permissions/page')->assertRedirect('/admin/roles-permissions');
        $this->get('/form/performance/page')->assertRedirect('/admin/performance-hub');
        $this->get('/form/performance/indicator/page')->assertRedirect('/admin/performance-hub');
        $this->get('/form/performance/appraisal/page')->assertRedirect('/admin/performance-hub');
        $this->get('/form/training/list/page')->assertRedirect('/admin/trainings');
        $this->get('/form/trainers/list/page')->assertRedirect('/admin/trainers');
        $this->get('/form/training/type/list/page')->assertRedirect('/admin/training-types');
    }

    public function test_admin_can_open_shipped_hr_reports(): void
    {
        $admin = User::where('email', 'admin@purplecrayola.com')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/form/employee/reports/page')->assertRedirect('/admin/reports-hub');
        $this->get('/form/leave/reports/page')->assertRedirect('/admin/reports-hub');
        $this->get('/form/daily/reports/page')->assertRedirect('/admin/reports-hub');
        $this->get('/form/expense/reports/page')->assertRedirect('/admin/reports-hub');
        $this->get('/form/invoice/reports/page')->assertRedirect('/admin/reports-hub');
    }

    public function test_non_admin_is_limited_to_self_service_surfaces(): void
    {
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'join_date' => now()->addMinutes(10)->format('Y-m-d H:i:s'),
            'last_login' => now()->addMinutes(11)->format('Y-m-d H:i:s'),
            'phone_number' => '+2348222222222',
            'status' => 'Active',
            'role_name' => 'Employee',
            'avatar' => null,
            'position' => 'Coordinator',
            'department' => 'Operations',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($employee);

        $this->get('/em/dashboard')->assertOk();
        $this->get('/home')->assertForbidden();
        $this->get('/attendance/employee/page')->assertOk();
        $this->get('/form/leavesemployee/new')->assertOk();
        $this->get('/employee/profile/' . $employee->user_id)->assertOk();
        $this->get('/change/password')->assertOk()->assertSee('Change Password');

        $this->get('/all/employee/card')->assertRedirect('/admin/employees');
        $this->get('/form/salary/page')->assertRedirect('/admin/staff-salaries');
        $this->get('/form/employee/reports/page')->assertRedirect('/admin/reports-hub');
        $this->get('/form/holidays/new')->assertRedirect('/admin/holidays');

        $this->get('/admin/employees')->assertForbidden();
        $this->get('/admin/staff-salaries')->assertForbidden();
        $this->get('/admin/reports-hub')->assertForbidden();
        $this->get('/admin/holidays')->assertForbidden();
    }
}
