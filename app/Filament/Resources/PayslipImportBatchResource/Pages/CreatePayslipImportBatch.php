<?php

namespace App\Filament\Resources\PayslipImportBatchResource\Pages;

use App\Filament\Resources\PayslipImportBatchResource;
use App\Support\MediaStorageManager;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\CreateRecord;

class CreatePayslipImportBatch extends CreateRecord
{
    protected static string $resource = PayslipImportBatchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by_user_id'] = auth()->user()?->user_id;

        if (! empty($data['import_file_path']) && MediaStorageManager::isCloudinaryEnabled()) {
            $localRelativePath = (string) $data['import_file_path'];
            $localPath = Storage::disk('local')->path($localRelativePath);
            if (is_file($localPath)) {
                $uploaded = MediaStorageManager::storeFileFromPath($localPath, 'assets/payslip-imports', 'payslip-import');
                $data['import_file_path'] = $uploaded['path'];
                Storage::disk('local')->delete($localRelativePath);
            }
        }

        if (empty($data['source_file_name']) && ! empty($data['import_file_path'])) {
            $data['source_file_name'] = basename((string) $data['import_file_path']);
        }

        return $data;
    }
}
