<?php

namespace App\Filament\Resources\EmployeeStatutoryProfileResource\Pages;

use App\Filament\Resources\EmployeeStatutoryProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeStatutoryProfile extends CreateRecord
{
    protected static string $resource = EmployeeStatutoryProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->user()?->user_id;

        return $data;
    }
}

