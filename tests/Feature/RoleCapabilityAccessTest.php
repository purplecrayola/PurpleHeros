<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleCapabilityAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_reports_analyst_can_access_reports_but_not_payroll(): void
    {
        $timestamp = '2001-01-01 01:01:01';

        $user = User::create([
            'name' => 'Reports Analyst',
            'email' => 'reports-analyst@example.com',
            'join_date' => $timestamp,
            'last_login' => $timestamp,
            'phone_number' => '+2348444444444',
            'status' => 'Active',
            'role_name' => 'Reports Analyst',
            'avatar' => null,
            'position' => 'Analyst',
            'department' => 'Operations',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/reports-hub')->assertOk();
        $this->get('/admin/export-audits')->assertOk();
        $this->get('/admin/staff-salaries')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_payroll_admin_can_access_payroll_and_reports_but_not_user_admin(): void
    {
        $timestamp = '2001-01-01 01:01:02';

        $user = User::create([
            'name' => 'Payroll Admin',
            'email' => 'payroll-admin@example.com',
            'join_date' => $timestamp,
            'last_login' => $timestamp,
            'phone_number' => '+2348555555555',
            'status' => 'Active',
            'role_name' => 'Payroll Admin',
            'avatar' => null,
            'position' => 'Payroll Lead',
            'department' => 'Finance',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/staff-salaries')->assertOk();
        $this->get('/admin/reports-hub')->assertOk();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/departments')->assertForbidden();
    }

    public function test_operations_manager_can_access_time_and_attendance_but_not_users_or_payroll(): void
    {
        $timestamp = '2001-01-01 01:01:03';

        $user = User::create([
            'name' => 'Operations Manager',
            'email' => 'ops-manager@example.com',
            'join_date' => $timestamp,
            'last_login' => $timestamp,
            'phone_number' => '+2348666666666',
            'status' => 'Active',
            'role_name' => 'Operations Manager',
            'avatar' => null,
            'position' => 'Ops Manager',
            'department' => 'Operations',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/attendance-records')->assertOk();
        $this->get('/admin/leaves-admins')->assertOk();
        $this->get('/admin/overtime-entries')->assertOk();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/staff-salaries')->assertForbidden();
    }
}
