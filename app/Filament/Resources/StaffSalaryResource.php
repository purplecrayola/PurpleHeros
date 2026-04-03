<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffSalaryResource\Pages;
use App\Models\EmployeeStatutoryProfile;
use App\Models\StaffSalary;
use App\Models\User;
use App\Services\Payroll\PayrollCalculatorService;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffSalaryResource extends Resource
{
    protected static ?string $model = StaffSalary::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Payroll Records';
    protected static ?string $modelLabel = 'Payroll Record';
    protected static ?string $pluralModelLabel = 'Payroll Records';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Employee')
                    ->schema([
                        Select::make('user_id')
                            ->label('Employee')
                            ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                            ->searchable()
                            ->unique(ignoreRecord: true)
                            ->helperText('Create one active payroll record per employee for monthly processing.')
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if (blank($state)) {
                                    return;
                                }

                                $name = User::query()->where('user_id', $state)->value('name');
                                if (filled($name)) {
                                    $set('name', $name);
                                }
                            })
                            ->required(),
                        TextInput::make('name')
                            ->label('Employee Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('tax_station')
                            ->label('Tax Station (State)')
                            ->maxLength(255)
                            ->helperText('Example: Lagos, Abuja, Rivers. Used for payslip context.'),
                    ])->columns(2),
                Section::make('Monthly Inputs')
                    ->schema([
                        TextInput::make('salary')
                            ->label('Monthly Gross Salary')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->reactive(),
                        TextInput::make('worked_days')
                            ->label('Worked Days')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(31)
                            ->default(21)
                            ->reactive(),
                        TextInput::make('total_working_days')
                            ->label('Total Working Days')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->default(21)
                            ->reactive(),
                        TextInput::make('unpaid_days')
                            ->label('Unpaid Days')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->reactive(),
                    ])->columns(2),
                Section::make('Deductions')
                    ->schema([
                        TextInput::make('salary_advance')
                            ->label('Salary Advance (NGN)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->reactive(),
                        TextInput::make('kpi_other_deductions')
                            ->label('KPI / Other Deductions (NGN)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->reactive(),
                    ])->columns(2),
                Section::make('Reimbursements & Relief Inputs')
                    ->schema([
                        TextInput::make('annual_rent')
                            ->label('Annual Rent (NGN)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->reactive(),
                        TextInput::make('non_taxable_reimbursement')
                            ->label('Non-Taxable Reimbursement (NGN)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->reactive(),
                    ])->columns(2),
                Section::make('Estimated Monthly Summary')
                    ->description('Live preview from the Nigerian payroll calculator. Final values are generated and locked on Payroll Run.')
                    ->schema([
                        Placeholder::make('preview_gross')
                            ->label('Gross Salary')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['gross'])),
                        Placeholder::make('preview_paye')
                            ->label('PAYE (Estimated)')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['paye'])),
                        Placeholder::make('preview_pension')
                            ->label('Pension (Estimated)')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['pension'])),
                        Placeholder::make('preview_nhf')
                            ->label('NHF (Estimated)')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['nhf'])),
                        Placeholder::make('preview_unpaid_time')
                            ->label('Unpaid Time Deduction')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['unpaid_time_deduction'])),
                        Placeholder::make('preview_salary_advance')
                            ->label('Salary Advance')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['salary_advance'])),
                        Placeholder::make('preview_kpi_other')
                            ->label('KPI / Other Deductions')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['kpi_other_deductions'])),
                        Placeholder::make('preview_other_statutory')
                            ->label('Other Statutory Deductions')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['other_statutory_deductions'])),
                        Placeholder::make('preview_total_deductions')
                            ->label('Total Deductions')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['total_deductions'])),
                        Placeholder::make('preview_take_home')
                            ->label('Take-Home (Net Salary)')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['net_salary'])),
                        Placeholder::make('preview_total_paid')
                            ->label('Total Paid (Net + Reimbursement)')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['total_paid'])),
                        Placeholder::make('preview_rent_relief')
                            ->label('Rent Relief (Annual)')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['rent_relief'])),
                        Placeholder::make('preview_annual_taxable')
                            ->label('Annual Taxable Income')
                            ->content(fn (Get $get): string => static::formatMoney(static::calculatePreview($get)['annual_taxable'])),
                    ])->columns(3),
            ]);
    }

    /**
     * @return array{
     * gross:float,
     * paye:float,
     * pension:float,
     * nhf:float,
     * unpaid_time_deduction:float,
     * salary_advance:float,
     * kpi_other_deductions:float,
     * other_statutory_deductions:float,
     * total_deductions:float,
     * net_salary:float,
     * total_paid:float,
     * rent_relief:float,
     * annual_taxable:float
     * }
     */
    private static function calculatePreview(Get $get): array
    {
        $gross = (float) ($get('salary') ?? 0);
        $userId = $get('user_id');
        $profile = null;

        if (filled($userId)) {
            $profile = EmployeeStatutoryProfile::query()->where('user_id', (string) $userId)->first();
        }

        $input = [
            'monthly_gross' => $gross,
            'worked_days' => (int) ($get('worked_days') ?? 21),
            'total_working_days' => max(1, (int) ($get('total_working_days') ?? 21)),
            'unpaid_days' => (float) ($get('unpaid_days') ?? 0),
            'salary_advance' => (float) ($get('salary_advance') ?? 0),
            'kpi_other_deductions' => (float) ($get('kpi_other_deductions') ?? 0),
            'annual_rent' => (float) ($get('annual_rent') ?? 0),
            'non_taxable_reimbursement' => (float) ($get('non_taxable_reimbursement') ?? 0),
            'other_statutory_deductions' => (float) ($profile?->other_statutory_deductions ?? 0),
            'pension_enabled' => (bool) ($profile?->pension_enabled ?? true),
            'employee_pension_rate_percent' => (float) ($profile?->employee_pension_rate_percent ?? 8),
            'nhf_enabled' => (bool) ($profile?->nhf_enabled ?? false),
            'nhf_rate_percent' => (float) ($profile?->nhf_rate_percent ?? 2.5),
            'nhf_base_cap' => $profile?->nhf_base_cap,
        ];

        $policy = [
            'tax_free_threshold' => 800000,
            'rent_relief_percent' => 20,
            'rent_relief_cap' => 500000,
        ];

        $computed = app(PayrollCalculatorService::class)->calculate($input, $policy);

        return [
            'gross' => (float) ($input['monthly_gross'] ?? 0),
            'paye' => (float) ($computed['monthly_paye'] ?? 0),
            'pension' => (float) ($computed['monthly_pension'] ?? 0),
            'nhf' => (float) ($computed['monthly_nhf'] ?? 0),
            'unpaid_time_deduction' => (float) ($computed['unpaid_time_deduction'] ?? 0),
            'salary_advance' => (float) ($input['salary_advance'] ?? 0),
            'kpi_other_deductions' => (float) ($input['kpi_other_deductions'] ?? 0),
            'other_statutory_deductions' => (float) ($computed['other_statutory_deductions'] ?? 0),
            'total_deductions' => (float) ($computed['total_deductions'] ?? 0),
            'net_salary' => (float) ($computed['net_salary'] ?? 0),
            'total_paid' => (float) ($computed['total_paid'] ?? 0),
            'rent_relief' => (float) ($computed['rent_relief'] ?? 0),
            'annual_taxable' => (float) ($computed['annual_taxable'] ?? 0),
        ];
    }

    private static function formatMoney(float $amount): string
    {
        return 'NGN ' . number_format($amount, 2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_id')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('salary')
                    ->label('Monthly Gross')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2))
                    ->sortable(),
                TextColumn::make('tax_station')
                    ->label('Tax Station')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-'),
                TextColumn::make('worked_days')->label('Worked Days')->sortable()->toggleable()->placeholder('-'),
                TextColumn::make('total_working_days')->label('Total Days')->sortable()->toggleable()->placeholder('-'),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'user_id')->all())
                    ->searchable(),
                Filter::make('salary_band')
                    ->form([
                        TextInput::make('min_salary')
                            ->label('Min Gross')
                            ->numeric(),
                        TextInput::make('max_salary')
                            ->label('Max Gross')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min_salary'] ?? null, fn (Builder $q, $amount): Builder => $q->where('salary', '>=', (float) $amount))
                            ->when($data['max_salary'] ?? null, fn (Builder $q, $amount): Builder => $q->where('salary', '<=', (float) $amount));
                    }),
            ])
            ->actions([
                Action::make('exportPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (StaffSalary $record): string => url('/extra/report/pdf?user_id=' . urlencode((string) $record->user_id)))
                    ->openUrlInNewTab(),
                Action::make('exportXlsx')
                    ->label('XLSX')
                    ->icon('heroicon-o-table-cells')
                    ->url(fn (StaffSalary $record): string => url('/extra/report/excel?user_id=' . urlencode((string) $record->user_id)))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListStaffSalaries::route('/'),
            'create' => Pages\CreateStaffSalary::route('/create'),
            'edit' => Pages\EditStaffSalary::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePayroll();
    }
}
