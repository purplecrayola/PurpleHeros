<?php

namespace Tests\Feature;

use App\Filament\Widgets\AttendanceTrendChart;
use App\Filament\Widgets\HrSnapshotOverview;
use App\Filament\Widgets\LeaveVolumeChart;
use App\Filament\Widgets\OvertimeStatusChart;
use App\Filament\Widgets\PayrollSnapshotOverview;
use App\Filament\Widgets\RoleWelcomePanel;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_reports_analyst_sees_reporting_widgets_but_not_payroll_widget(): void
    {
        $user = User::factory()->create([
            'email' => 'dash-reports-analyst@example.com',
            'role_name' => 'Reports Analyst',
        ]);

        $this->actingAs($user);

        $this->assertTrue(HrSnapshotOverview::canView());
        $this->assertTrue(RoleWelcomePanel::canView());
        $this->assertTrue(AttendanceTrendChart::canView());
        $this->assertTrue(OvertimeStatusChart::canView());
        $this->assertTrue(LeaveVolumeChart::canView());
        $this->assertFalse(PayrollSnapshotOverview::canView());
    }

    public function test_payroll_admin_sees_payroll_widget_and_summary_cards(): void
    {
        $user = User::factory()->create([
            'email' => 'dash-payroll-admin@example.com',
            'role_name' => 'Payroll Admin',
        ]);

        $this->actingAs($user);

        $this->assertTrue(HrSnapshotOverview::canView());
        $this->assertTrue(RoleWelcomePanel::canView());
        $this->assertTrue(PayrollSnapshotOverview::canView());
        $this->assertTrue(AttendanceTrendChart::canView());
        $this->assertTrue(OvertimeStatusChart::canView());
        $this->assertTrue(LeaveVolumeChart::canView());
    }

    public function test_employee_cannot_view_admin_dashboard_widgets(): void
    {
        $user = User::factory()->create([
            'email' => 'dash-employee@example.com',
            'role_name' => 'Employee',
        ]);

        $this->actingAs($user);

        $this->assertFalse(HrSnapshotOverview::canView());
        $this->assertFalse(RoleWelcomePanel::canView());
        $this->assertFalse(PayrollSnapshotOverview::canView());
        $this->assertFalse(AttendanceTrendChart::canView());
        $this->assertFalse(OvertimeStatusChart::canView());
        $this->assertFalse(LeaveVolumeChart::canView());
    }
}
