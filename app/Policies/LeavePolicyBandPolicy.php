<?php

namespace App\Policies;

use App\Models\LeavePolicyBand;
use App\Models\User;

class LeavePolicyBandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function view(User $user, LeavePolicyBand $leavePolicyBand): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function create(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function update(User $user, LeavePolicyBand $leavePolicyBand): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function delete(User $user, LeavePolicyBand $leavePolicyBand): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }
}
