<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollPaymentResource\Pages;
use App\Models\PayrollPayment;
use App\Models\PayrollRun;
use App\Services\Payments\PayrollPaymentService;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollPaymentResource extends Resource
{
    protected static ?string $model = PayrollPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Payroll';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationLabel = 'Salary Payments';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Requested')->dateTime('M j, Y H:i')->sortable(),
                TextColumn::make('employee_name')->label('Employee')->searchable()->sortable(),
                TextColumn::make('provider')->badge()->formatStateUsing(fn (?string $state): string => strtoupper((string) $state))->sortable(),
                TextColumn::make('payment_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::paymentTypeLabel((string) $state))
                    ->sortable(),
                TextColumn::make('account_source')->label('Account')->formatStateUsing(fn (?string $state): string => ucfirst((string) $state))->sortable(),
                TextColumn::make('account_number')->label('Account No')->formatStateUsing(fn (?string $state): string => static::maskAccount((string) $state)),
                TextColumn::make('amount')->money('NGN', true)->sortable(),
                TextColumn::make('payment_note')->label('Note')->limit(40)->placeholder('-')->toggleable(),
                BadgeColumn::make('status')->colors([
                    'gray' => 'pending',
                    'warning' => 'processing',
                    'success' => 'paid',
                    'danger' => 'failed',
                ]),
                TextColumn::make('provider_reference')->label('Reference')->placeholder('-')->toggleable(),
                TextColumn::make('failure_reason')->label('Failure')->limit(40)->placeholder('-')->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->visible(fn (PayrollPayment $record): bool => in_array($record->status, ['failed', 'pending'], true))
                    ->action(function (PayrollPayment $record, PayrollPaymentService $payments): void {
                        $result = $payments->processPayment($record);

                        Notification::make()
                            ->title($result['success'] ? 'Payment processed' : 'Payment failed')
                            ->body($result['message'] ?? null)
                            ->{$result['success'] ? 'success' : 'danger'}()
                            ->send();
                    }),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollPayments::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canManagePayroll();
    }

    public static function paymentActionSchema(?PayrollRun $run = null): array
    {
        return [
            Select::make('provider')
                ->label('Payout Provider')
                ->options([
                    'opay' => 'OPay Nigeria',
                    'kuda' => 'Kuda Bank',
                ])
                ->default('opay')
                ->required(),
            Select::make('payroll_run_id')
                ->label('Payroll Run')
                ->options(fn (): array => PayrollRun::query()->orderByDesc('id')->pluck('run_code', 'id')->all())
                ->default($run?->id)
                ->hidden(fn (): bool => $run !== null)
                ->searchable()
                ->reactive(),
            Select::make('user_id')
                ->label('Employee')
                ->required()
                ->searchable()
                ->reactive()
                ->options(function (Get $get) use ($run): array {
                    $selectedRunId = $run?->id ?: (int) ($get('payroll_run_id') ?: 0);

                    if ($selectedRunId > 0) {
                        return PayrollRun::query()
                            ->whereKey($selectedRunId)
                            ->first()?->employees()
                            ->orderBy('employee_name')
                            ->pluck('employee_name', 'user_id')
                            ->all() ?? [];
                    }

                    return \App\Models\User::query()->orderBy('name')->pluck('name', 'user_id')->all();
                })
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) use ($run): void {
                    $set('account_source', null);

                    if (blank($state)) {
                        return;
                    }

                    $selectedRunId = $run?->id ?: (int) ($get('payroll_run_id') ?: 0);
                    if ($selectedRunId > 0) {
                        $amount = static::suggestedAmountFor($selectedRunId, $state);
                        if ($amount > 0) {
                            $set('amount', $amount);
                        }
                    }
                }),
            Select::make('account_source')
                ->label('Account Details')
                ->required()
                ->options(fn (Get $get): array => app(PayrollPaymentService::class)->accountOptionsForUser($get('user_id')))
                ->helperText('Choose the employee primary or secondary account to be paid.'),
            Select::make('payment_type')
                ->label('Payment Type')
                ->required()
                ->default('salary')
                ->options(fn (): array => app(PayrollPaymentService::class)->paymentTypeOptions()),
            Textarea::make('payment_note')
                ->label('Payment Note')
                ->rows(2)
                ->maxLength(500)
                ->placeholder('Optional note sent to provider as narration/description.'),
            TextInput::make('amount')
                ->label('Amount (NGN)')
                ->numeric()
                ->required()
                ->minValue(0.01),
        ];
    }

    public static function processPaymentAction(array $data, PayrollPaymentService $payments): array
    {
        $payment = $payments->createManual(
            userId: (string) $data['user_id'],
            payrollRunId: ! empty($data['payroll_run_id']) ? (int) $data['payroll_run_id'] : null,
            provider: (string) $data['provider'],
            accountSource: (string) $data['account_source'],
            amount: (float) $data['amount'],
            currency: 'NGN',
            paymentType: (string) ($data['payment_type'] ?? 'salary'),
            paymentNote: $data['payment_note'] ?? null,
        );

        $result = $payments->processPayment($payment);

        return [
            'success' => (bool) ($result['success'] ?? false),
            'message' => $result['message'] ?? null,
            'payment_id' => $payment->id,
        ];
    }

    private static function suggestedAmountFor(int $runId, string $userId): float
    {
        $row = \App\Models\PayrollRunEmployee::query()
            ->where('payroll_run_id', $runId)
            ->where('user_id', $userId)
            ->first();

        if (! $row) {
            return 0.0;
        }

        return (float) ($row->total_paid ?? $row->net_salary ?? 0);
    }

    private static function maskAccount(string $accountNumber): string
    {
        $value = trim($accountNumber);
        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', max(0, $length - 4)) . substr($value, -4);
    }

    private static function paymentTypeLabel(string $type): string
    {
        $options = app(PayrollPaymentService::class)->paymentTypeOptions();
        $key = strtolower(trim($type));

        return $options[$key] ?? 'Other';
    }
}
