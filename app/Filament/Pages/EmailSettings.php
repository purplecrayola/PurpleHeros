<?php

namespace App\Filament\Pages;

use App\Models\CompanySettings;
use App\Support\MailSettingsManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class EmailSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 30;
    protected static ?string $title = 'Email Settings';
    protected static ?string $slug = 'email-settings';

    protected static string $view = 'filament.pages.email-settings';

    public string $mail_mailer = 'log';
    public bool $ses_enabled = false;
    public string $ses_region = 'us-east-1';
    public ?string $ses_access_key_id = null;
    public ?string $ses_secret_access_key = null;
    public ?string $ses_configuration_set = null;
    public string $mail_from_address = '';
    public string $mail_from_name = '';
    public ?string $mail_reply_to_address = null;
    public string $test_recipient = '';

    public function mount(): void
    {
        $settings = CompanySettings::current();
        $this->mail_mailer = (string) ($settings->mail_mailer ?: 'log');
        $this->ses_enabled = (bool) $settings->ses_enabled;
        $this->ses_region = (string) ($settings->ses_region ?: 'us-east-1');
        $this->ses_access_key_id = $settings->ses_access_key_id;
        $this->ses_secret_access_key = $settings->ses_secret_access_key;
        $this->ses_configuration_set = $settings->ses_configuration_set;
        $this->mail_from_address = (string) ($settings->mail_from_address ?: $settings->email);
        $this->mail_from_name = (string) ($settings->mail_from_name ?: $settings->company_name);
        $this->mail_reply_to_address = $settings->mail_reply_to_address;
        $this->test_recipient = (string) ($settings->email ?: '');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->canManageSettings();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'mail_mailer' => 'required|in:log,smtp,ses',
            'ses_enabled' => 'required|boolean',
            'ses_region' => 'nullable|string|max:100|required_if:ses_enabled,true',
            'ses_access_key_id' => 'nullable|string|max:255|required_if:ses_enabled,true',
            'ses_secret_access_key' => 'nullable|string|max:255|required_if:ses_enabled,true',
            'ses_configuration_set' => 'nullable|string|max:255',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mail_reply_to_address' => 'nullable|email|max:255',
        ]);

        if ($validated['ses_enabled']) {
            $validated['mail_mailer'] = 'ses';
        }

        $settings = CompanySettings::current();
        $settings->update($validated);
        MailSettingsManager::apply($settings->refresh());

        Notification::make()
            ->title('Email settings updated')
            ->success()
            ->send();
    }

    public function sendTest(): void
    {
        $validated = $this->validate([
            'test_recipient' => 'required|email|max:255',
        ]);

        try {
            $settings = CompanySettings::current();
            MailSettingsManager::apply($settings);

            Mail::raw(
                'This is a test email from Purple HR mail delivery settings. If you received this, mail transport is working.',
                function ($message) use ($validated, $settings): void {
                    $message->to($validated['test_recipient'])->subject('Purple HR Mail Test');

                    if (filled($settings->mail_reply_to_address)) {
                        $message->replyTo((string) $settings->mail_reply_to_address);
                    }
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
                ->body('Please review SES credentials, region, and sender configuration.')
                ->danger()
                ->send();
        }
    }
}
