<?php

namespace App\Filament\Resources\TimesheetEntryResource\Pages;

use App\Filament\Resources\TimesheetEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTimesheetEntry extends CreateRecord
{
    protected static string $resource = TimesheetEntryResource::class;
}
