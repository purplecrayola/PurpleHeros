<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManagePeopleOps();
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->canManagePeopleOps();
    }

    public function create(User $user): bool
    {
        return $user->canManagePeopleOps();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->canManagePeopleOps();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->canManagePeopleOps();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManagePeopleOps();
    }
}
