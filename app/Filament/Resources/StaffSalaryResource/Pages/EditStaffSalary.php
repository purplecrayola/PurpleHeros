<?php

namespace App\Filament\Resources\StaffSalaryResource\Pages;

use App\Filament\Resources\StaffSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaffSalary extends EditRecord
{
    protected static string $resource = StaffSalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
