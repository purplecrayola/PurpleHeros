<?php

namespace App\Filament\Resources\LearningEnrollmentResource\Pages;

use App\Filament\Resources\LearningEnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningEnrollments extends ListRecords
{
    protected static string $resource = LearningEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

