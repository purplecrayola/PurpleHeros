<?php

namespace App\Filament\Resources\ExportAuditResource\Pages;

use App\Filament\Resources\ExportAuditResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExportAudit extends EditRecord
{
    protected static string $resource = ExportAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
