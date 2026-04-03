<?php

namespace Tests\Feature;

use App\Models\TimesheetEntry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TimesheetWorkedHoursPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_timesheet_allows_worked_hours_within_assigned_plus_four(): void
    {
        $user = User::factory()->create([
            'email' => 'timesheet-policy-ok@example.com',
            'role_name' => 'Employee',
        ]);

        TimesheetEntry::query()->create([
            'user_id' => $user->user_id,
            'work_date' => '2026-04-01',
            'project_name' => 'Policy Test',
            'assigned_hours' => 8,
            'worked_hours' => 12,
            'description' => 'Valid threshold test.',
        ]);

        $this->assertDatabaseHas('timesheet_entries', [
            'user_id' => $user->user_id,
            'assigned_hours' => 8,
            'worked_hours' => 12,
        ]);
    }

    public function test_timesheet_blocks_worked_hours_beyond_assigned_plus_four(): void
    {
        $user = User::factory()->create([
            'email' => 'timesheet-policy-fail@example.com',
            'role_name' => 'Employee',
        ]);

        $this->expectException(ValidationException::class);

        TimesheetEntry::query()->create([
            'user_id' => $user->user_id,
            'work_date' => '2026-04-02',
            'project_name' => 'Policy Test',
            'assigned_hours' => 8,
            'worked_hours' => 13,
            'description' => 'Invalid threshold test.',
        ]);
    }
}
