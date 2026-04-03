<?php

namespace App\Filament\Resources\LearningCourseResource\Pages;

use App\Filament\Resources\LearningCourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningCourses extends ListRecords
{
    protected static string $resource = LearningCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

