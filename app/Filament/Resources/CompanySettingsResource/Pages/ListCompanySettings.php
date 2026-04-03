<?php

namespace App\Filament\Resources\CompanySettingsResource\Pages;

use App\Filament\Resources\CompanySettingsResource;
use App\Models\CompanySettings;
use Filament\Resources\Pages\ListRecords;

class ListCompanySettings extends ListRecords
{
    protected static string $resource = CompanySettingsResource::class;

    public function mount(): void
    {
        parent::mount();

        $settings = CompanySettings::current();
        $this->redirect(CompanySettingsResource::getUrl('edit', ['record' => $settings]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
