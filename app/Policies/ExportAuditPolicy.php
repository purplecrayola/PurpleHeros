<?php

namespace App\Policies;

use App\Models\ExportAudit;
use App\Models\User;

class ExportAuditPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewReports();
    }

    public function view(User $user, ExportAudit $exportAudit): bool
    {
        return $user->canViewReports();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ExportAudit $exportAudit): bool
    {
        return false;
    }

    public function delete(User $user, ExportAudit $exportAudit): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
