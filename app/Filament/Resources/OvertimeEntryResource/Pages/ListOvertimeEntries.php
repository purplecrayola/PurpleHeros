<?php

namespace App\Filament\Resources\OvertimeEntryResource\Pages;

use App\Filament\Resources\OvertimeEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOvertimeEntries extends ListRecords
{
    protected static string $resource = OvertimeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
