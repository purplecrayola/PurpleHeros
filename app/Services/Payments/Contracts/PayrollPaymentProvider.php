<?php

namespace App\Services\Payments\Contracts;

use App\Services\Payments\ProviderTransferResult;

interface PayrollPaymentProvider
{
    public function transfer(array $payload): ProviderTransferResult;
}

