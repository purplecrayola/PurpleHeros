<?php

namespace App\Filament\Resources\LeavesAdminResource\Pages;

use App\Filament\Resources\LeavesAdminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeavesAdmin extends EditRecord
{
    protected static string $resource = LeavesAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
