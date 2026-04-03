<?php

namespace App\Filament\Resources\PositionTypeResource\Pages;

use App\Filament\Resources\PositionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPositionType extends EditRecord
{
    protected static string $resource = PositionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
