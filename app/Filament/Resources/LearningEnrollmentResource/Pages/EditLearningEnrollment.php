<?php

namespace App\Filament\Resources\LearningEnrollmentResource\Pages;

use App\Filament\Resources\LearningEnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningEnrollment extends EditRecord
{
    protected static string $resource = LearningEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

