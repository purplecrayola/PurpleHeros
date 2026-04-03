<?php

namespace App\Policies;

use App\Models\TimesheetEntry;
use App\Models\User;

class TimesheetEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function view(User $user, TimesheetEntry $timesheetEntry): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function create(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function update(User $user, TimesheetEntry $timesheetEntry): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function delete(User $user, TimesheetEntry $timesheetEntry): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }
}
