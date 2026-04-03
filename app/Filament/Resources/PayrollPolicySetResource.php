<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollPolicySetResource\Pages;
use App\Models\PayrollPolicySet;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
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
use Filament\Tables\Table;

class PayrollPolicySetResource extends Resource
{
    protected static ?string $model = PayrollPolicySet::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Payroll Policies';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Policy Basics')
                    ->schema([
                        TextInput::make('name')
                            ->label('Policy Name')
                            ->placeholder('Purple Crayola Nigeria Payroll 2026 v1')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Policy Code')
                            ->placeholder('NG-2026-PC-V1')
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true),
                        Select::make('country_code')
                            ->label('Country')
                            ->options(['NG' => 'Nigeria'])
                            ->required()
                            ->default('NG'),
                        TextInput::make('state_code')
                            ->label('Default Remittance State (Optional)')
                            ->placeholder('Leave blank for multi-state payroll')
                            ->helperText('Set this only if this policy is for one state. Otherwise leave blank and use each employee tax station.')
                            ->maxLength(16),
                        TextInput::make('currency_code')
                            ->label('Currency')
                            ->required()
                            ->maxLength(3)
                            ->default('NGN'),
                        DatePicker::make('effective_from')
                            ->label('Effective From')
                            ->required()
                            ->native(false),
                        DatePicker::make('effective_to')
                            ->label('Effective To (Optional)')
                            ->native(false),
                        Toggle::make('is_active')
                            ->label('Set As Active Policy'),
                    ])
                    ->columns(2),
                Section::make('Tax Relief Settings')
                    ->description('Configure the annual relief values used in PAYE calculations.')
                    ->schema([
                        TextInput::make('settings.tax_free_threshold')
                            ->label('Annual Tax-Free Threshold (NGN)')
                            ->numeric()
                            ->default(800000)
                            ->required()
                            ->helperText('Example: 800000'),
                        TextInput::make('settings.rent_relief_percent')
                            ->label('Rent Relief Percentage (%)')
                            ->numeric()
                            ->default(20)
                            ->required()
                            ->helperText('Example: 20 for 20%'),
                        TextInput::make('settings.rent_relief_cap')
                            ->label('Rent Relief Cap (NGN)')
                            ->numeric()
                            ->default(500000)
                            ->required()
                            ->helperText('Maximum annual rent relief amount.'),
                    ])
                    ->columns(3),
                Section::make('Statutory Default Switches')
                    ->description('These are fallback defaults when an employee statutory profile is missing a value.')
                    ->schema([
                        Toggle::make('settings.default_pension_enabled')
                            ->label('Default Pension Enabled')
                            ->default(true),
                        TextInput::make('settings.default_employee_pension_rate_percent')
                            ->label('Default Employee Pension Rate (%)')
                            ->numeric()
                            ->default(8)
                            ->required()
                            ->minValue(0)
                            ->maxValue(100),
                        Toggle::make('settings.default_nhf_enabled')
                            ->label('Default NHF Enabled')
                            ->default(false),
                        TextInput::make('settings.default_nhf_rate_percent')
                            ->label('Default NHF Rate (%)')
                            ->numeric()
                            ->default(2.5)
                            ->required()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->columns(2),
                Section::make('PAYE Bands')
                    ->description('Add one row per annual taxable income band. Leave "Up To" empty on the last row to represent "and above".')
                    ->schema([
                        Repeater::make('settings.paye_bands')
                            ->label('Annual PAYE Band Table')
                            ->default([
                                ['up_to' => 2200000, 'rate' => 15],
                                ['up_to' => 11200000, 'rate' => 18],
                                ['up_to' => 24200000, 'rate' => 21],
                                ['up_to' => 49200000, 'rate' => 23],
                                ['up_to' => null, 'rate' => 25],
                            ])
                            ->schema([
                                TextInput::make('up_to')
                                    ->label('Up To (NGN)')
                                    ->numeric()
                                    ->placeholder('Leave blank for final band'),
                                TextInput::make('rate')
                                    ->label('Tax Rate (%)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(100),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->collapsed(false)
                            ->reorderableWithButtons()
                            ->columnSpanFull(),
                    ]),
                Section::make('Setup Guide')
                    ->schema([
                        Placeholder::make('policy_guide')
                            ->label('')
                            ->content('Use one Nigeria policy for all employees. Keep state blank for multi-state teams. Use employee Tax Station for remittance grouping, and create a new policy version only when rates or relief rules change.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('effective_from')->date('M j, Y')->sortable(),
                TextColumn::make('effective_to')->date('M j, Y')->placeholder('-')->sortable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->dateTime('M j, Y H:i')->sortable()->toggleable(),
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
            'index' => Pages\ListPayrollPolicySets::route('/'),
            'create' => Pages\CreatePayrollPolicySet::route('/create'),
            'edit' => Pages\EditPayrollPolicySet::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePayroll();
    }
}
