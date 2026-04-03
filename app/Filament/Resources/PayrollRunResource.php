<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollRunResource\Pages;
use App\Models\PayrollPolicySet;
use App\Models\PayrollRun;
use App\Services\Payments\PayrollPaymentService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollRunResource extends Resource
{
    protected static ?string $model = PayrollRun::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationLabel = 'Payroll Runs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('run_code')->required()->maxLength(64)->unique(ignoreRecord: true),
                Select::make('payroll_policy_set_id')
                    ->label('Policy Set')
                    ->options(fn (): array => PayrollPolicySet::query()->orderByDesc('effective_from')->pluck('name', 'id')->all())
                    ->searchable(),
                TextInput::make('period_year')->required()->numeric()->minValue(2000)->maxValue(2100),
                Select::make('period_month')
                    ->required()
                    ->options([
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ]),
                DatePicker::make('period_start')->required()->native(false),
                DatePicker::make('period_end')->required()->native(false),
                TextInput::make('default_worked_days')->required()->numeric()->minValue(1)->maxValue(31)->default(21),
                TextInput::make('default_total_working_days')->required()->numeric()->minValue(1)->maxValue(31)->default(21),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'calculated' => 'Calculated',
                        'approved' => 'Approved',
                        'posted' => 'Posted',
                        'locked' => 'Locked',
                    ])
                    ->default('draft')
                    ->required(),
                TextInput::make('notes')->maxLength(1000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('run_code')->searchable()->sortable(),
                TextColumn::make('period_year')->sortable(),
                TextColumn::make('period_month')
                    ->formatStateUsing(fn ($state): string => date('M', mktime(0, 0, 0, (int) $state, 1)))
                    ->sortable(),
                TextColumn::make('policySet.name')->label('Policy'),
                BadgeColumn::make('status')->colors([
                    'gray' => 'draft',
                    'info' => 'calculated',
                    'warning' => 'approved',
                    'success' => 'posted',
                    'danger' => 'locked',
                ]),
                TextColumn::make('employees_count')
                    ->counts('employees')
                    ->label('Employees'),
                TextColumn::make('calculated_at')->dateTime('M j, Y H:i')->placeholder('-')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('makePayment')
                    ->label('Make Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form(fn (PayrollRun $record): array => PayrollPaymentResource::paymentActionSchema($record))
                    ->action(function (PayrollRun $record, array $data, PayrollPaymentService $payments): void {
                        $data['payroll_run_id'] = $record->id;
                        $result = PayrollPaymentResource::processPaymentAction($data, $payments);

                        $notification = Notification::make()
                            ->title($result['success'] ? 'Payment processed' : 'Payment failed')
                            ->body($result['message'] ?? null);

                        if ($result['success']) {
                            $notification->success()->send();
                            return;
                        }

                        $notification->danger()->send();
                    }),
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
            'index' => Pages\ListPayrollRuns::route('/'),
            'create' => Pages\CreatePayrollRun::route('/create'),
            'edit' => Pages\EditPayrollRun::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePayroll();
    }
}
