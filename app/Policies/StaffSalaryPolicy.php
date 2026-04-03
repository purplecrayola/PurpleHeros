<?php

namespace App\Policies;

use App\Models\StaffSalary;
use App\Models\User;

class StaffSalaryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManagePayroll();
    }

    public function view(User $user, StaffSalary $staffSalary): bool
    {
        return $user->canManagePayroll();
    }

    public function create(User $user): bool
    {
        return $user->canManagePayroll();
    }

    public function update(User $user, StaffSalary $staffSalary): bool
    {
        return $user->canManagePayroll();
    }

    public function delete(User $user, StaffSalary $staffSalary): bool
    {
        return $user->canManagePayroll();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManagePayroll();
    }
}
