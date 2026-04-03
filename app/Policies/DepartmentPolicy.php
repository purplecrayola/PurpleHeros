<?php

namespace App\Policies;

use App\Models\department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageOrganization();
    }

    public function view(User $user, department $department): bool
    {
        return $user->canManageOrganization();
    }

    public function create(User $user): bool
    {
        return $user->canManageOrganization();
    }

    public function update(User $user, department $department): bool
    {
        return $user->canManageOrganization();
    }

    public function delete(User $user, department $department): bool
    {
        return $user->canManageOrganization();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageOrganization();
    }
}
