<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\BankInformation;
use App\Models\CompanySettings;
use App\Models\Employee;
use App\Models\EmployeeFamilyMember;
use App\Models\EmployeeEducation;
use App\Models\EmployeeExperience;
use App\Models\EmployeeDocument;
use App\Models\EmployeeReference;
use App\Models\EmployeeOffboarding;
use App\Models\EmployeeOnboarding;
use App\Models\EmployeeStatutoryProfile;
use App\Models\PersonalInformation;
use App\Models\ProfileInformation;
use App\Models\StaffSalary;
use App\Models\User;
use App\Models\UserEmergencyContact;
use App\Support\OffboardingNotificationManager;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'People';
    protected static ?int $navigationSort = 10;
    protected static ?string $modelLabel = 'Employee';
    protected static ?string $pluralModelLabel = 'Employees';
    private static ?array $fieldRuleCache = null;
    private static ?array $statusOptionsCache = null;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Employee Details')
                    ->tabs([
                        Tabs\Tab::make('Core')
                            ->schema([
                                Section::make('Core Employee')
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->required(fn (): bool => static::isFieldRequired('first_name'))
                                            ->maxLength(255),
                                        TextInput::make('last_name')
                                            ->required(fn (): bool => static::isFieldRequired('last_name'))
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->required(fn (): bool => static::isFieldRequired('email'))
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('employee_id')
                                            ->label('Employee ID')
                                            ->required(fn (): bool => static::isFieldRequired('employee_id'))
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true),
                                        Select::make('gender')
                                            ->required(fn (): bool => static::isFieldRequired('gender'))
                                            ->options([
                                                'Male' => 'Male',
                                                'Female' => 'Female',
                                            ]),
                                        DatePicker::make('birth_date')
                                            ->required(fn (): bool => static::isFieldRequired('birth_date'))
                                            ->native(false),
                                        TextInput::make('company')
                                            ->required(fn (): bool => static::isFieldRequired('company'))
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Select::make('status')
                                            ->label('Employment Status')
                                            ->options(fn (): array => static::statusOptions())
                                            ->searchable()
                                            ->required(fn (): bool => static::isFieldRequired('status')),
                                        TextInput::make('phone_number')->label('Phone Number')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('phone_number')),
                                        TextInput::make('department')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('department')),
                                        TextInput::make('designation')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('designation')),
                                        TextInput::make('reports_to')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('reports_to')),
                                    ])
                                    ->columns(2),
                            ]),
                        Tabs\Tab::make('Personal')
                            ->schema([
                                Section::make('Profile Information')
                                    ->schema([
                                        TextInput::make('address')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('address')),
                                        TextInput::make('state')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('state')),
                                        TextInput::make('country')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('country')),
                                        TextInput::make('pin_code')->label('Postal / PIN Code')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('pin_code')),
                                    ])
                                    ->columns(2),
                                Section::make('Personal Information')
                                    ->schema([
                                        TextInput::make('passport_no')->label('Passport No')->maxLength(255),
                                        DatePicker::make('passport_expiry_date')->label('Passport Expiry Date')->native(false),
                                        TextInput::make('tel')->label('Alternative Telephone')->maxLength(255),
                                        TextInput::make('nationality')->maxLength(255),
                                        TextInput::make('religion')->maxLength(255),
                                        TextInput::make('marital_status')->maxLength(255),
                                        TextInput::make('employment_of_spouse')->label('Employment of Spouse')->maxLength(255),
                                        TextInput::make('children')->label('Number of Children')->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),
                        Tabs\Tab::make('Bank')
                            ->schema([
                                Section::make('Bank Information')
                                    ->schema([
                                        TextInput::make('primary_bank_name')->label('Primary Bank Name')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('primary_bank_name')),
                                        TextInput::make('primary_bank_account_no')->label('Primary Bank Account Number')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('primary_bank_account_no')),
                                        TextInput::make('primary_ifsc_code')->label('Primary Bank Code (NIP)')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('primary_ifsc_code')),
                                        TextInput::make('primary_pan_no')->label('Primary PAN / Tax ID')->maxLength(255),
                                        TextInput::make('secondary_bank_name')->label('Secondary Bank Name')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('secondary_bank_name')),
                                        TextInput::make('secondary_bank_account_no')->label('Secondary Bank Account Number')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('secondary_bank_account_no')),
                                        TextInput::make('secondary_ifsc_code')->label('Secondary Bank Code (NIP)')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('secondary_ifsc_code')),
                                        TextInput::make('secondary_pan_no')->label('Secondary PAN / Tax ID')->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),
                        Tabs\Tab::make('Payroll & Statutory')
                            ->schema([
                                Section::make('Payroll & Statutory Defaults')
                                    ->schema([
                                        TextInput::make('salary_amount')->label('Monthly Gross Salary')->numeric()->minValue(0)->prefix('₦')
                                            ->required(fn (): bool => static::isFieldRequired('salary_amount')),
                                        Select::make('salary_basis')
                                            ->options([
                                                'hourly' => 'Hourly',
                                                'daily' => 'Daily',
                                                'weekly' => 'Weekly',
                                                'monthly' => 'Monthly',
                                            ]),
                                        Select::make('payment_type')
                                            ->options([
                                                'bank_transfer' => 'Bank Transfer',
                                                'cash' => 'Cash',
                                                'cheque' => 'Cheque',
                                            ]),
                                        TextInput::make('tax_station')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('tax_station')),
                                        TextInput::make('tax_residency_state')->maxLength(255)
                                            ->required(fn (): bool => static::isFieldRequired('tax_residency_state')),
                                        Toggle::make('pension_enabled')->default(true),
                                        TextInput::make('employee_pension_rate_percent')->label('Employee Pension Rate (%)')->numeric()->minValue(0)->maxValue(100),
                                        TextInput::make('employer_pension_rate_percent')->label('Employer Pension Rate (%)')->numeric()->minValue(0)->maxValue(100),
                                        TextInput::make('pension_pin')->maxLength(255),
                                        Toggle::make('nhf_enabled')->default(false),
                                        TextInput::make('nhf_rate_percent')->label('NHF Rate (%)')->numeric()->minValue(0)->maxValue(100),
                                        TextInput::make('nhf_base_cap')->label('NHF Base Cap')->numeric()->minValue(0),
                                        TextInput::make('nhf_number')->maxLength(255),
                                        TextInput::make('annual_rent')->label('Annual Rent (Relief)')->numeric()->minValue(0)
                                            ->required(fn (): bool => static::isFieldRequired('annual_rent')),
                                        TextInput::make('other_statutory_deductions')->label('Other Statutory Deductions')->numeric()->minValue(0),
                                        TextInput::make('default_non_taxable_reimbursement')->label('Default Non-Taxable Reimbursement')->numeric()->minValue(0),
                                    ])
                                    ->columns(2),
                            ]),
                        Tabs\Tab::make('Contacts & Family')
                            ->schema([
                                Section::make('Emergency Contact')
                                    ->schema([
                                        TextInput::make('name_primary')->label('Primary Contact Name')->maxLength(255),
                                        TextInput::make('relationship_primary')->label('Primary Relationship')->maxLength(255),
                                        TextInput::make('phone_primary')->label('Primary Phone')->maxLength(255),
                                        TextInput::make('phone_2_primary')->label('Primary Alternate Phone')->maxLength(255),
                                        TextInput::make('name_secondary')->label('Secondary Contact Name')->maxLength(255),
                                        TextInput::make('relationship_secondary')->label('Secondary Relationship')->maxLength(255),
                                        TextInput::make('phone_secondary')->label('Secondary Phone')->maxLength(255),
                                        TextInput::make('phone_2_secondary')->label('Secondary Alternate Phone')->maxLength(255),
                                    ])
                                    ->columns(2),
                                Section::make('Family Information')
                                    ->schema([
                                        Repeater::make('family_members')
                                            ->label('Family Members')
                                            ->schema([
                                                TextInput::make('name')->required()->maxLength(255),
                                                TextInput::make('relationship')->maxLength(255),
                                                DatePicker::make('date_of_birth')->native(false),
                                                TextInput::make('phone')->maxLength(255),
                                                Toggle::make('is_next_of_kin')->label('Is Next of Kin'),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tabs\Tab::make('Onboarding & References')
                            ->schema([
                                Section::make('Education History')
                                    ->schema([
                                        Repeater::make('educations')
                                            ->schema([
                                                TextInput::make('institution')->required()->maxLength(255),
                                                TextInput::make('degree')->maxLength(255),
                                                TextInput::make('field_of_study')->label('Field of Study')->maxLength(255),
                                                DatePicker::make('start_date')->native(false),
                                                DatePicker::make('end_date')->native(false),
                                                TextInput::make('grade')->maxLength(255),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Onboarding Workflow')
                                    ->description('Track offer/contract signing flow, reference checks, and onboarding readiness.')
                                    ->schema([
                                        Select::make('onboarding_status')
                                            ->options([
                                                'draft' => 'Draft',
                                                'in_progress' => 'In Progress',
                                                'ready_to_join' => 'Ready to Join',
                                                'onboarded' => 'Onboarded',
                                                'cancelled' => 'Cancelled',
                                            ])
                                            ->default('draft')
                                            ->required(),
                                        DatePicker::make('planned_start_date')->native(false),
                                        Select::make('offer_status')
                                            ->options([
                                                'not_started' => 'Not Started',
                                                'sent' => 'Sent',
                                                'signed' => 'Signed',
                                                'declined' => 'Declined',
                                            ])
                                            ->default('not_started'),
                                        FileUpload::make('offer_document_upload')
                                            ->label('Offer Document (Upload PDF)')
                                            ->disk('public')
                                            ->directory('onboarding-documents')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->downloadable()
                                            ->openable()
                                            ->dehydrated(false)
                                            ->afterStateUpdated(fn ($state, callable $set): mixed => $set('offer_document_path', $state ? ('storage/' . ltrim((string) $state, '/')) : null)),
                                        TextInput::make('offer_document_path')
                                            ->label('Offer Document Path/URL')
                                            ->helperText('Use upload above or paste an external/public URL.')
                                            ->maxLength(255),
                                        Select::make('offer_sign_provider')
                                            ->label('Offer Signing Provider')
                                            ->options([
                                                'internal_upload' => 'Internal Upload',
                                                'purpleheros_internal' => 'PurpleHeros Internal',
                                                'manual_external' => 'Manual / External',
                                            ]),
                                        Repeater::make('offer_signers')
                                            ->label('Offer Signature Fields / Actors')
                                            ->helperText('Define signing order and field keys (Adobe-sign style routing, native PurpleHeros).')
                                            ->schema([
                                                TextInput::make('role_label')->label('Actor Role')->required()->maxLength(120),
                                                TextInput::make('signer_name')->label('Signer Name')->required()->maxLength(255),
                                                TextInput::make('signer_email')->label('Signer Email')->email()->required()->maxLength(255),
                                                TextInput::make('sign_order')->label('Signing Order')->numeric()->required()->minValue(1)->default(1),
                                                TextInput::make('signature_field_key')->label('Signature Field Key')->required()->maxLength(120)->default('SIGNATURE_1'),
                                                TextInput::make('page_number')->label('Page')->numeric()->required()->minValue(1)->default(1),
                                                TextInput::make('position_x')->label('X Position')->numeric()->minValue(0)->helperText('Signature box X coordinate on page.'),
                                                TextInput::make('position_y')->label('Y Position')->numeric()->minValue(0)->helperText('Signature box Y coordinate on page.'),
                                                TextInput::make('field_width')->label('Box Width')->numeric()->minValue(1)->helperText('Signature field width.'),
                                                TextInput::make('field_height')->label('Box Height')->numeric()->minValue(1)->helperText('Signature field height.'),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                        TextInput::make('offer_signature_request_id')
                                            ->label('Offer Signature Request ID')
                                            ->maxLength(255),
                                        DatePicker::make('offer_sent_at')->native(false),
                                        DatePicker::make('offer_signed_at')->native(false),
                                        Select::make('contract_status')
                                            ->options([
                                                'not_started' => 'Not Started',
                                                'sent' => 'Sent',
                                                'signed' => 'Signed',
                                                'declined' => 'Declined',
                                            ])
                                            ->default('not_started'),
                                        FileUpload::make('contract_document_upload')
                                            ->label('Contract Document (Upload PDF)')
                                            ->disk('public')
                                            ->directory('onboarding-documents')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->downloadable()
                                            ->openable()
                                            ->dehydrated(false)
                                            ->afterStateUpdated(fn ($state, callable $set): mixed => $set('contract_document_path', $state ? ('storage/' . ltrim((string) $state, '/')) : null)),
                                        TextInput::make('contract_document_path')
                                            ->label('Contract Document Path/URL')
                                            ->helperText('Use upload above or paste an external/public URL.')
                                            ->maxLength(255),
                                        Select::make('contract_sign_provider')
                                            ->label('Contract Signing Provider')
                                            ->options([
                                                'internal_upload' => 'Internal Upload',
                                                'purpleheros_internal' => 'PurpleHeros Internal',
                                                'manual_external' => 'Manual / External',
                                            ]),
                                        Repeater::make('contract_signers')
                                            ->label('Contract Signature Fields / Actors')
                                            ->helperText('Define signing order and field keys (Adobe-sign style routing, native PurpleHeros).')
                                            ->schema([
                                                TextInput::make('role_label')->label('Actor Role')->required()->maxLength(120),
                                                TextInput::make('signer_name')->label('Signer Name')->required()->maxLength(255),
                                                TextInput::make('signer_email')->label('Signer Email')->email()->required()->maxLength(255),
                                                TextInput::make('sign_order')->label('Signing Order')->numeric()->required()->minValue(1)->default(1),
                                                TextInput::make('signature_field_key')->label('Signature Field Key')->required()->maxLength(120)->default('SIGNATURE_1'),
                                                TextInput::make('page_number')->label('Page')->numeric()->required()->minValue(1)->default(1),
                                                TextInput::make('position_x')->label('X Position')->numeric()->minValue(0)->helperText('Signature box X coordinate on page.'),
                                                TextInput::make('position_y')->label('Y Position')->numeric()->minValue(0)->helperText('Signature box Y coordinate on page.'),
                                                TextInput::make('field_width')->label('Box Width')->numeric()->minValue(1)->helperText('Signature field width.'),
                                                TextInput::make('field_height')->label('Box Height')->numeric()->minValue(1)->helperText('Signature field height.'),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                        TextInput::make('contract_signature_request_id')
                                            ->label('Contract Signature Request ID')
                                            ->maxLength(255),
                                        DatePicker::make('contract_sent_at')->native(false),
                                        DatePicker::make('contract_signed_at')->native(false),
                                        Select::make('reference_check_status')
                                            ->options([
                                                'pending' => 'Pending',
                                                'in_progress' => 'In Progress',
                                                'completed' => 'Completed',
                                                'failed' => 'Failed',
                                            ])
                                            ->default('pending'),
                                        Select::make('background_check_status')
                                            ->options([
                                                'not_started' => 'Not Started',
                                                'in_progress' => 'In Progress',
                                                'cleared' => 'Cleared',
                                                'failed' => 'Failed',
                                            ])
                                            ->default('not_started'),
                                        Textarea::make('onboarding_notes')
                                            ->rows(3)
                                            ->maxLength(3000)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                                Section::make('Work Experience')
                                    ->schema([
                                        Repeater::make('experiences')
                                            ->schema([
                                                TextInput::make('company_name')->required()->maxLength(255),
                                                TextInput::make('job_title')->maxLength(255),
                                                TextInput::make('location')->maxLength(255),
                                                DatePicker::make('start_date')->native(false),
                                                DatePicker::make('end_date')->native(false),
                                                Toggle::make('is_current'),
                                                TextInput::make('summary')->columnSpanFull()->maxLength(2000),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Uploaded Documents')
                                    ->description('Employees upload CV and certification files from their profile. Admin can review and verify here.')
                                    ->schema([
                                        Repeater::make('documents')
                                            ->schema([
                                                TextInput::make('document_type')->required()->maxLength(255),
                                                TextInput::make('title')->maxLength(255),
                                                TextInput::make('file_path')->label('File Path')->required()->maxLength(255),
                                                Toggle::make('is_verified'),
                                                TextInput::make('verification_feedback')->label('Verification Feedback')->maxLength(2000),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Reference Checks')
                                    ->description('Employees can submit referees. Admin verifies references and logs feedback.')
                                    ->schema([
                                        Repeater::make('references')
                                            ->schema([
                                                TextInput::make('referee_name')->required()->maxLength(255),
                                                TextInput::make('relationship')->maxLength(255),
                                                TextInput::make('company_name')->maxLength(255),
                                                TextInput::make('job_title')->maxLength(255),
                                                TextInput::make('email')->email()->maxLength(255),
                                                TextInput::make('phone')->maxLength(255),
                                                TextInput::make('years_known')->maxLength(255),
                                                Toggle::make('is_verified')->label('Verified'),
                                                TextInput::make('verification_feedback')->label('Verification Notes')->maxLength(2000)->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tabs\Tab::make('Offboarding')
                            ->schema([
                                Section::make('Offboarding Workflow')
                                    ->description('Track employee exit workflow and completion checklist.')
                                    ->schema([
                                        Select::make('offboarding_status')
                                            ->options([
                                                'not_started' => 'Not Started',
                                                'planned' => 'Planned',
                                                'in_progress' => 'In Progress',
                                                'completed' => 'Completed',
                                                'cancelled' => 'Cancelled',
                                            ])
                                            ->default('not_started')
                                            ->required(),
                                        Select::make('offboarding_type')
                                            ->options([
                                                'resignation' => 'Resignation',
                                                'termination' => 'Termination',
                                                'end_of_contract' => 'End of Contract',
                                                'retirement' => 'Retirement',
                                                'other' => 'Other',
                                            ]),
                                        DatePicker::make('notice_submitted_on')->native(false),
                                        DatePicker::make('last_working_day')->native(false),
                                        DatePicker::make('exit_interview_date')->native(false),
                                        Toggle::make('exit_interview_completed')->default(false),
                                        Toggle::make('knowledge_transfer_completed')->default(false),
                                        Toggle::make('assets_returned')->default(false),
                                        Toggle::make('access_revoked')->default(false),
                                        Toggle::make('final_settlement_completed')->default(false),
                                        Toggle::make('rehire_eligible')->default(false),
                                        Textarea::make('offboarding_reason')
                                            ->rows(3)
                                            ->maxLength(2000)
                                            ->columnSpanFull(),
                                        Textarea::make('offboarding_notes')
                                            ->rows(4)
                                            ->maxLength(3000)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_id')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(function ($state, Employee $record): string {
                        $full = trim(((string) ($record->first_name ?? '')) . ' ' . ((string) ($record->last_name ?? '')));
                        return $full !== '' ? $full : (string) $state;
                    })
                    ->searchable(['name', 'first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Employment Status')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('offboarding_status')
                    ->label('Offboarding')
                    ->state(function (Employee $record): ?string {
                        $status = EmployeeOffboarding::query()
                            ->where('user_id', (string) $record->employee_id)
                            ->value('offboarding_status');

                        return $status ?: null;
                    })
                    ->badge()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('gender')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('company')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Employment Status')
                    ->options(fn (): array => static::statusOptions())
                    ->searchable(),
                SelectFilter::make('offboarding_status')
                    ->label('Offboarding')
                    ->options([
                        'not_started' => 'Not Started',
                        'planned' => 'Planned',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = (string) ($data['value'] ?? '');
                        if ($value === '') {
                            return $query;
                        }

                        return $query->whereIn('employee_id', EmployeeOffboarding::query()
                            ->where('offboarding_status', $value)
                            ->pluck('user_id'));
                    }),
                SelectFilter::make('gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                    ]),
                SelectFilter::make('company')
                    ->options(
                        fn (): array => Employee::query()
                            ->whereNotNull('company')
                            ->where('company', '!=', '')
                            ->distinct()
                            ->orderBy('company')
                            ->pluck('company', 'company')
                            ->all()
                    )
                    ->searchable(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Created from'),
                        DatePicker::make('created_until')->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }

    public static function extractRelatedData(array &$data): array
    {
        $relatedKeys = [
            'phone_number',
            'status',
            'department',
            'designation',
            'reports_to',
            'address',
            'state',
            'country',
            'pin_code',
            'passport_no',
            'passport_expiry_date',
            'tel',
            'nationality',
            'religion',
            'marital_status',
            'employment_of_spouse',
            'children',
            'bank_name',
            'bank_account_no',
            'ifsc_code',
            'pan_no',
            'primary_bank_name',
            'primary_bank_account_no',
            'primary_ifsc_code',
            'primary_pan_no',
            'secondary_bank_name',
            'secondary_bank_account_no',
            'secondary_ifsc_code',
            'secondary_pan_no',
            'salary_amount',
            'tax_station',
            'tax_residency_state',
            'pension_enabled',
            'employee_pension_rate_percent',
            'employer_pension_rate_percent',
            'pension_pin',
            'nhf_enabled',
            'nhf_rate_percent',
            'nhf_base_cap',
            'nhf_number',
            'annual_rent',
            'other_statutory_deductions',
            'default_non_taxable_reimbursement',
            'pf_enabled',
            'pf_number',
            'pf_contribution_rate_percent',
            'pf_additional_rate_percent',
            'esi_enabled',
            'esi_number',
            'esi_contribution_rate_percent',
            'esi_additional_rate_percent',
            'salary_basis',
            'payment_type',
            'name_primary',
            'relationship_primary',
            'phone_primary',
            'phone_2_primary',
            'name_secondary',
            'relationship_secondary',
            'phone_secondary',
            'phone_2_secondary',
            'family_members',
            'educations',
            'experiences',
            'documents',
            'references',
            'onboarding_status',
            'offer_status',
            'offer_document_path',
            'offer_signers',
            'offer_sign_provider',
            'offer_signature_request_id',
            'offer_sent_at',
            'offer_signed_at',
            'contract_status',
            'contract_document_path',
            'contract_signers',
            'contract_sign_provider',
            'contract_signature_request_id',
            'contract_sent_at',
            'contract_signed_at',
            'reference_check_status',
            'background_check_status',
            'planned_start_date',
            'onboarding_notes',
            'offboarding_status',
            'offboarding_type',
            'notice_submitted_on',
            'last_working_day',
            'exit_interview_date',
            'exit_interview_completed',
            'knowledge_transfer_completed',
            'assets_returned',
            'access_revoked',
            'final_settlement_completed',
            'rehire_eligible',
            'offboarding_reason',
            'offboarding_notes',
        ];

        $related = [];
        foreach ($relatedKeys as $key) {
            if (array_key_exists($key, $data)) {
                $related[$key] = $data[$key];
                unset($data[$key]);
            }
        }

        return $related;
    }

    public static function fillRelatedDataForRecord(Employee $record, array $data): array
    {
        $userId = (string) ($record->employee_id ?? '');
        if ($userId === '') {
            return $data;
        }

        $profile = ProfileInformation::query()->where('user_id', $userId)->first();
        $personal = PersonalInformation::query()->where('user_id', $userId)->first();
        $bank = BankInformation::query()->where('user_id', $userId)->first();
        $salary = StaffSalary::query()->where('user_id', $userId)->first();
        $statutory = EmployeeStatutoryProfile::query()->where('user_id', $userId)->first();
        $onboarding = EmployeeOnboarding::query()->where('user_id', $userId)->first();
        $offboarding = EmployeeOffboarding::query()->where('user_id', $userId)->first();
        $user = User::query()->where('user_id', $userId)->first();
        $data['first_name'] = $user?->first_name ?: $record->first_name;
        $data['last_name'] = $user?->last_name ?: $record->last_name;

        if (! $data['first_name'] && ! $data['last_name']) {
            $nameSource = trim((string) ($user?->name ?: $record->name ?: ''));
            if ($nameSource !== '') {
                $parts = preg_split('/\s+/', $nameSource) ?: [];
                $data['first_name'] = $parts[0] ?? null;
                $data['last_name'] = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;
            }
        }

        $data['phone_number'] = $profile?->phone_number ?? $user?->phone_number;
        $data['status'] = $user?->status;
        $data['department'] = $profile?->department ?? $user?->department;
        $data['designation'] = $profile?->designation ?? $user?->position;
        $data['reports_to'] = $profile?->reports_to;
        $data['address'] = $profile?->address;
        $data['state'] = $profile?->state;
        $data['country'] = $profile?->country;
        $data['pin_code'] = $profile?->pin_code;

        $data['passport_no'] = $personal?->passport_no;
        $data['passport_expiry_date'] = $personal?->passport_expiry_date;
        $data['tel'] = $personal?->tel;
        $data['nationality'] = $personal?->nationality;
        $data['religion'] = $personal?->religion;
        $data['marital_status'] = $personal?->marital_status;
        $data['employment_of_spouse'] = $personal?->employment_of_spouse;
        $data['children'] = $personal?->children;

        $data['primary_bank_name'] = $bank?->primary_bank_name ?: $bank?->bank_name;
        $data['primary_bank_account_no'] = $bank?->primary_bank_account_no ?: $bank?->bank_account_no;
        $data['primary_ifsc_code'] = $bank?->primary_ifsc_code ?: $bank?->ifsc_code;
        $data['primary_pan_no'] = $bank?->primary_pan_no ?: $bank?->pan_no;
        $data['secondary_bank_name'] = $bank?->secondary_bank_name;
        $data['secondary_bank_account_no'] = $bank?->secondary_bank_account_no;
        $data['secondary_ifsc_code'] = $bank?->secondary_ifsc_code;
        $data['secondary_pan_no'] = $bank?->secondary_pan_no;

        $data['salary_amount'] = $salary?->salary;

        $data['tax_station'] = $statutory?->tax_station;
        $data['tax_residency_state'] = $statutory?->tax_residency_state;
        $data['salary_basis'] = $statutory?->salary_basis;
        $data['payment_type'] = $statutory?->payment_type;
        $data['pension_enabled'] = $statutory?->pension_enabled ?? true;
        $data['employee_pension_rate_percent'] = $statutory?->employee_pension_rate_percent ?? 8;
        $data['employer_pension_rate_percent'] = $statutory?->employer_pension_rate_percent ?? 10;
        $data['pension_pin'] = $statutory?->pension_pin;
        $data['nhf_enabled'] = $statutory?->nhf_enabled ?? false;
        $data['nhf_rate_percent'] = $statutory?->nhf_rate_percent ?? 2.5;
        $data['nhf_base_cap'] = $statutory?->nhf_base_cap;
        $data['nhf_number'] = $statutory?->nhf_number;
        $data['annual_rent'] = $statutory?->annual_rent ?? 0;
        $data['other_statutory_deductions'] = $statutory?->other_statutory_deductions ?? 0;
        $data['default_non_taxable_reimbursement'] = $statutory?->default_non_taxable_reimbursement ?? 0;
        $data['pf_enabled'] = $statutory?->pf_enabled ?? false;
        $data['pf_number'] = $statutory?->pf_number;
        $data['pf_contribution_rate_percent'] = $statutory?->pf_contribution_rate_percent;
        $data['pf_additional_rate_percent'] = $statutory?->pf_additional_rate_percent;
        $data['esi_enabled'] = $statutory?->esi_enabled ?? false;
        $data['esi_number'] = $statutory?->esi_number;
        $data['esi_contribution_rate_percent'] = $statutory?->esi_contribution_rate_percent;
        $data['esi_additional_rate_percent'] = $statutory?->esi_additional_rate_percent;

        $data['onboarding_status'] = $onboarding?->onboarding_status ?? 'draft';
        $data['offer_status'] = $onboarding?->offer_status ?? 'not_started';
        $data['offer_document_path'] = $onboarding?->offer_document_path;
        $data['offer_document_upload'] = str_starts_with((string) ($onboarding?->offer_document_path ?? ''), 'storage/')
            ? substr((string) $onboarding->offer_document_path, 8)
            : null;
        $data['offer_signers'] = is_array($onboarding?->offer_signers_json) ? $onboarding->offer_signers_json : [];
        $data['offer_sign_provider'] = $onboarding?->offer_sign_provider;
        $data['offer_signature_request_id'] = $onboarding?->offer_signature_request_id;
        $data['offer_sent_at'] = optional($onboarding?->offer_sent_at)->format('Y-m-d');
        $data['offer_signed_at'] = optional($onboarding?->offer_signed_at)->format('Y-m-d');
        $data['contract_status'] = $onboarding?->contract_status ?? 'not_started';
        $data['contract_document_path'] = $onboarding?->contract_document_path;
        $data['contract_document_upload'] = str_starts_with((string) ($onboarding?->contract_document_path ?? ''), 'storage/')
            ? substr((string) $onboarding->contract_document_path, 8)
            : null;
        $data['contract_signers'] = is_array($onboarding?->contract_signers_json) ? $onboarding->contract_signers_json : [];
        $data['contract_sign_provider'] = $onboarding?->contract_sign_provider;
        $data['contract_signature_request_id'] = $onboarding?->contract_signature_request_id;
        $data['contract_sent_at'] = optional($onboarding?->contract_sent_at)->format('Y-m-d');
        $data['contract_signed_at'] = optional($onboarding?->contract_signed_at)->format('Y-m-d');
        $data['reference_check_status'] = $onboarding?->reference_check_status ?? 'pending';
        $data['background_check_status'] = $onboarding?->background_check_status ?? 'not_started';
        $data['planned_start_date'] = optional($onboarding?->planned_start_date)->format('Y-m-d');
        $data['onboarding_notes'] = $onboarding?->onboarding_notes;

        $data['offboarding_status'] = $offboarding?->offboarding_status ?? 'not_started';
        $data['offboarding_type'] = $offboarding?->offboarding_type;
        $data['notice_submitted_on'] = optional($offboarding?->notice_submitted_on)->format('Y-m-d');
        $data['last_working_day'] = optional($offboarding?->last_working_day)->format('Y-m-d');
        $data['exit_interview_date'] = optional($offboarding?->exit_interview_date)->format('Y-m-d');
        $data['exit_interview_completed'] = (bool) ($offboarding?->exit_interview_completed ?? false);
        $data['knowledge_transfer_completed'] = (bool) ($offboarding?->knowledge_transfer_completed ?? false);
        $data['assets_returned'] = (bool) ($offboarding?->assets_returned ?? false);
        $data['access_revoked'] = (bool) ($offboarding?->access_revoked ?? false);
        $data['final_settlement_completed'] = (bool) ($offboarding?->final_settlement_completed ?? false);
        $data['rehire_eligible'] = (bool) ($offboarding?->rehire_eligible ?? false);
        $data['offboarding_reason'] = $offboarding?->offboarding_reason;
        $data['offboarding_notes'] = $offboarding?->offboarding_notes;

        $emergency = UserEmergencyContact::query()->where('user_id', $userId)->first();
        $data['name_primary'] = $emergency?->name_primary;
        $data['relationship_primary'] = $emergency?->relationship_primary;
        $data['phone_primary'] = $emergency?->phone_primary;
        $data['phone_2_primary'] = $emergency?->phone_2_primary;
        $data['name_secondary'] = $emergency?->name_secondary;
        $data['relationship_secondary'] = $emergency?->relationship_secondary;
        $data['phone_secondary'] = $emergency?->phone_secondary;
        $data['phone_2_secondary'] = $emergency?->phone_2_secondary;

        $data['family_members'] = EmployeeFamilyMember::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get(['name', 'relationship', 'date_of_birth', 'phone', 'is_next_of_kin'])
            ->map(function (EmployeeFamilyMember $member): array {
                return [
                    'name' => $member->name,
                    'relationship' => $member->relationship,
                    'date_of_birth' => optional($member->date_of_birth)->format('Y-m-d'),
                    'phone' => $member->phone,
                    'is_next_of_kin' => (bool) $member->is_next_of_kin,
                ];
            })
            ->all();

        $data['educations'] = EmployeeEducation::query()
            ->where('user_id', $userId)
            ->orderByDesc('end_date')
            ->orderByDesc('start_date')
            ->get(['institution', 'degree', 'field_of_study', 'start_date', 'end_date', 'grade'])
            ->map(function (EmployeeEducation $education): array {
                return [
                    'institution' => $education->institution,
                    'degree' => $education->degree,
                    'field_of_study' => $education->field_of_study,
                    'start_date' => optional($education->start_date)->format('Y-m-d'),
                    'end_date' => optional($education->end_date)->format('Y-m-d'),
                    'grade' => $education->grade,
                ];
            })
            ->all();

        $data['experiences'] = EmployeeExperience::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_current')
            ->orderByDesc('end_date')
            ->orderByDesc('start_date')
            ->get(['company_name', 'job_title', 'location', 'start_date', 'end_date', 'is_current', 'summary'])
            ->map(function (EmployeeExperience $experience): array {
                return [
                    'company_name' => $experience->company_name,
                    'job_title' => $experience->job_title,
                    'location' => $experience->location,
                    'start_date' => optional($experience->start_date)->format('Y-m-d'),
                    'end_date' => optional($experience->end_date)->format('Y-m-d'),
                    'is_current' => (bool) $experience->is_current,
                    'summary' => $experience->summary,
                ];
            })
            ->all();

        $data['documents'] = EmployeeDocument::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get(['document_type', 'title', 'file_path', 'is_verified', 'verification_feedback'])
            ->map(fn (EmployeeDocument $document): array => [
                'document_type' => $document->document_type,
                'title' => $document->title,
                'file_path' => $document->file_path,
                'is_verified' => (bool) $document->is_verified,
                'verification_feedback' => $document->verification_feedback,
            ])
            ->all();

        $data['references'] = EmployeeReference::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_verified')
            ->orderBy('referee_name')
            ->get(['referee_name', 'relationship', 'company_name', 'job_title', 'email', 'phone', 'years_known', 'is_verified', 'verification_feedback'])
            ->map(fn (EmployeeReference $reference): array => [
                'referee_name' => $reference->referee_name,
                'relationship' => $reference->relationship,
                'company_name' => $reference->company_name,
                'job_title' => $reference->job_title,
                'email' => $reference->email,
                'phone' => $reference->phone,
                'years_known' => $reference->years_known,
                'is_verified' => (bool) $reference->is_verified,
                'verification_feedback' => $reference->verification_feedback,
            ])
            ->all();

        return $data;
    }

    public static function syncRelatedData(Employee $record, array $relatedData): void
    {
        $userId = (string) ($record->employee_id ?? '');
        if ($userId === '') {
            return;
        }

        $firstName = trim((string) ($relatedData['first_name'] ?? $record->first_name ?? ''));
        $lastName = trim((string) ($relatedData['last_name'] ?? $record->last_name ?? ''));
        $fullName = trim($firstName . ' ' . $lastName);

        $record->first_name = $firstName !== '' ? $firstName : null;
        $record->last_name = $lastName !== '' ? $lastName : null;
        $record->name = $fullName !== '' ? $fullName : (string) ($record->name ?? '');
        $record->save();

        User::query()->where('user_id', $userId)->update([
            'name' => $fullName !== '' ? $fullName : (string) ($record->name ?? ''),
            'first_name' => $firstName !== '' ? $firstName : null,
            'last_name' => $lastName !== '' ? $lastName : null,
            'email' => (string) ($record->email ?? ''),
            'phone_number' => $relatedData['phone_number'] ?? null,
            'status' => $relatedData['status'] ?? null,
            'department' => $relatedData['department'] ?? null,
            'position' => $relatedData['designation'] ?? null,
        ]);

        ProfileInformation::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'name' => $fullName !== '' ? $fullName : (string) ($record->name ?? ''),
                'first_name' => $firstName !== '' ? $firstName : null,
                'last_name' => $lastName !== '' ? $lastName : null,
                'email' => (string) ($record->email ?? ''),
                'birth_date' => $record->birth_date ? \Illuminate\Support\Carbon::parse($record->birth_date)->toDateString() : null,
                'gender' => $record->gender,
                'address' => $relatedData['address'] ?? null,
                'state' => $relatedData['state'] ?? null,
                'country' => $relatedData['country'] ?? null,
                'pin_code' => $relatedData['pin_code'] ?? null,
                'phone_number' => $relatedData['phone_number'] ?? null,
                'department' => $relatedData['department'] ?? null,
                'designation' => $relatedData['designation'] ?? null,
                'reports_to' => $relatedData['reports_to'] ?? null,
            ]
        );

        $hasPersonalData = collect([
            $relatedData['passport_no'] ?? null,
            $relatedData['passport_expiry_date'] ?? null,
            $relatedData['tel'] ?? null,
            $relatedData['nationality'] ?? null,
            $relatedData['religion'] ?? null,
            $relatedData['marital_status'] ?? null,
            $relatedData['employment_of_spouse'] ?? null,
            $relatedData['children'] ?? null,
        ])->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();

        if ($hasPersonalData || PersonalInformation::query()->where('user_id', $userId)->exists()) {
            PersonalInformation::query()->updateOrCreate(
                ['user_id' => $userId],
                [
                    'passport_no' => (string) ($relatedData['passport_no'] ?? ''),
                    'passport_expiry_date' => $relatedData['passport_expiry_date'] ?? null,
                    'tel' => $relatedData['tel'] ?? null,
                    'nationality' => $relatedData['nationality'] ?? null,
                    'religion' => $relatedData['religion'] ?? null,
                    'marital_status' => $relatedData['marital_status'] ?? null,
                    'employment_of_spouse' => $relatedData['employment_of_spouse'] ?? null,
                    'children' => $relatedData['children'] ?? null,
                ]
            );
        }

        BankInformation::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'bank_name' => $relatedData['primary_bank_name'] ?? null,
                'bank_account_no' => $relatedData['primary_bank_account_no'] ?? null,
                'ifsc_code' => $relatedData['primary_ifsc_code'] ?? null,
                'pan_no' => $relatedData['primary_pan_no'] ?? null,
                'primary_bank_name' => $relatedData['primary_bank_name'] ?? null,
                'primary_bank_account_no' => $relatedData['primary_bank_account_no'] ?? null,
                'primary_ifsc_code' => $relatedData['primary_ifsc_code'] ?? null,
                'primary_pan_no' => $relatedData['primary_pan_no'] ?? null,
                'secondary_bank_name' => $relatedData['secondary_bank_name'] ?? null,
                'secondary_bank_account_no' => $relatedData['secondary_bank_account_no'] ?? null,
                'secondary_ifsc_code' => $relatedData['secondary_ifsc_code'] ?? null,
                'secondary_pan_no' => $relatedData['secondary_pan_no'] ?? null,
            ]
        );

        if (isset($relatedData['salary_amount']) && $relatedData['salary_amount'] !== null && $relatedData['salary_amount'] !== '') {
            StaffSalary::query()->updateOrCreate(
                ['user_id' => $userId],
                [
                    'name' => (string) ($record->name ?? $userId),
                    'salary' => (string) $relatedData['salary_amount'],
                ]
            );
        }

        EmployeeStatutoryProfile::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'tax_station' => $relatedData['tax_station'] ?? null,
                'tax_residency_state' => $relatedData['tax_residency_state'] ?? null,
                'salary_basis' => $relatedData['salary_basis'] ?? null,
                'payment_type' => $relatedData['payment_type'] ?? null,
                'pension_enabled' => (bool) ($relatedData['pension_enabled'] ?? true),
                'employee_pension_rate_percent' => $relatedData['employee_pension_rate_percent'] ?? null,
                'employer_pension_rate_percent' => $relatedData['employer_pension_rate_percent'] ?? null,
                'pension_pin' => $relatedData['pension_pin'] ?? null,
                'nhf_enabled' => (bool) ($relatedData['nhf_enabled'] ?? false),
                'nhf_rate_percent' => $relatedData['nhf_rate_percent'] ?? null,
                'nhf_base_cap' => $relatedData['nhf_base_cap'] ?? null,
                'nhf_number' => $relatedData['nhf_number'] ?? null,
                'annual_rent' => (float) ($relatedData['annual_rent'] ?? 0),
                'other_statutory_deductions' => (float) ($relatedData['other_statutory_deductions'] ?? 0),
                'default_non_taxable_reimbursement' => (float) ($relatedData['default_non_taxable_reimbursement'] ?? 0),
                'pf_enabled' => (bool) ($relatedData['pf_enabled'] ?? false),
                'pf_number' => $relatedData['pf_number'] ?? null,
                'pf_contribution_rate_percent' => $relatedData['pf_contribution_rate_percent'] ?? null,
                'pf_additional_rate_percent' => $relatedData['pf_additional_rate_percent'] ?? null,
                'esi_enabled' => (bool) ($relatedData['esi_enabled'] ?? false),
                'esi_number' => $relatedData['esi_number'] ?? null,
                'esi_contribution_rate_percent' => $relatedData['esi_contribution_rate_percent'] ?? null,
                'esi_additional_rate_percent' => $relatedData['esi_additional_rate_percent'] ?? null,
            ]
        );

        UserEmergencyContact::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'name_primary' => $relatedData['name_primary'] ?? null,
                'relationship_primary' => $relatedData['relationship_primary'] ?? null,
                'phone_primary' => $relatedData['phone_primary'] ?? null,
                'phone_2_primary' => $relatedData['phone_2_primary'] ?? null,
                'name_secondary' => $relatedData['name_secondary'] ?? null,
                'relationship_secondary' => $relatedData['relationship_secondary'] ?? null,
                'phone_secondary' => $relatedData['phone_secondary'] ?? null,
                'phone_2_secondary' => $relatedData['phone_2_secondary'] ?? null,
            ]
        );

        EmployeeFamilyMember::query()->where('user_id', $userId)->delete();
        $familyMembers = $relatedData['family_members'] ?? [];
        if (is_array($familyMembers)) {
            foreach ($familyMembers as $member) {
                $name = trim((string) ($member['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                EmployeeFamilyMember::query()->create([
                    'user_id' => $userId,
                    'name' => $name,
                    'relationship' => $member['relationship'] ?? null,
                    'date_of_birth' => $member['date_of_birth'] ?? null,
                    'phone' => $member['phone'] ?? null,
                    'is_next_of_kin' => (bool) ($member['is_next_of_kin'] ?? false),
                ]);
            }
        }

        EmployeeEducation::query()->where('user_id', $userId)->delete();
        foreach (($relatedData['educations'] ?? []) as $education) {
            $institution = trim((string) ($education['institution'] ?? ''));
            if ($institution === '') {
                continue;
            }

            EmployeeEducation::query()->create([
                'user_id' => $userId,
                'institution' => $institution,
                'degree' => $education['degree'] ?? null,
                'field_of_study' => $education['field_of_study'] ?? null,
                'start_date' => $education['start_date'] ?? null,
                'end_date' => $education['end_date'] ?? null,
                'grade' => $education['grade'] ?? null,
                'created_by_user_id' => auth()->user()?->user_id,
            ]);
        }

        EmployeeExperience::query()->where('user_id', $userId)->delete();
        foreach (($relatedData['experiences'] ?? []) as $experience) {
            $companyName = trim((string) ($experience['company_name'] ?? ''));
            if ($companyName === '') {
                continue;
            }

            EmployeeExperience::query()->create([
                'user_id' => $userId,
                'company_name' => $companyName,
                'job_title' => $experience['job_title'] ?? null,
                'location' => $experience['location'] ?? null,
                'start_date' => $experience['start_date'] ?? null,
                'end_date' => $experience['end_date'] ?? null,
                'is_current' => (bool) ($experience['is_current'] ?? false),
                'summary' => $experience['summary'] ?? null,
                'created_by_user_id' => auth()->user()?->user_id,
            ]);
        }

        EmployeeDocument::query()->where('user_id', $userId)->delete();
        foreach (($relatedData['documents'] ?? []) as $document) {
            $filePath = trim((string) ($document['file_path'] ?? ''));
            $docType = trim((string) ($document['document_type'] ?? ''));
            if ($filePath === '' || $docType === '') {
                continue;
            }

            $isVerified = (bool) ($document['is_verified'] ?? false);
            EmployeeDocument::query()->create([
                'user_id' => $userId,
                'document_type' => $docType,
                'title' => $document['title'] ?? null,
                'file_path' => $filePath,
                'is_verified' => $isVerified,
                'verification_feedback' => $document['verification_feedback'] ?? null,
                'verified_by_user_id' => $isVerified ? auth()->user()?->user_id : null,
                'verified_at' => $isVerified ? now() : null,
                'uploaded_by_user_id' => auth()->user()?->user_id,
            ]);
        }

        EmployeeReference::query()->where('user_id', $userId)->delete();
        foreach (($relatedData['references'] ?? []) as $reference) {
            $refereeName = trim((string) ($reference['referee_name'] ?? ''));
            if ($refereeName === '') {
                continue;
            }

            $isVerified = (bool) ($reference['is_verified'] ?? false);
            EmployeeReference::query()->create([
                'user_id' => $userId,
                'referee_name' => $refereeName,
                'relationship' => $reference['relationship'] ?? null,
                'company_name' => $reference['company_name'] ?? null,
                'job_title' => $reference['job_title'] ?? null,
                'email' => $reference['email'] ?? null,
                'phone' => $reference['phone'] ?? null,
                'years_known' => $reference['years_known'] ?? null,
                'is_verified' => $isVerified,
                'verification_feedback' => $reference['verification_feedback'] ?? null,
                'verified_by_user_id' => $isVerified ? auth()->user()?->user_id : null,
                'verified_at' => $isVerified ? now() : null,
                'created_by_user_id' => auth()->user()?->user_id,
            ]);
        }

        $referencesTotal = EmployeeReference::query()->where('user_id', $userId)->count();
        $referencesVerified = EmployeeReference::query()->where('user_id', $userId)->where('is_verified', true)->count();
        $referenceStatus = (string) ($relatedData['reference_check_status'] ?? 'pending');
        if ($referencesTotal > 0 && $referencesVerified >= $referencesTotal) {
            $referenceStatus = 'completed';
        } elseif ($referencesVerified > 0) {
            $referenceStatus = 'in_progress';
        }

        $onboardingStatus = (string) ($relatedData['onboarding_status'] ?? 'draft');
        $onboardingCompletedAt = $onboardingStatus === 'onboarded' ? now() : null;
        $existingOnboarding = EmployeeOnboarding::query()->where('user_id', $userId)->first();

        EmployeeOnboarding::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'onboarding_status' => $onboardingStatus,
                'offer_status' => (string) ($relatedData['offer_status'] ?? 'not_started'),
                'offer_document_path' => $relatedData['offer_document_path'] ?? null,
                'offer_signers_json' => static::normalizeSignatureActors($relatedData['offer_signers'] ?? []),
                'offer_sign_provider' => $relatedData['offer_sign_provider'] ?? null,
                'offer_signature_request_id' => $relatedData['offer_signature_request_id'] ?? null,
                'offer_sent_at' => $relatedData['offer_sent_at'] ?? null,
                'offer_signed_at' => $relatedData['offer_signed_at'] ?? null,
                'contract_status' => (string) ($relatedData['contract_status'] ?? 'not_started'),
                'contract_document_path' => $relatedData['contract_document_path'] ?? null,
                'contract_signers_json' => static::normalizeSignatureActors($relatedData['contract_signers'] ?? []),
                'contract_sign_provider' => $relatedData['contract_sign_provider'] ?? null,
                'contract_signature_request_id' => $relatedData['contract_signature_request_id'] ?? null,
                'contract_sent_at' => $relatedData['contract_sent_at'] ?? null,
                'contract_signed_at' => $relatedData['contract_signed_at'] ?? null,
                'reference_check_status' => $referenceStatus,
                'references_total_count' => $referencesTotal,
                'references_verified_count' => $referencesVerified,
                'background_check_status' => (string) ($relatedData['background_check_status'] ?? 'not_started'),
                'planned_start_date' => $relatedData['planned_start_date'] ?? null,
                'onboarding_completed_at' => $onboardingCompletedAt,
                'onboarding_notes' => $relatedData['onboarding_notes'] ?? null,
                'created_by_user_id' => $existingOnboarding?->created_by_user_id ?: auth()->user()?->user_id,
                'updated_by_user_id' => auth()->user()?->user_id,
            ]
        );

        $offboardingStatus = (string) ($relatedData['offboarding_status'] ?? 'not_started');
        $hasOffboardingData = collect([
            $offboardingStatus !== 'not_started',
            $relatedData['offboarding_type'] ?? null,
            $relatedData['notice_submitted_on'] ?? null,
            $relatedData['last_working_day'] ?? null,
            $relatedData['exit_interview_date'] ?? null,
            $relatedData['offboarding_reason'] ?? null,
            $relatedData['offboarding_notes'] ?? null,
            (bool) ($relatedData['exit_interview_completed'] ?? false),
            (bool) ($relatedData['knowledge_transfer_completed'] ?? false),
            (bool) ($relatedData['assets_returned'] ?? false),
            (bool) ($relatedData['access_revoked'] ?? false),
            (bool) ($relatedData['final_settlement_completed'] ?? false),
            (bool) ($relatedData['rehire_eligible'] ?? false),
        ])->contains(fn ($value) => (bool) $value);

        if ($hasOffboardingData || EmployeeOffboarding::query()->where('user_id', $userId)->exists()) {
            $completionMarked = $offboardingStatus === 'completed';
            $existing = EmployeeOffboarding::query()->where('user_id', $userId)->first();

            $updated = EmployeeOffboarding::query()->updateOrCreate(
                ['user_id' => $userId],
                [
                    'offboarding_status' => $offboardingStatus,
                    'offboarding_type' => $relatedData['offboarding_type'] ?? null,
                    'notice_submitted_on' => $relatedData['notice_submitted_on'] ?? null,
                    'last_working_day' => $relatedData['last_working_day'] ?? null,
                    'exit_interview_date' => $relatedData['exit_interview_date'] ?? null,
                    'exit_interview_completed' => (bool) ($relatedData['exit_interview_completed'] ?? false),
                    'knowledge_transfer_completed' => (bool) ($relatedData['knowledge_transfer_completed'] ?? false),
                    'assets_returned' => (bool) ($relatedData['assets_returned'] ?? false),
                    'access_revoked' => (bool) ($relatedData['access_revoked'] ?? false),
                    'final_settlement_completed' => (bool) ($relatedData['final_settlement_completed'] ?? false),
                    'rehire_eligible' => (bool) ($relatedData['rehire_eligible'] ?? false),
                    'offboarding_reason' => $relatedData['offboarding_reason'] ?? null,
                    'offboarding_notes' => $relatedData['offboarding_notes'] ?? null,
                    'initiated_by_user_id' => $existing?->initiated_by_user_id ?: auth()->user()?->user_id,
                    'completed_by_user_id' => $completionMarked ? auth()->user()?->user_id : null,
                    'completed_at' => $completionMarked ? now() : null,
                ]
            );

            $previousStatus = strtolower((string) ($existing?->offboarding_status ?: 'not_started'));
            $currentStatus = strtolower((string) ($updated->offboarding_status ?: 'not_started'));
            if ($previousStatus !== $currentStatus && in_array($currentStatus, ['planned', 'completed'], true)) {
                OffboardingNotificationManager::sendStatusTransition($updated, $previousStatus, $currentStatus);
            }
        }
    }

    /**
     * @return array<string,string>
     */
    private static function normalizeSignatureActors(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $normalized = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $email = trim((string) ($row['signer_email'] ?? ''));
            if ($email === '') {
                continue;
            }

            $normalized[] = [
                'role_label' => trim((string) ($row['role_label'] ?? 'Signer')),
                'signer_name' => trim((string) ($row['signer_name'] ?? '')),
                'signer_email' => $email,
                'sign_order' => max(1, (int) ($row['sign_order'] ?? 1)),
                'signature_field_key' => trim((string) ($row['signature_field_key'] ?? 'SIGNATURE_1')),
                'page_number' => max(1, (int) ($row['page_number'] ?? 1)),
                'position_x' => isset($row['position_x']) && $row['position_x'] !== '' ? (float) $row['position_x'] : null,
                'position_y' => isset($row['position_y']) && $row['position_y'] !== '' ? (float) $row['position_y'] : null,
                'field_width' => isset($row['field_width']) && $row['field_width'] !== '' ? (float) $row['field_width'] : null,
                'field_height' => isset($row['field_height']) && $row['field_height'] !== '' ? (float) $row['field_height'] : null,
            ];
        }

        usort($normalized, fn (array $a, array $b): int => ((int) $a['sign_order']) <=> ((int) $b['sign_order']));

        return $normalized;
    }

    /**
     * @return array<string,string>
     */
    public static function fieldRequirementOptions(): array
    {
        return [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'employee_id' => 'Employee ID',
            'gender' => 'Gender',
            'birth_date' => 'Birth Date',
            'company' => 'Company',
            'status' => 'Employment Status',
            'phone_number' => 'Phone Number',
            'department' => 'Department',
            'designation' => 'Designation',
            'reports_to' => 'Reports To',
            'address' => 'Address',
            'state' => 'State',
            'country' => 'Country',
            'pin_code' => 'Postal / PIN Code',
            'primary_bank_name' => 'Primary Bank Name',
            'primary_bank_account_no' => 'Primary Bank Account Number',
            'primary_ifsc_code' => 'Primary Bank Code',
            'secondary_bank_name' => 'Secondary Bank Name',
            'secondary_bank_account_no' => 'Secondary Bank Account Number',
            'secondary_ifsc_code' => 'Secondary Bank Code',
            'salary_amount' => 'Monthly Gross Salary',
            'tax_station' => 'Tax Station',
            'tax_residency_state' => 'Tax Residency State',
            'annual_rent' => 'Annual Rent',
        ];
    }

    private static function isFieldRequired(string $field): bool
    {
        if (static::$fieldRuleCache === null) {
            static::$fieldRuleCache = CompanySettings::current()->employeeFieldRulesMap();
        }

        return (bool) (static::$fieldRuleCache[$field] ?? false);
    }

    /**
     * @return array<string,string>
     */
    private static function statusOptions(): array
    {
        if (static::$statusOptionsCache === null) {
            static::$statusOptionsCache = CompanySettings::current()->employeeStatusOptionsForSelect();
        }

        return static::$statusOptionsCache;
    }
}
