<?php

namespace App\Filament\Resources\EmployeeStatutoryProfileResource\Pages;

use App\Filament\Resources\EmployeeStatutoryProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeStatutoryProfile extends EditRecord
{
    protected static string $resource = EmployeeStatutoryProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

