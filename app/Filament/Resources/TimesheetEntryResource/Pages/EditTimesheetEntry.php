<?php

namespace App\Filament\Resources\TimesheetEntryResource\Pages;

use App\Filament\Resources\TimesheetEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimesheetEntry extends EditRecord
{
    protected static string $resource = TimesheetEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
