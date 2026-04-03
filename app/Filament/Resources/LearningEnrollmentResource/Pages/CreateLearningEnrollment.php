<?php

namespace App\Filament\Resources\LearningEnrollmentResource\Pages;

use App\Filament\Resources\LearningEnrollmentResource;
use App\Support\LearningNotificationManager;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningEnrollment extends CreateRecord
{
    protected static string $resource = LearningEnrollmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['assigned_by_user_id'] = auth()->user()?->user_id;
        $data['assigned_at'] = $data['assigned_at'] ?? now();

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        if ($record) {
            LearningNotificationManager::sendAssignmentNotification($record->refresh()->loadMissing(['user', 'course']));
        }
    }
}
