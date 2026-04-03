<?php

namespace App\Policies;

use App\Models\CompanySettings;
use App\Models\User;

class CompanySettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageSettings();
    }

    public function view(User $user, CompanySettings $companySettings): bool
    {
        return $user->canManageSettings();
    }

    public function create(User $user): bool
    {
        return $user->canManageSettings();
    }

    public function update(User $user, CompanySettings $companySettings): bool
    {
        return $user->canManageSettings();
    }

    public function delete(User $user, CompanySettings $companySettings): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
