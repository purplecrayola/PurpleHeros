<?php

namespace App\Services\Payments\Providers;

use App\Models\CompanySettings;
use App\Services\Payments\Contracts\PayrollPaymentProvider;
use App\Services\Payments\ProviderTransferResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KudaPayrollProvider implements PayrollPaymentProvider
{
    public function __construct(private readonly CompanySettings $settings)
    {
    }

    public function transfer(array $payload): ProviderTransferResult
    {
        if (! $this->settings->kuda_enabled) {
            return new ProviderTransferResult(false, null, 'Kuda is disabled in settings.');
        }

        if ($this->settings->kuda_sandbox_mode) {
            return new ProviderTransferResult(
                true,
                'KUDA-SANDBOX-' . Str::upper(Str::random(12)),
                'Sandbox transfer simulated.',
                ['sandbox' => true, 'provider' => 'kuda', 'payload' => $payload]
            );
        }

        $baseUrl = rtrim((string) $this->settings->kuda_base_url, '/');
        $path = '/' . ltrim((string) ($this->settings->kuda_transfer_path ?: '/v2/disbursements'), '/');

        if ($baseUrl === '' || blank($this->settings->kuda_api_key)) {
            return new ProviderTransferResult(false, null, 'Kuda base URL or API key is missing.');
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

            // Common Kuda/disbursement aliases
            'beneficiaryName' => $recipient['name'] ?? null,
            'beneficiaryAccountNumber' => $recipient['account_number'] ?? null,
            'beneficiaryBankCode' => $recipient['bank_code'] ?? null,
            'beneficiaryBankName' => $recipient['bank_name'] ?? null,
            'accountName' => $recipient['name'] ?? null,
            'accountNumber' => $recipient['account_number'] ?? null,
            'bankCode' => $recipient['bank_code'] ?? null,
            'bankName' => $recipient['bank_name'] ?? null,
            'amountInKobo' => $payload['amount_kobo'] ?? null,

            // Original nested structure for backward compatibility.
            'recipient' => $recipient,
            'meta' => $payload['meta'] ?? [],
        ];

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . (string) $this->settings->kuda_api_key,
                    'X-Client-Secret' => (string) ($this->settings->kuda_secret_key ?: ''),
                    'X-Client-Email' => (string) ($this->settings->kuda_client_email ?: ''),
                ])
                ->post($baseUrl . $path, $requestBody);
        } catch (\Throwable $exception) {
            return new ProviderTransferResult(
                false,
                null,
                'Unable to reach Kuda endpoint. Check API base URL, transfer path, and network access.',
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
            ?: data_get($json, 'data.transactionReference');

        $message = $response->successful()
            ? 'Transfer accepted by Kuda.'
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
                'Kuda request failed (HTTP %d) on path %s. Check Kuda base URL/transfer path in Settings.',
                $status,
                $path
            );
        }

        return sprintf('%s (HTTP %d)', $providerMessage, $status);
    }
}
