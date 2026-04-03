<?php

namespace App\Filament\Resources\PayslipImportBatchResource\Pages;

use App\Filament\Resources\PayslipImportBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayslipImportBatches extends ListRecords
{
    protected static string $resource = PayslipImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
