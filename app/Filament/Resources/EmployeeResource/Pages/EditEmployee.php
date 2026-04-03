<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;
    protected array $relatedData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return EmployeeResource::fillRelatedDataForRecord($this->record, $data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->relatedData = EmployeeResource::extractRelatedData($data);
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $data['name'] = trim($firstName . ' ' . $lastName);

        return $data;
    }

    protected function afterSave(): void
    {
        EmployeeResource::syncRelatedData($this->record, $this->relatedData);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
