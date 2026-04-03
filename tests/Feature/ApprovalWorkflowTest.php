<?php

namespace Tests\Feature;

use App\Filament\Pages\ReportsHub;
use App\Models\ExportAudit;
use App\Models\LeavesAdmin;
use App\Models\OvertimeEntry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_leave_request_can_be_approved_and_rejected_with_audit_fields(): void
    {
        $admin = User::query()->where('email', 'admin@purplecrayola.com')->firstOrFail();
        $leave = LeavesAdmin::query()->firstOrFail();

        $leave->approveBy($admin);
        $leave->refresh();

        $this->assertSame('Approved', $leave->status);
        $this->assertSame($admin->email, $leave->approved_by);
        $this->assertNotNull($leave->approved_at);

        $leave->rejectBy($admin, 'Policy violation');
        $leave->refresh();

        $this->assertSame('Rejected', $leave->status);
        $this->assertSame($admin->email, $leave->approved_by);
        $this->assertSame('Policy violation', $leave->rejection_reason);
        $this->assertNotNull($leave->approved_at);
    }

    public function test_overtime_entry_can_be_approved_and_rejected_with_audit_fields(): void
    {
        $admin = User::query()->where('email', 'admin@purplecrayola.com')->firstOrFail();
        $entry = OvertimeEntry::query()->firstOrFail();

        $entry->approveBy($admin);
        $entry->refresh();

        $this->assertSame('Approved', $entry->status);
        $this->assertSame($admin->email, $entry->approved_by);
        $this->assertNotNull($entry->approved_at);

        $entry->rejectBy($admin, 'Insufficient justification');
        $entry->refresh();

        $this->assertSame('Rejected', $entry->status);
        $this->assertSame($admin->email, $entry->approved_by);
        $this->assertSame('Insufficient justification', $entry->rejection_reason);
        $this->assertNotNull($entry->approved_at);
    }

    public function test_employee_report_export_creates_audit_log_entry(): void
    {
        $admin = User::query()->where('email', 'admin@purplecrayola.com')->firstOrFail();
        $this->actingAs($admin);

        Livewire::test(ReportsHub::class)
            ->set('reportDate', now()->toDateString())
            ->set('employeeSearch', '')
            ->call('exportEmployees', 'csv');

        $this->assertDatabaseHas('export_audits', [
            'user_email' => $admin->email,
            'report_name' => 'Employee Report',
            'format' => 'csv',
            'filename' => 'employee-report.csv',
        ]);
    }
}
