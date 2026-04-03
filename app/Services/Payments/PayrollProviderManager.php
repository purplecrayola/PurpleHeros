<?php

namespace App\Services\Payments;

use App\Models\CompanySettings;
use App\Services\Payments\Contracts\PayrollPaymentProvider;
use App\Services\Payments\Providers\KudaPayrollProvider;
use App\Services\Payments\Providers\OpayPayrollProvider;
use InvalidArgumentException;

class PayrollProviderManager
{
    public function driver(string $provider): PayrollPaymentProvider
    {
        $settings = CompanySettings::current();

        return match (strtolower(trim($provider))) {
            'opay' => new OpayPayrollProvider($settings),
            'kuda' => new KudaPayrollProvider($settings),
            default => throw new InvalidArgumentException("Unsupported payout provider: {$provider}"),
        };
    }
}
