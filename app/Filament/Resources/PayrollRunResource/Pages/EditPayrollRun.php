<?php

namespace App\Filament\Resources\PayrollRunResource\Pages;

use App\Filament\Resources\PayrollPaymentResource;
use App\Filament\Resources\PayrollRunResource;
use App\Models\PayrollRunEmployee;
use App\Services\Payments\PayrollPaymentService;
use App\Services\Payroll\PayrollRunGeneratorService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPayrollRun extends EditRecord
{
    protected static string $resource = PayrollRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('makePayment')
                ->label('Make Payment')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->form(fn (): array => PayrollPaymentResource::paymentActionSchema($this->record))
                ->action(function (array $data, PayrollPaymentService $payments): void {
                    $data['payroll_run_id'] = $this->record->id;
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
            Actions\Action::make('payMultiple')
                ->label('Pay Multiple')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->form([
                    Select::make('provider')
                        ->label('Payout Provider')
                        ->options([
                            'opay' => 'OPay Nigeria',
                            'kuda' => 'Kuda Bank',
                        ])
                        ->default('opay')
                        ->required(),
                    Select::make('account_source')
                        ->label('Account Source')
                        ->options([
                            'primary' => 'Primary Account',
                            'secondary' => 'Secondary Account',
                        ])
                        ->default('primary')
                        ->required(),
                    Select::make('payment_type')
                        ->label('Payment Type')
                        ->options(fn (): array => app(PayrollPaymentService::class)->paymentTypeOptions())
                        ->default('salary')
                        ->required(),
                    Textarea::make('payment_note')
                        ->label('Payment Note')
                        ->rows(2)
                        ->maxLength(500)
                        ->placeholder('Optional note to include in provider narration.'),
                    Select::make('user_ids')
                        ->label('Employees')
                        ->multiple()
                        ->searchable()
                        ->options(fn (): array => PayrollRunEmployee::query()
                            ->where('payroll_run_id', $this->record->id)
                            ->orderBy('employee_name')
                            ->pluck('employee_name', 'user_id')
                            ->all())
                        ->helperText('Leave empty to pay all employees in this payroll run.'),
                ])
                ->requiresConfirmation()
                ->action(function (array $data, PayrollPaymentService $payments): void {
                    $result = $payments->payRun(
                        run: $this->record,
                        provider: (string) $data['provider'],
                        accountSource: (string) $data['account_source'],
                        userIds: ! empty($data['user_ids']) ? array_values($data['user_ids']) : null,
                        paymentType: (string) ($data['payment_type'] ?? 'salary'),
                        paymentNote: $data['payment_note'] ?? null,
                    );

                    $title = $result['failed'] > 0
                        ? 'Batch payout completed with failures'
                        : 'Batch payout completed';

                    $body = sprintf(
                        'Created: %d | Paid: %d | Failed: %d',
                        (int) $result['created'],
                        (int) $result['paid'],
                        (int) $result['failed'],
                    );

                    if ($result['failed'] > 0 && ! empty($result['errors'])) {
                        $body .= "\n" . collect($result['errors'])->take(3)->implode("\n");
                    }

                    $notification = Notification::make()
                        ->title($title)
                        ->body($body);

                    if ($result['failed'] > 0) {
                        $notification->warning()->send();
                        return;
                    }

                    $notification->success()->send();
                }),
            Actions\Action::make('generate')
                ->label('Generate Payroll')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (PayrollRunGeneratorService $generator): void {
                    $result = $generator->generate($this->record);

                    Notification::make()
                        ->title('Payroll generated successfully')
                        ->body('Employees generated: '.$result['generated'])
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'calculated_at']);
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
