<?php

namespace App\Policies;

use App\Models\OvertimeEntry;
use App\Models\User;

class OvertimeEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function view(User $user, OvertimeEntry $overtimeEntry): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function create(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function update(User $user, OvertimeEntry $overtimeEntry): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function delete(User $user, OvertimeEntry $overtimeEntry): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }
}
