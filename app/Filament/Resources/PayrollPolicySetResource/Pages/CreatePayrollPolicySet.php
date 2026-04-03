<?php

namespace App\Filament\Resources\PayrollPolicySetResource\Pages;

use App\Filament\Resources\PayrollPolicySetResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayrollPolicySet extends CreateRecord
{
    protected static string $resource = PayrollPolicySetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->user()?->user_id;

        return $data;
    }
}
