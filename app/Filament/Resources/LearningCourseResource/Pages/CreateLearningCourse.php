<?php

namespace App\Filament\Resources\LearningCourseResource\Pages;

use App\Filament\Resources\LearningCourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningCourse extends CreateRecord
{
    protected static string $resource = LearningCourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->user()?->user_id;

        return $data;
    }
}

