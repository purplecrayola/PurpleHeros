<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected array $relatedData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->relatedData = EmployeeResource::extractRelatedData($data);
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $data['name'] = trim($firstName . ' ' . $lastName);

        return $data;
    }

    protected function afterCreate(): void
    {
        EmployeeResource::syncRelatedData($this->record, $this->relatedData);
    }
}
