<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function view(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function create(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->canManageTimeAttendance();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageTimeAttendance();
    }
}
