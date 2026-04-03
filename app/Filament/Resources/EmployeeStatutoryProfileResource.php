<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeStatutoryProfileResource\Pages;
use App\Models\EmployeeStatutoryProfile;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeStatutoryProfileResource extends Resource
{
    protected static ?string $model = EmployeeStatutoryProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = 'Employee Statutory Profiles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Employee Link')
                    ->description('One statutory profile per employee.')
                    ->schema([
                        Select::make('user_id')
                            ->label('Employee')
                            ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                            ->searchable()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('tax_station')
                            ->placeholder('Lagos Mainland')
                            ->helperText('Used for remittance grouping and appears on generated payroll records.'),
                        TextInput::make('tax_residency_state')
                            ->label('Tax Residency State')
                            ->placeholder('Lagos'),
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
                    ])
                    ->columns(2),

                Section::make('Nigeria Statutory Deductions')
                    ->description('Defaults used by payroll run generation. You can still override values during manual adjustments.')
                    ->schema([
                        Toggle::make('pension_enabled')
                            ->label('Apply Employee Pension Deduction')
                            ->default(true),
                        TextInput::make('employee_pension_rate_percent')
                            ->label('Employee Pension Rate (%)')
                            ->numeric()
                            ->default(8)
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('employer_pension_rate_percent')
                            ->label('Employer Pension Rate (%)')
                            ->numeric()
                            ->default(10)
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('pension_pin')
                            ->label('Pension PIN (Optional)')
                            ->placeholder('PEN1234567890'),

                        Toggle::make('nhf_enabled')
                            ->label('Apply NHF Deduction')
                            ->default(false),
                        TextInput::make('nhf_rate_percent')
                            ->label('NHF Rate (%)')
                            ->numeric()
                            ->default(2.5)
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('nhf_base_cap')
                            ->label('NHF Base Cap (NGN, Optional)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Leave blank to apply NHF % on full monthly gross.'),
                        TextInput::make('nhf_number')
                            ->label('NHF Number (Optional)')
                            ->placeholder('NHF-00000001'),

                        TextInput::make('annual_rent')
                            ->label('Annual Rent for Relief (NGN)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('other_statutory_deductions')
                            ->label('Other Monthly Statutory Deduction (NGN)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('default_non_taxable_reimbursement')
                            ->label('Default Monthly Non-Taxable Reimbursement (NGN)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Employee')->searchable()->sortable(),
                TextColumn::make('user_id')->label('Employee ID')->searchable()->sortable(),
                TextColumn::make('tax_station')->sortable()->searchable()->placeholder('-'),
                IconColumn::make('pension_enabled')->label('Pension')->boolean(),
                TextColumn::make('employee_pension_rate_percent')->label('Pension %')->sortable()->suffix('%')->placeholder('-'),
                IconColumn::make('nhf_enabled')->label('NHF')->boolean(),
                TextColumn::make('nhf_rate_percent')->label('NHF %')->sortable()->suffix('%')->placeholder('-'),
                TextColumn::make('updated_at')->dateTime('M j, Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tax_station')
                    ->options(fn (): array => EmployeeStatutoryProfile::query()
                        ->whereNotNull('tax_station')
                        ->orderBy('tax_station')
                        ->pluck('tax_station', 'tax_station')
                        ->all()),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeStatutoryProfiles::route('/'),
            'create' => Pages\CreateEmployeeStatutoryProfile::route('/create'),
            'edit' => Pages\EditEmployeeStatutoryProfile::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePayroll();
    }
}
