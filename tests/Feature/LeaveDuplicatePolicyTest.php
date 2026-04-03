<?php

namespace Tests\Feature;

use App\Models\LeavesAdmin;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LeaveDuplicatePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_exact_duplicate_leave_request_is_blocked(): void
    {
        $user = User::factory()->create([
            'email' => 'leave-dup-user@example.com',
            'role_name' => 'Employee',
        ]);

        LeavesAdmin::query()->create([
            'user_id' => $user->user_id,
            'leave_type' => 'Annual Leave',
            'from_date' => '2026-04-10',
            'to_date' => '2026-04-12',
            'day' => '3',
            'status' => 'Pending',
            'leave_reason' => 'Initial request.',
        ]);

        $this->expectException(ValidationException::class);

        LeavesAdmin::query()->create([
            'user_id' => $user->user_id,
            'leave_type' => 'Annual Leave',
            'from_date' => '2026-04-10',
            'to_date' => '2026-04-12',
            'day' => '3',
            'status' => 'Pending',
            'leave_reason' => 'Duplicate request.',
        ]);
    }

    public function test_overlapping_leave_request_is_blocked_even_for_different_leave_type(): void
    {
        $user = User::factory()->create([
            'email' => 'leave-nondup-user@example.com',
            'role_name' => 'Employee',
        ]);

        LeavesAdmin::query()->create([
            'user_id' => $user->user_id,
            'leave_type' => 'Annual Leave',
            'from_date' => '2026-04-15',
            'to_date' => '2026-04-16',
            'day' => '2',
            'status' => 'Pending',
            'leave_reason' => 'Annual leave request.',
        ]);

        $this->expectException(ValidationException::class);

        LeavesAdmin::query()->create([
            'user_id' => $user->user_id,
            'leave_type' => 'Medical Leave',
            'from_date' => '2026-04-15',
            'to_date' => '2026-04-16',
            'day' => '2',
            'status' => 'Pending',
            'leave_reason' => 'Medical leave request.',
        ]);
    }

    public function test_non_overlapping_leave_requests_are_allowed(): void
    {
        $user = User::factory()->create([
            'email' => 'leave-nooverlap-user@example.com',
            'role_name' => 'Employee',
        ]);

        LeavesAdmin::query()->create([
            'user_id' => $user->user_id,
            'leave_type' => 'Annual Leave',
            'from_date' => '2026-04-20',
            'to_date' => '2026-04-22',
            'day' => '3',
            'status' => 'Pending',
            'leave_reason' => 'Annual leave request.',
        ]);

        LeavesAdmin::query()->create([
            'user_id' => $user->user_id,
            'leave_type' => 'Medical Leave',
            'from_date' => '2026-04-24',
            'to_date' => '2026-04-25',
            'day' => '2',
            'status' => 'Pending',
            'leave_reason' => 'Medical leave request.',
        ]);

        $this->assertSame(
            2,
            LeavesAdmin::query()->where('user_id', $user->user_id)->count()
        );
    }
}
