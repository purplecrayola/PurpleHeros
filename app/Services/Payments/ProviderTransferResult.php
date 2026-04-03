<?php

namespace App\Services\Payments;

class ProviderTransferResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $reference = null,
        public readonly ?string $message = null,
        public readonly array $raw = [],
    ) {
    }
}

