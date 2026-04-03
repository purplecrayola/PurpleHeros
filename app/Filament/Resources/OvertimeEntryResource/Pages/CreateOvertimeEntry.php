<?php

namespace App\Filament\Resources\OvertimeEntryResource\Pages;

use App\Filament\Resources\OvertimeEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOvertimeEntry extends CreateRecord
{
    protected static string $resource = OvertimeEntryResource::class;
}
