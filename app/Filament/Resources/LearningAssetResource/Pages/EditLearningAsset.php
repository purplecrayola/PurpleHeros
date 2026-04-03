<?php

namespace App\Filament\Resources\LearningAssetResource\Pages;

use App\Filament\Resources\LearningAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningAsset extends EditRecord
{
    protected static string $resource = LearningAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

