<?php

namespace App\Filament\Resources\PayrollPaymentResource\Pages;

use App\Filament\Resources\PayrollPaymentResource;
use App\Services\Payments\PayrollPaymentService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayrollPayments extends ListRecords
{
    protected static string $resource = PayrollPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('makePayment')
                ->label('Make Payment')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->form(PayrollPaymentResource::paymentActionSchema())
                ->action(function (array $data, PayrollPaymentService $payments): void {
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
        ];
    }
}
