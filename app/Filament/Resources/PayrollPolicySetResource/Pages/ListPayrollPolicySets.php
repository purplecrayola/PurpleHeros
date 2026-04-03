<?php

namespace App\Filament\Resources\PayrollPolicySetResource\Pages;

use App\Filament\Resources\PayrollPolicySetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayrollPolicySets extends ListRecords
{
    protected static string $resource = PayrollPolicySetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
