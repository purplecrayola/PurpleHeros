<?php

namespace App\Filament\Resources\CompanySettingsResource\Pages;

use App\Filament\Resources\CompanySettingsResource;
use App\Support\MailSettingsManager;
use App\Support\MediaStorageManager;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditCompanySettings extends EditRecord
{
    protected static string $resource = CompanySettingsResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['ses_enabled'] = (bool) ($data['ses_enabled'] ?? false);

        if ($data['ses_enabled']) {
            $data['mail_mailer'] = 'ses';
        }

        $data = $this->processBrandUpload($data, 'header_logo_upload', 'header_logo_path', 'header-logo');
        $data = $this->processBrandUpload($data, 'login_logo_upload', 'login_logo_path', 'login-logo');
        $data = $this->processBrandUpload($data, 'favicon_upload', 'favicon_path', 'favicon');
        $data = $this->processBrandUpload($data, 'login_image_upload', 'login_image_path', 'login-hero');

        return $data;
    }

    protected function afterSave(): void
    {
        MailSettingsManager::apply($this->record->refresh());
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('testOpayConnection')
                ->label('Test OPay Connection')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->requiresConfirmation()
                ->action(function (): void {
                    $result = $this->runOpayConnectionTest();

                    $notification = Notification::make()
                        ->title($result['success'] ? 'OPay connection looks good' : 'OPay connection test failed')
                        ->body($result['message']);

                    if ($result['success']) {
                        $notification->success()->send();
                        return;
                    }

                    $notification->danger()->send();
                }),
            Actions\Action::make('sendTestEmail')
                ->label('Send Test Email')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    TextInput::make('recipient')
                        ->email()
                        ->required()
                        ->default(fn (): string => (string) ($this->record->mail_from_address ?: $this->record->email)),
                ])
                ->action(function (array $data): void {
                    try {
                        MailSettingsManager::apply($this->record->refresh());

                        Mail::raw(
                            'This is a test email from Purple HR SES configuration.',
                            function ($message) use ($data): void {
                                $message->to((string) $data['recipient'])->subject('Purple HR Mail Test');
                            }
                        );

                        Notification::make()
                            ->title('Test email sent')
                            ->success()
                            ->send();
                    } catch (\Throwable $exception) {
                        report($exception);
                        Notification::make()
                            ->title('Test email failed')
                            ->body('Please verify SES region, credentials, and sender identity.')
                            ->danger()
                            ->send();
                    }
                })
                ->color('gray'),
        ];
    }

    private function processBrandUpload(
        array $data,
        string $uploadKey,
        string $targetPathKey,
        string $namePrefix
    ): array {
        $relativePath = trim((string) ($data[$uploadKey] ?? ''));
        unset($data[$uploadKey]);

        if ($relativePath === '') {
            return $data;
        }

        if (! str_starts_with($relativePath, 'http://') && ! str_starts_with($relativePath, 'https://')) {
            $localPath = Storage::disk('public')->path($relativePath);
            if (is_file($localPath)) {
                $uploaded = MediaStorageManager::storeFileFromPath($localPath, 'assets/img/brand/uploads', $namePrefix);
                $data[$targetPathKey] = $uploaded['path'];
                Storage::disk('public')->delete($relativePath);
            }
        }

        return $data;
    }

    /**
     * @return array{success:bool,message:string}
     */
    private function runOpayConnectionTest(): array
    {
        $settings = $this->record->refresh();
        $baseUrl = rtrim((string) $settings->opay_base_url, '/');
        $path = '/' . ltrim((string) ($settings->opay_transfer_path ?: '/api/v1/transfers'), '/');

        if (! (bool) $settings->opay_enabled) {
            return [
                'success' => false,
                'message' => 'OPay is disabled in settings.',
            ];
        }

        if ($baseUrl === '' || blank($settings->opay_secret_key)) {
            return [
                'success' => false,
                'message' => 'Missing OPay base URL or secret key.',
            ];
        }

        $url = $baseUrl . $path;

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . (string) $settings->opay_secret_key,
                    'MerchantId' => (string) ($settings->opay_merchant_id ?: ''),
                    'X-Public-Key' => (string) ($settings->opay_public_key ?: ''),
                    'X-Connection-Test' => 'true',
                ])
                ->post($url, [
                    'reference' => 'OPAY-CONN-TEST-' . Str::upper(Str::random(8)),
                    'amount' => 0,
                    'currency' => 'NGN',
                    'narration' => 'Connection test',
                    'recipient' => [
                        'name' => 'Connection Test',
                        'account_number' => '0000000000',
                        'bank_code' => '000',
                    ],
                ]);
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'message' => 'Unable to reach endpoint: ' . $exception->getMessage(),
            ];
        }

        $status = $response->status();
        $json = $response->json();
        $providerMessage = trim((string) (
            (is_array($json) ? ($json['message'] ?? $json['error'] ?? $json['detail'] ?? null) : null)
            ?: ''
        ));

        if ($status === 404) {
            return [
                'success' => false,
                'message' => "HTTP 404 on {$path}. Transfer Path appears incorrect for this OPay environment.",
            ];
        }

        if (in_array($status, [401, 403], true)) {
            return [
                'success' => false,
                'message' => "Endpoint reached but auth failed (HTTP {$status}). Check OPay keys/merchant headers.",
            ];
        }

        if (in_array($status, [400, 422], true)) {
            return [
                'success' => true,
                'message' => "Endpoint/auth reachable (HTTP {$status}). Validation failed as expected for test payload." .
                    ($providerMessage !== '' ? " Provider: {$providerMessage}" : ''),
            ];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => "Connected successfully (HTTP {$status}) at {$path}.",
            ];
        }

        return [
            'success' => false,
            'message' => "Received HTTP {$status} at {$path}." . ($providerMessage !== '' ? " Provider: {$providerMessage}" : ''),
        ];
    }
}
