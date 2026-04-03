<?php

namespace App\Policies;

use App\Models\Holiday;
use App\Models\User;

class HolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessHrPanel();
    }

    public function view(User $user, Holiday $holiday): bool
    {
        return $user->canAccessHrPanel();
    }

    public function create(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }
}
