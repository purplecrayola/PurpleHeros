<?php

namespace App\Filament\Resources\TimesheetEntryResource\Pages;

use App\Filament\Resources\TimesheetEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimesheetEntries extends ListRecords
{
    protected static string $resource = TimesheetEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
