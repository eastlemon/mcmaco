<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Thin HTTP client for YooKassa API with idempotence key support.
 */
class YooKassaHttpClient
{
    public function __construct(
        private string $shopId,
        private string $secretKey,
        private string $apiBase = 'https://api.yookassa.ru/v3',
    ) {}

    public function post(string $path, array $payload, ?string $idempotenceKey = null): \Illuminate\Http\Client\Response
    {
        $headers = $this->baseHeaders();
        if ($idempotenceKey) {
            $headers['Idempotence-Key'] = $idempotenceKey;
        }

        return Http::withHeaders($headers)
            ->withBasicAuth($this->shopId, $this->secretKey)
            ->post($this->apiBase . $path, $payload);
    }

    public function get(string $path): \Illuminate\Http\Client\Response
    {
        return Http::withHeaders($this->baseHeaders())
            ->withBasicAuth($this->shopId, $this->secretKey)
            ->get($this->apiBase . $path);
    }

    private function baseHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }
}
