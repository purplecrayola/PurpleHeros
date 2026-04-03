<?php

namespace App\Filament\Resources\PayslipResource\Pages;

use App\Filament\Resources\PayslipResource;
use App\Support\MediaStorageManager;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditPayslip extends EditRecord
{
    protected static string $resource = PayslipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['file_path']) && MediaStorageManager::isCloudinaryEnabled()) {
            $filePath = (string) $data['file_path'];

            if (! str_starts_with($filePath, 'http://') && ! str_starts_with($filePath, 'https://')) {
                $localPath = Storage::disk('public')->path($filePath);
                if (is_file($localPath)) {
                    $uploaded = MediaStorageManager::storeFileFromPath($localPath, 'assets/payslips-uploaded', 'payslip-upload');
                    $data['file_path'] = $uploaded['path'];
                    Storage::disk('public')->delete($filePath);
                }
            }
        }

        return $data;
    }
}
