<?php

namespace App\Filament\Resources\PayslipImportBatchResource\Pages;

use App\Filament\Resources\PayslipImportBatchResource;
use App\Services\Payroll\PayslipImportService;
use App\Support\MediaStorageManager;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditPayslipImportBatch extends EditRecord
{
    protected static string $resource = PayslipImportBatchResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['import_file_path']) && MediaStorageManager::isCloudinaryEnabled()) {
            $filePath = (string) $data['import_file_path'];

            if (! str_starts_with($filePath, 'http://') && ! str_starts_with($filePath, 'https://')) {
                $localPath = Storage::disk('local')->path($filePath);
                if (is_file($localPath)) {
                    $uploaded = MediaStorageManager::storeFileFromPath($localPath, 'assets/payslip-imports', 'payslip-import');
                    $data['import_file_path'] = $uploaded['path'];
                    Storage::disk('local')->delete($filePath);
                }
            }
        }

        if (empty($data['source_file_name']) && ! empty($data['import_file_path'])) {
            $data['source_file_name'] = basename((string) $data['import_file_path']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('process_import')
                ->label('Process Import')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (PayslipImportService $service): void {
                    $result = $service->process($this->record->refresh());

                    $summary = 'Processed: '.$result['processed'].' | Failed: '.$result['failed'];

                    Notification::make()
                        ->title('Payslip import completed')
                        ->body($summary)
                        ->success()
                        ->send();

                    if (! empty($result['errors'])) {
                        Notification::make()
                            ->title('Import warnings')
                            ->body(implode(' | ', array_slice($result['errors'], 0, 3)))
                            ->warning()
                            ->send();
                    }

                    $this->refreshFormData(['status', 'processed_rows', 'failed_rows']);
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
