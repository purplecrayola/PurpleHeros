<?php

namespace App\Policies;

use App\Models\LeavesAdmin;
use App\Models\User;

class LeavesAdminPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function view(User $user, LeavesAdmin $leavesAdmin): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function create(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function update(User $user, LeavesAdmin $leavesAdmin): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function delete(User $user, LeavesAdmin $leavesAdmin): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }
}
