<?php

namespace App\Services\Payments\Providers;

use App\Models\CompanySettings;
use App\Services\Payments\Contracts\PayrollPaymentProvider;
use App\Services\Payments\ProviderTransferResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpayPayrollProvider implements PayrollPaymentProvider
{
    public function __construct(private readonly CompanySettings $settings)
    {
    }

    public function transfer(array $payload): ProviderTransferResult
    {
        if (! $this->settings->opay_enabled) {
            return new ProviderTransferResult(false, null, 'Opay is disabled in settings.');
        }

        if ($this->settings->opay_sandbox_mode) {
            return new ProviderTransferResult(
                true,
                'OPAY-SANDBOX-' . Str::upper(Str::random(12)),
                'Sandbox transfer simulated.',
                ['sandbox' => true, 'provider' => 'opay', 'payload' => $payload]
            );
        }

        $baseUrl = rtrim((string) $this->settings->opay_base_url, '/');
        $path = '/' . ltrim((string) ($this->settings->opay_transfer_path ?: '/api/v1/transfers'), '/');

        if ($baseUrl === '' || blank($this->settings->opay_secret_key)) {
            return new ProviderTransferResult(false, null, 'Opay base URL or secret key is missing.');
        }

        $recipient = (array) ($payload['recipient'] ?? []);
        $requestBody = [
            // Canonical fields
            'reference' => $payload['reference'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'currency' => $payload['currency'] ?? 'NGN',
            'narration' => $payload['narration'] ?? null,
            'description' => $payload['description'] ?? $payload['narration'] ?? null,
            'remark' => $payload['remark'] ?? $payload['narration'] ?? null,

            // Common transfer aliases used by provider gateways
            'beneficiaryName' => $recipient['name'] ?? null,
            'beneficiaryAccountNumber' => $recipient['account_number'] ?? null,
            'beneficiaryBankName' => $recipient['bank_name'] ?? null,
            'beneficiaryBankCode' => $recipient['bank_code'] ?? null,
            'accountName' => $recipient['name'] ?? null,
            'accountNumber' => $recipient['account_number'] ?? null,
            'bankName' => $recipient['bank_name'] ?? null,
            'bankCode' => $recipient['bank_code'] ?? null,
            'amountKobo' => $payload['amount_kobo'] ?? null,

            // Original nested structure for backward compatibility.
            'recipient' => $recipient,
            'meta' => $payload['meta'] ?? [],
        ];

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . (string) $this->settings->opay_secret_key,
                    'MerchantId' => (string) ($this->settings->opay_merchant_id ?: ''),
                    'X-Public-Key' => (string) ($this->settings->opay_public_key ?: ''),
                ])
                ->post($baseUrl . $path, $requestBody);
        } catch (\Throwable $exception) {
            return new ProviderTransferResult(
                false,
                null,
                'Unable to reach Opay endpoint. Check API base URL, transfer path, and network access.',
                [
                    'error' => $exception->getMessage(),
                    'path' => $path,
                    'request' => $requestBody,
                ]
            );
        }

        $json = $response->json();
        $ref = data_get($json, 'data.reference')
            ?: data_get($json, 'reference')
            ?: data_get($json, 'data.transactionId');

        $message = $response->successful()
            ? 'Transfer accepted by Opay.'
            : $this->buildFailureMessage($response->status(), $path, is_array($json) ? $json : []);

        return new ProviderTransferResult(
            $response->successful(),
            $ref ? (string) $ref : null,
            $message,
            is_array($json)
                ? array_merge($json, ['request' => $requestBody, 'status' => $response->status(), 'path' => $path])
                : ['body' => (string) $response->body(), 'request' => $requestBody, 'status' => $response->status(), 'path' => $path]
        );
    }

    /**
     * @param  array<string,mixed>  $json
     */
    private function buildFailureMessage(int $status, string $path, array $json): string
    {
        $providerMessage = trim((string) (
            $json['message']
            ?? $json['error_description']
            ?? $json['error']
            ?? $json['detail']
            ?? ''
        ));

        if ($providerMessage === '' || strcasecmp($providerMessage, 'No message available') === 0) {
            return sprintf(
                'Opay request failed (HTTP %d) on path %s. Check OPay base URL/transfer path in Settings.',
                $status,
                $path
            );
        }

        return sprintf('%s (HTTP %d)', $providerMessage, $status);
    }
}
