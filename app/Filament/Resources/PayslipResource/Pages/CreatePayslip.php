<?php

namespace App\Filament\Resources\PayslipResource\Pages;

use App\Filament\Resources\PayslipResource;
use App\Support\MediaStorageManager;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\CreateRecord;

class CreatePayslip extends CreateRecord
{
    protected static string $resource = PayslipResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['file_path']) && MediaStorageManager::isCloudinaryEnabled()) {
            $localRelativePath = (string) $data['file_path'];
            $localPath = Storage::disk('public')->path($localRelativePath);
            $uploaded = MediaStorageManager::storeFileFromPath($localPath, 'assets/payslips-uploaded', 'payslip-upload');
            $data['file_path'] = $uploaded['path'];
            Storage::disk('public')->delete($localRelativePath);
        }

        if (! empty($data['file_path'])) {
            $data['source'] = 'uploaded';
        }

        $data['issued_at'] = now();
        $data['published_at'] = now();

        return $data;
    }
}
