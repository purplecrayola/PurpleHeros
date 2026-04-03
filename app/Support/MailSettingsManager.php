<?php

namespace App\Support;

use App\Models\CompanySettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class MailSettingsManager
{
    public static function apply(?CompanySettings $settings = null): void
    {
        try {
            $model = new CompanySettings();

            if (! Schema::hasTable($model->getTable())) {
                return;
            }

            $settings ??= CompanySettings::current();

            $fromAddress = trim((string) ($settings->mail_from_address ?: config('mail.from.address')));
            $fromName = trim((string) ($settings->mail_from_name ?: config('mail.from.name')));
            $replyToAddress = trim((string) ($settings->mail_reply_to_address ?: ''));
            $mailer = trim((string) ($settings->mail_mailer ?: config('mail.default')));

            $hasSesCredentials = filled($settings->ses_access_key_id)
                && filled($settings->ses_secret_access_key)
                && filled($settings->ses_region);

            if (($settings->ses_enabled ?? false) && $hasSesCredentials) {
                Config::set('services.ses.key', (string) $settings->ses_access_key_id);
                Config::set('services.ses.secret', (string) $settings->ses_secret_access_key);
                Config::set('services.ses.region', (string) $settings->ses_region);
                Config::set('mail.mailers.ses.transport', 'ses');

                if (filled($settings->ses_configuration_set)) {
                    Config::set('mail.mailers.ses.options', [
                        'ConfigurationSetName' => (string) $settings->ses_configuration_set,
                    ]);
                } else {
                    Config::set('mail.mailers.ses.options', []);
                }
            }

            if ($mailer === 'ses' && (! ($settings->ses_enabled ?? false) || ! $hasSesCredentials)) {
                $mailer = 'log';
            }

            Config::set('mail.default', $mailer !== '' ? $mailer : 'log');

            if ($fromAddress !== '') {
                Config::set('mail.from.address', $fromAddress);
            }

            if ($fromName !== '') {
                Config::set('mail.from.name', $fromName);
            }

            if ($replyToAddress !== '') {
                Config::set('mail.reply_to.address', $replyToAddress);
            } else {
                Config::set('mail.reply_to.address', null);
            }

            if (app()->bound('mail.manager')) {
                app('mail.manager')->forgetMailers();
            }
        } catch (\Throwable) {
            // Never block request boot from settings mail config.
        }
    }
}

