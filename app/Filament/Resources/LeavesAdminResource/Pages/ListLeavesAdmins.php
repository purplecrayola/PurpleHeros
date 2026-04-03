<?php

namespace App\Filament\Resources\LeavesAdminResource\Pages;

use App\Filament\Resources\LeavesAdminResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeavesAdmins extends ListRecords
{
    protected static string $resource = LeavesAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
