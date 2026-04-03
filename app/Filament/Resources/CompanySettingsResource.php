<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanySettingsResource\Pages;
use App\Models\CompanySettings;
use Filament\Forms\Get;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class CompanySettingsResource extends Resource
{
    protected static ?string $model = CompanySettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'System Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('System Settings')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Organization')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make('Company Profile')
                                    ->schema([
                                        TextInput::make('company_name')->required()->maxLength(255),
                                        TextInput::make('contact_person')->maxLength(255),
                                        TextInput::make('email')->email()->maxLength(255),
                                        TextInput::make('website_url')->url()->maxLength(255),
                                        TextInput::make('phone_number')->maxLength(255),
                                        TextInput::make('mobile_number')->maxLength(255),
                                        TextInput::make('address')->maxLength(255),
                                        TextInput::make('country')->maxLength(255),
                                        TextInput::make('city')->maxLength(255),
                                        TextInput::make('state_province')->label('State/Province')->maxLength(255),
                                        TextInput::make('postal_code')->maxLength(255),
                                    ])->columns(2),
                                Section::make('System Preferences')
                                    ->schema([
                                        Select::make('locale')
                                            ->options([
                                                'en' => 'English',
                                                'fr' => 'French',
                                                'es' => 'Spanish',
                                            ])
                                            ->searchable()
                                            ->required(),
                                        Select::make('timezone')
                                            ->options([
                                                'Africa/Lagos' => 'Africa/Lagos',
                                                'Europe/London' => 'Europe/London',
                                                'America/New_York' => 'America/New_York',
                                                'Asia/Dubai' => 'Asia/Dubai',
                                            ])
                                            ->searchable()
                                            ->required(),
                                        Select::make('currency_code')
                                            ->label('Currency Code')
                                            ->options([
                                                'NGN' => 'NGN',
                                                'USD' => 'USD',
                                                'GBP' => 'GBP',
                                                'EUR' => 'EUR',
                                            ])
                                            ->required(),
                                        TextInput::make('currency_symbol')
                                            ->label('Currency Symbol')
                                            ->required()
                                            ->maxLength(10),
                                        Select::make('date_format')
                                            ->options([
                                                'Y-m-d' => 'YYYY-MM-DD',
                                                'd/m/Y' => 'DD/MM/YYYY',
                                                'm/d/Y' => 'MM/DD/YYYY',
                                            ])
                                            ->required(),
                                        Select::make('time_format')
                                            ->options([
                                                'H:i' => '24-hour (HH:mm)',
                                                'h:i A' => '12-hour (hh:mm AM/PM)',
                                            ])
                                            ->required(),
                                        Select::make('week_starts_on')
                                            ->options([
                                                'Monday' => 'Monday',
                                                'Sunday' => 'Sunday',
                                            ])
                                            ->required(),
                                        Toggle::make('allow_employee_bank_edit')
                                            ->label('Allow Employees To Edit Their Bank Information')
                                            ->helperText('Keep OFF to make bank details admin-managed only.'),
                                    ])->columns(2),
                                Section::make('Employee Form Controls')
                                    ->description('Control which employee fields are required and configure employment status options.')
                                    ->schema([
                                        TagsInput::make('employee_status_options_json')
                                            ->label('Employment Status Options')
                                            ->placeholder('Add status')
                                            ->helperText('Examples: Permanent, Contract, Intern, Probation')
                                            ->default(CompanySettings::defaultEmployeeStatusOptions()),
                                        Repeater::make('employee_field_rules_json')
                                            ->label('Field Requirement Rules')
                                            ->schema([
                                                Select::make('field')
                                                    ->label('Field')
                                                    ->options(\App\Filament\Resources\EmployeeResource::fieldRequirementOptions())
                                                    ->searchable()
                                                    ->required(),
                                                Toggle::make('required')
                                                    ->label('Required')
                                                    ->default(false),
                                            ])
                                            ->columns(2)
                                            ->default(CompanySettings::defaultEmployeeFieldRules())
                                            ->addActionLabel('Add Field Rule')
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ])->columns(1),
                            ]),
                        Tab::make('Branding')
                            ->icon('heroicon-o-swatch')
                            ->schema([
                                Section::make('Brand & Login Experience')
                                    ->schema([
                                        FileUpload::make('header_logo_upload')
                                            ->label('Upload Header Logo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('brand-settings-temp')
                                            ->visibility('public')
                                            ->preserveFilenames()
                                            ->dehydrated()
                                            ->helperText('Used in the top app header.'),
                                        TextInput::make('header_logo_path')->maxLength(255),
                                        FileUpload::make('login_logo_upload')
                                            ->label('Upload Login Logo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('brand-settings-temp')
                                            ->visibility('public')
                                            ->preserveFilenames()
                                            ->dehydrated()
                                            ->helperText('Shown above the sign-in form.'),
                                        TextInput::make('login_logo_path')->maxLength(255),
                                        FileUpload::make('favicon_upload')
                                            ->label('Upload Favicon (.ico or image)')
                                            ->acceptedFileTypes([
                                                'image/x-icon',
                                                'image/vnd.microsoft.icon',
                                                'image/png',
                                                'image/jpeg',
                                                'image/webp',
                                            ])
                                            ->disk('public')
                                            ->directory('brand-settings-temp')
                                            ->visibility('public')
                                            ->preserveFilenames()
                                            ->dehydrated()
                                            ->helperText('Browser tab icon across web and admin pages.'),
                                        TextInput::make('favicon_path')->maxLength(255),
                                        TextInput::make('login_image_path')->maxLength(255),
                                        TextInput::make('brand_primary_color')->label('Primary Color')->maxLength(7),
                                        TextInput::make('brand_dark_color')->label('Dark Color')->maxLength(7),
                                        TextInput::make('brand_neutral_color')->label('Neutral Color')->maxLength(7),
                                        TextInput::make('workflow_current_color')->label('Workflow Current Step Color')->maxLength(7),
                                        TextInput::make('workflow_completed_color')->label('Workflow Completed Step Color')->maxLength(7),
                                        TextInput::make('workflow_pending_color')->label('Workflow Pending Step Color')->maxLength(7),
                                        TextInput::make('login_page_title')->maxLength(255),
                                        TextInput::make('login_brand_label')->maxLength(255),
                                        TextInput::make('login_hero_title')->maxLength(255),
                                        Textarea::make('login_hero_copy')->rows(3),
                                        TextInput::make('login_left_label')->maxLength(255),
                                        Textarea::make('login_left_copy')->rows(3),
                                        TextInput::make('login_right_label')->maxLength(255),
                                        Textarea::make('login_right_copy')->rows(3),
                                        TextInput::make('login_help_line_one')->maxLength(255),
                                        TextInput::make('login_help_line_two')->maxLength(255),
                                        TextInput::make('login_help_line_three')->maxLength(255),
                                    ])->columns(2),
                            ]),
                        Tab::make('Storage')
                            ->icon('heroicon-o-cloud')
                            ->schema([
                                Section::make('File Storage')
                                    ->schema([
                                        Select::make('storage_provider')
                                            ->options([
                                                'local' => 'Local Server',
                                                'cloudinary' => 'Cloudinary',
                                            ])
                                            ->required()
                                            ->default('local')
                                            ->reactive(),
                                        TextInput::make('cloudinary_folder')
                                            ->label('Cloudinary Folder')
                                            ->placeholder('purple-hr')
                                            ->maxLength(255),
                                        Toggle::make('cloudinary_secure_delivery')
                                            ->label('Use Secure Delivery (HTTPS)')
                                            ->default(true),
                                        TextInput::make('cloudinary_cloud_name')
                                            ->required(fn (Get $get): bool => $get('storage_provider') === 'cloudinary')
                                            ->maxLength(255),
                                        TextInput::make('cloudinary_api_key')
                                            ->required(fn (Get $get): bool => $get('storage_provider') === 'cloudinary')
                                            ->maxLength(255),
                                        TextInput::make('cloudinary_api_secret')
                                            ->password()
                                            ->revealable()
                                            ->required(fn (Get $get): bool => $get('storage_provider') === 'cloudinary')
                                            ->maxLength(255),
                                    ])->columns(2),
                            ]),
                        Tab::make('Communication')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Section::make('Email Delivery')
                                    ->schema([
                                        Select::make('mail_mailer')
                                            ->label('Mailer')
                                            ->options([
                                                'log' => 'Log (No external delivery)',
                                                'smtp' => 'SMTP',
                                                'ses' => 'AWS SES',
                                            ])
                                            ->required()
                                            ->default('log')
                                            ->reactive(),
                                        Toggle::make('ses_enabled')
                                            ->label('Enable AWS SES')
                                            ->default(false)
                                            ->reactive(),
                                        TextInput::make('ses_region')
                                            ->label('SES Region')
                                            ->placeholder('us-east-1')
                                            ->required(fn (Get $get): bool => (bool) $get('ses_enabled'))
                                            ->maxLength(100),
                                        TextInput::make('ses_access_key_id')
                                            ->label('SES Access Key ID')
                                            ->required(fn (Get $get): bool => (bool) $get('ses_enabled'))
                                            ->maxLength(255),
                                        TextInput::make('ses_secret_access_key')
                                            ->label('SES Secret Access Key')
                                            ->password()
                                            ->revealable()
                                            ->required(fn (Get $get): bool => (bool) $get('ses_enabled'))
                                            ->maxLength(255),
                                        TextInput::make('ses_configuration_set')
                                            ->label('SES Configuration Set')
                                            ->maxLength(255),
                                        TextInput::make('mail_from_address')
                                            ->label('From Address')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('mail_from_name')
                                            ->label('From Name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('mail_reply_to_address')
                                            ->label('Reply-To Address')
                                            ->email()
                                            ->maxLength(255),
                                    ]),
                                Section::make('Offboarding Notifications')
                                    ->description('Use tokens: {employee_name}, {employee_id}, {from_status}, {to_status}, {offboarding_type}, {last_working_day}, {offboarding_reason}, {completed_by}, {completed_at}, {company_name}.')
                                    ->schema([
                                        Toggle::make('offboarding_notify_on_planned')
                                            ->label('Send notification when offboarding is planned')
                                            ->default(true),
                                        Toggle::make('offboarding_notify_on_completed')
                                            ->label('Send notification when offboarding is completed')
                                            ->default(true),
                                        TextInput::make('offboarding_planned_subject')
                                            ->label('Planned Subject')
                                            ->maxLength(255)
                                            ->default('Offboarding Initiated - {employee_name}'),
                                        Textarea::make('offboarding_planned_body')
                                            ->label('Planned Body')
                                            ->rows(3)
                                            ->default('Employee {employee_name} ({employee_id}) has entered offboarding. Status: {from_status} -> {to_status}. Last working day: {last_working_day}. Type: {offboarding_type}. Reason: {offboarding_reason}.'),
                                        TextInput::make('offboarding_completed_subject')
                                            ->label('Completed Subject')
                                            ->maxLength(255)
                                            ->default('Offboarding Completed - {employee_name}'),
                                        Textarea::make('offboarding_completed_body')
                                            ->label('Completed Body')
                                            ->rows(3)
                                            ->default('Offboarding for {employee_name} ({employee_id}) is complete. Completed by: {completed_by}. Completed at: {completed_at}.'),
                                    ])->columns(2),
                            ]),
                        Tab::make('Learning')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Section::make('Learning Notifications')
                                    ->description('Use tokens like {employee_name}, {course_title}, {due_date}, {completion_percent}, {company_name}.')
                                    ->schema([
                                        Toggle::make('learning_notify_on_assignment')
                                            ->label('Send assignment notification email')
                                            ->default(true),
                                        Toggle::make('learning_notify_on_reminder')
                                            ->label('Send reminder notification email')
                                            ->default(true),
                                        Toggle::make('learning_notify_on_completion')
                                            ->label('Send completion notification email')
                                            ->default(true),
                                        TextInput::make('learning_assignment_subject')
                                            ->label('Assignment Subject')
                                            ->maxLength(255)
                                            ->default('New Learning Assignment: {course_title}'),
                                        Textarea::make('learning_assignment_body')
                                            ->label('Assignment Body')
                                            ->rows(3)
                                            ->default('Hello {employee_name}, you have been enrolled in {course_title}. Due date: {due_date}.'),
                                        TextInput::make('learning_reminder_subject')
                                            ->label('Reminder Subject')
                                            ->maxLength(255)
                                            ->default('Learning Reminder: {course_title}'),
                                        Textarea::make('learning_reminder_body')
                                            ->label('Reminder Body')
                                            ->rows(3)
                                            ->default('Hello {employee_name}, this is a reminder to complete {course_title} by {due_date}.'),
                                        TextInput::make('learning_completion_subject')
                                            ->label('Completion Subject')
                                            ->maxLength(255)
                                            ->default('Learning Completed: {course_title}'),
                                        Textarea::make('learning_completion_body')
                                            ->label('Completion Body')
                                            ->rows(3)
                                            ->default('Hello {employee_name}, completion has been recorded for {course_title}. Great work.'),
                                    ])->columns(2),
                                Section::make('Certificate Template (Prep)')
                                    ->description('These fields are used by the certificate PDF generator.')
                                    ->schema([
                                        TextInput::make('learning_certificate_title')
                                            ->label('Certificate Title')
                                            ->maxLength(255)
                                            ->default('Certificate of Completion'),
                                        TextInput::make('learning_certificate_subtitle')
                                            ->label('Certificate Subtitle')
                                            ->maxLength(255)
                                            ->default('This certifies that {employee_name} completed {course_title}.'),
                                        TextInput::make('learning_certificate_signatory_name')
                                            ->label('Signatory Name')
                                            ->maxLength(255)
                                            ->default('HR Manager'),
                                        TextInput::make('learning_certificate_signatory_title')
                                            ->label('Signatory Title')
                                            ->maxLength(255)
                                            ->default('People Operations'),
                                        Textarea::make('learning_certificate_footer_note')
                                            ->label('Footer Note')
                                            ->rows(2)
                                            ->default('System-generated learning certificate.'),
                                    ])->columns(2),
                            ]),
                        Tab::make('Payments')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Section::make('OPay Nigeria')
                                    ->schema([
                                        Toggle::make('opay_enabled')
                                            ->label('Enable OPay Payouts')
                                            ->default(false)
                                            ->reactive(),
                                        Toggle::make('opay_sandbox_mode')
                                            ->label('Sandbox Mode')
                                            ->default(true)
                                            ->helperText('Keep sandbox enabled until live credentials are verified.'),
                                        TextInput::make('opay_base_url')
                                            ->label('API Base URL')
                                            ->url()
                                            ->required(fn (Get $get): bool => (bool) $get('opay_enabled'))
                                            ->maxLength(255),
                                        TextInput::make('opay_transfer_path')
                                            ->label('Transfer Path')
                                            ->required(fn (Get $get): bool => (bool) $get('opay_enabled'))
                                            ->maxLength(255)
                                            ->default('/api/v1/transfers'),
                                        TextInput::make('opay_merchant_id')
                                            ->label('Merchant ID')
                                            ->maxLength(255),
                                        TextInput::make('opay_public_key')
                                            ->label('Public Key')
                                            ->maxLength(255),
                                        TextInput::make('opay_secret_key')
                                            ->label('Secret Key')
                                            ->password()
                                            ->revealable()
                                            ->required(fn (Get $get): bool => (bool) $get('opay_enabled'))
                                            ->maxLength(255),
                                    ])->columns(2),
                                Section::make('Kuda Bank')
                                    ->schema([
                                        Toggle::make('kuda_enabled')
                                            ->label('Enable Kuda Payouts')
                                            ->default(false)
                                            ->reactive(),
                                        Toggle::make('kuda_sandbox_mode')
                                            ->label('Sandbox Mode')
                                            ->default(true)
                                            ->helperText('Keep sandbox enabled until live credentials are verified.'),
                                        TextInput::make('kuda_base_url')
                                            ->label('API Base URL')
                                            ->url()
                                            ->required(fn (Get $get): bool => (bool) $get('kuda_enabled'))
                                            ->maxLength(255),
                                        TextInput::make('kuda_transfer_path')
                                            ->label('Transfer Path')
                                            ->required(fn (Get $get): bool => (bool) $get('kuda_enabled'))
                                            ->maxLength(255)
                                            ->default('/v2/disbursements'),
                                        TextInput::make('kuda_api_key')
                                            ->label('API Key')
                                            ->required(fn (Get $get): bool => (bool) $get('kuda_enabled'))
                                            ->maxLength(255),
                                        TextInput::make('kuda_secret_key')
                                            ->label('Secret Key')
                                            ->password()
                                            ->revealable()
                                            ->required(fn (Get $get): bool => (bool) $get('kuda_enabled'))
                                            ->maxLength(255),
                                        TextInput::make('kuda_client_email')
                                            ->label('Client Email')
                                            ->email()
                                            ->maxLength(255),
                                    ])->columns(2),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_person')
                    ->label('Contact')
                    ->toggleable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->dateTime('M j, Y H:i')
                    ->label('Updated')
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanySettings::route('/'),
            'create' => Pages\CreateCompanySettings::route('/create'),
            'edit' => Pages\EditCompanySettings::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManageSettings();
    }
}
