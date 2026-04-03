<?php

namespace App\Filament\Resources\PayrollPolicySetResource\Pages;

use App\Filament\Resources\PayrollPolicySetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayrollPolicySet extends EditRecord
{
    protected static string $resource = PayrollPolicySetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
