<?php

namespace App\Filament\Resources\LearningCourseResource\Pages;

use App\Filament\Resources\LearningCourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningCourse extends EditRecord
{
    protected static string $resource = LearningCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

