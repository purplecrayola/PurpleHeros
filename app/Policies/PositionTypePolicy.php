<?php

namespace App\Policies;

use App\Models\positionType;
use App\Models\User;

class PositionTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageOrganization();
    }

    public function view(User $user, positionType $positionType): bool
    {
        return $user->canManageOrganization();
    }

    public function create(User $user): bool
    {
        return $user->canManageOrganization();
    }

    public function update(User $user, positionType $positionType): bool
    {
        return $user->canManageOrganization();
    }

    public function delete(User $user, positionType $positionType): bool
    {
        return $user->canManageOrganization();
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageOrganization();
    }
}
