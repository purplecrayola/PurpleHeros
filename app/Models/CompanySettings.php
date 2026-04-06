<?php

namespace App\Models;

use App\Support\MediaStorageManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CompanySettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'contact_person',
        'address',
        'country',
        'city',
        'state_province',
        'postal_code',
        'email',
        'phone_number',
        'mobile_number',
        'fax',
        'website_url',
        'locale',
        'timezone',
        'currency_code',
        'currency_symbol',
        'date_format',
        'time_format',
        'week_starts_on',
        'allow_employee_bank_edit',
        'employee_field_rules_json',
        'employee_status_options_json',
        'storage_provider',
        'cloudinary_cloud_name',
        'cloudinary_api_key',
        'cloudinary_api_secret',
        'cloudinary_folder',
        'cloudinary_secure_delivery',
        'mail_mailer',
        'ses_enabled',
        'ses_region',
        'ses_access_key_id',
        'ses_secret_access_key',
        'ses_configuration_set',
        'mail_from_address',
        'mail_from_name',
        'mail_reply_to_address',
        'people_ops_email',
        'learning_notify_on_assignment',
        'learning_notify_on_reminder',
        'learning_notify_on_completion',
        'learning_assignment_subject',
        'learning_assignment_body',
        'learning_reminder_subject',
        'learning_reminder_body',
        'learning_completion_subject',
        'learning_completion_body',
        'offboarding_notify_on_planned',
        'offboarding_notify_on_completed',
        'offboarding_planned_subject',
        'offboarding_planned_body',
        'offboarding_completed_subject',
        'offboarding_completed_body',
        'learning_certificate_title',
        'learning_certificate_subtitle',
        'learning_certificate_signatory_name',
        'learning_certificate_signatory_title',
        'learning_certificate_footer_note',
        'opay_enabled',
        'opay_sandbox_mode',
        'opay_base_url',
        'opay_transfer_path',
        'opay_merchant_id',
        'opay_public_key',
        'opay_secret_key',
        'kuda_enabled',
        'kuda_sandbox_mode',
        'kuda_base_url',
        'kuda_transfer_path',
        'kuda_api_key',
        'kuda_secret_key',
        'kuda_client_email',
        'header_logo_path',
        'login_logo_path',
        'favicon_path',
        'login_image_path',
        'brand_primary_color',
        'brand_dark_color',
        'brand_neutral_color',
        'header_text_color',
        'sidebar_text_color',
        'sidebar_muted_text_color',
        'workflow_current_color',
        'workflow_completed_color',
        'workflow_pending_color',
        'login_page_title',
        'login_brand_label',
        'login_hero_title',
        'login_hero_copy',
        'login_left_label',
        'login_left_copy',
        'login_right_label',
        'login_right_copy',
        'login_help_line_one',
        'login_help_line_two',
        'login_help_line_three',
    ];

    protected $casts = [
        'allow_employee_bank_edit' => 'boolean',
        'cloudinary_secure_delivery' => 'boolean',
        'ses_enabled' => 'boolean',
        'employee_field_rules_json' => 'array',
        'employee_status_options_json' => 'array',
        'learning_notify_on_assignment' => 'boolean',
        'learning_notify_on_reminder' => 'boolean',
        'learning_notify_on_completion' => 'boolean',
        'offboarding_notify_on_planned' => 'boolean',
        'offboarding_notify_on_completed' => 'boolean',
        'opay_enabled' => 'boolean',
        'opay_sandbox_mode' => 'boolean',
        'kuda_enabled' => 'boolean',
        'kuda_sandbox_mode' => 'boolean',
    ];

    public static function defaults(): array
    {
        return [
            'company_name' => 'Purple Crayola',
            'contact_person' => 'Purple HR Admin',
            'address' => 'Lagos, Nigeria',
            'country' => 'Nigeria',
            'city' => 'Lagos',
            'state_province' => 'Lagos',
            'postal_code' => '100001',
            'email' => 'hello@purplecrayola.com',
            'phone_number' => '+2348000000000',
            'mobile_number' => '+2348000000000',
            'fax' => 'N/A',
            'website_url' => 'https://purplecrayola.com',
            'locale' => 'en',
            'timezone' => 'Africa/Lagos',
            'currency_code' => 'NGN',
            'currency_symbol' => '₦',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'week_starts_on' => 'Monday',
            'allow_employee_bank_edit' => false,
            'employee_field_rules_json' => self::defaultEmployeeFieldRules(),
            'employee_status_options_json' => self::defaultEmployeeStatusOptions(),
            'storage_provider' => 'local',
            'cloudinary_cloud_name' => null,
            'cloudinary_api_key' => null,
            'cloudinary_api_secret' => null,
            'cloudinary_folder' => 'purple-hr',
            'cloudinary_secure_delivery' => true,
            'mail_mailer' => 'log',
            'ses_enabled' => false,
            'ses_region' => 'us-east-1',
            'ses_access_key_id' => null,
            'ses_secret_access_key' => null,
            'ses_configuration_set' => null,
            'mail_from_address' => 'hello@purplecrayola.com',
            'mail_from_name' => 'Purple Crayola',
            'mail_reply_to_address' => null,
            'people_ops_email' => 'heros@purplecrayola.com',
            'learning_notify_on_assignment' => true,
            'learning_notify_on_reminder' => true,
            'learning_notify_on_completion' => true,
            'learning_assignment_subject' => 'New Learning Assignment: {course_title}',
            'learning_assignment_body' => 'Hello {employee_name}, you have been enrolled in {course_title}. Due date: {due_date}.',
            'learning_reminder_subject' => 'Learning Reminder: {course_title}',
            'learning_reminder_body' => 'Hello {employee_name}, this is a reminder to complete {course_title} by {due_date}.',
            'learning_completion_subject' => 'Learning Completed: {course_title}',
            'learning_completion_body' => 'Hello {employee_name}, completion has been recorded for {course_title}. Great work.',
            'offboarding_notify_on_planned' => true,
            'offboarding_notify_on_completed' => true,
            'offboarding_planned_subject' => 'Offboarding Initiated - {employee_name}',
            'offboarding_planned_body' => 'Employee {employee_name} ({employee_id}) has entered offboarding. Status: {from_status} -> {to_status}. Last working day: {last_working_day}. Type: {offboarding_type}. Reason: {offboarding_reason}.',
            'offboarding_completed_subject' => 'Offboarding Completed - {employee_name}',
            'offboarding_completed_body' => 'Offboarding for {employee_name} ({employee_id}) is complete. Completed by: {completed_by}. Completed at: {completed_at}.',
            'learning_certificate_title' => 'Certificate of Completion',
            'learning_certificate_subtitle' => 'This certifies that {employee_name} completed {course_title}.',
            'learning_certificate_signatory_name' => 'HR Manager',
            'learning_certificate_signatory_title' => 'People Operations',
            'learning_certificate_footer_note' => 'System-generated learning certificate.',
            'opay_enabled' => false,
            'opay_sandbox_mode' => true,
            'opay_base_url' => null,
            'opay_transfer_path' => '/api/v1/transfers',
            'opay_merchant_id' => null,
            'opay_public_key' => null,
            'opay_secret_key' => null,
            'kuda_enabled' => false,
            'kuda_sandbox_mode' => true,
            'kuda_base_url' => null,
            'kuda_transfer_path' => '/v2/disbursements',
            'kuda_api_key' => null,
            'kuda_secret_key' => null,
            'kuda_client_email' => null,
            'header_logo_path' => 'assets/img/brand/purplecrayola-white.svg',
            'login_logo_path' => 'assets/img/brand/purplecrayola-black.svg',
            'favicon_path' => 'assets/img/favicon.ico',
            'login_image_path' => 'assets/images/purplecrayola heros login.jpg',
            'brand_primary_color' => '#8A00FF',
            'brand_dark_color' => '#00163F',
            'brand_neutral_color' => '#DCDDDF',
            'header_text_color' => '#FFFFFF',
            'sidebar_text_color' => '#F5F7FF',
            'sidebar_muted_text_color' => '#A9B8CC',
            'workflow_current_color' => '#8A00FF',
            'workflow_completed_color' => '#0F9D58',
            'workflow_pending_color' => '#DCDDDF',
            'login_page_title' => 'PurpleHeros Access',
            'login_brand_label' => 'PurpleHeros',
            'login_hero_title' => 'Sign in to PurpleHeros',
            'login_hero_copy' => 'Secure access to people operations, payroll, leave, and internal workflow tools.',
            'login_left_label' => 'PurpleHeros',
            'login_left_copy' => 'People operations for teams that need one calm system for access, payroll, leave, and day-to-day workforce control.',
            'login_right_label' => 'Access',
            'login_right_copy' => 'Use your issued work credentials. Contact your HR or systems administrator if you need onboarding, password support, or access changes.',
            'login_help_line_one' => 'Forgot password?',
            'login_help_line_two' => 'PurpleHeros',
            'login_help_line_three' => 'Purple Crayola Employee Access',
        ];
    }

    public static function current(): self
    {
        $instance = new static();

        if (! Schema::hasTable($instance->getTable())) {
            return new static(static::defaults());
        }

        $settings = static::query()->find(1);
        if ($settings) {
            return $settings;
        }

        $settings = new static(static::defaults());
        // Ensure one canonical settings row is always keyed to id=1.
        $settings->id = 1;
        $settings->save();

        return $settings;
    }

    public function assetUrl(string $field, string $fallback): string
    {
        $path = trim((string) ($this->{$field} ?: $fallback));

        if ($path === '') {
            $path = $fallback;
        }

        return MediaStorageManager::publicUrl($path, $fallback);
    }

    public function color(string $field, string $fallback): string
    {
        $value = strtoupper(trim((string) ($this->{$field} ?: $fallback)));

        if (! preg_match('/^#[0-9A-F]{6}$/', $value)) {
            return strtoupper($fallback);
        }

        return $value;
    }

    public function colorRgb(string $field, string $fallback): string
    {
        $hex = ltrim($this->color($field, $fallback), '#');

        return implode(', ', [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ]);
    }

    /**
     * @return array<int, array{field:string,required:bool}>
     */
    public static function defaultEmployeeFieldRules(): array
    {
        return [
            ['field' => 'first_name', 'required' => true],
            ['field' => 'last_name', 'required' => true],
            ['field' => 'email', 'required' => true],
            ['field' => 'employee_id', 'required' => true],
            ['field' => 'gender', 'required' => false],
            ['field' => 'birth_date', 'required' => false],
            ['field' => 'company', 'required' => false],
            ['field' => 'status', 'required' => false],
            ['field' => 'phone_number', 'required' => false],
            ['field' => 'department', 'required' => false],
            ['field' => 'designation', 'required' => false],
            ['field' => 'reports_to', 'required' => false],
            ['field' => 'address', 'required' => false],
            ['field' => 'state', 'required' => false],
            ['field' => 'country', 'required' => false],
            ['field' => 'pin_code', 'required' => false],
            ['field' => 'primary_bank_name', 'required' => false],
            ['field' => 'primary_bank_account_no', 'required' => false],
            ['field' => 'primary_ifsc_code', 'required' => false],
            ['field' => 'secondary_bank_name', 'required' => false],
            ['field' => 'secondary_bank_account_no', 'required' => false],
            ['field' => 'secondary_ifsc_code', 'required' => false],
            ['field' => 'salary_amount', 'required' => false],
            ['field' => 'tax_station', 'required' => false],
            ['field' => 'tax_residency_state', 'required' => false],
            ['field' => 'annual_rent', 'required' => false],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function defaultEmployeeStatusOptions(): array
    {
        return ['Permanent', 'Contract', 'Intern', 'Probation'];
    }

    /**
     * @return array<string,bool>
     */
    public function employeeFieldRulesMap(): array
    {
        $defaults = collect(self::defaultEmployeeFieldRules())
            ->mapWithKeys(fn (array $rule): array => [(string) $rule['field'] => (bool) $rule['required']])
            ->all();

        $raw = is_array($this->employee_field_rules_json) ? $this->employee_field_rules_json : [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $field = trim((string) ($row['field'] ?? ''));
            if ($field === '') {
                continue;
            }

            $defaults[$field] = (bool) ($row['required'] ?? false);
        }

        return $defaults;
    }

    public function isEmployeeFieldRequired(string $field): bool
    {
        return (bool) ($this->employeeFieldRulesMap()[$field] ?? false);
    }

    /**
     * @return array<string,string>
     */
    public function employeeStatusOptionsForSelect(): array
    {
        $options = is_array($this->employee_status_options_json) ? $this->employee_status_options_json : [];
        $values = collect($options)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values();

        if ($values->isEmpty()) {
            $values = collect(self::defaultEmployeeStatusOptions());
        }

        return $values->mapWithKeys(fn (string $status): array => [$status => $status])->all();
    }
}
