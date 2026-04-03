<?php

namespace App\Filament\Resources\OvertimeEntryResource\Pages;

use App\Filament\Resources\OvertimeEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOvertimeEntry extends EditRecord
{
    protected static string $resource = OvertimeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
