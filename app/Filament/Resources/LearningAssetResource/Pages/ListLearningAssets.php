<?php

namespace App\Filament\Resources\LearningAssetResource\Pages;

use App\Filament\Resources\LearningAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningAssets extends ListRecords
{
    protected static string $resource = LearningAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

