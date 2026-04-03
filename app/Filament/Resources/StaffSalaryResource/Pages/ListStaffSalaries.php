<?php

namespace App\Filament\Resources\StaffSalaryResource\Pages;

use App\Filament\Resources\StaffSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffSalaries extends ListRecords
{
    protected static string $resource = StaffSalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
