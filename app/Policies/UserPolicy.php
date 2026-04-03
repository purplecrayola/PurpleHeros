<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function view(User $user, User $record): bool
    {
        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, User $record): bool
    {
        return $user->canManageUsers();
    }

    public function delete(User $user, User $record): bool
    {
        if (! $user->canManageUsers()) {
            return false;
        }

        return $user->id !== $record->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageUsers();
    }
}
