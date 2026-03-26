<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaDarajaService extends IntegrationService
{
    protected string $integrationName = 'mpesa_daraja';

    private string $baseUrl;
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;
    private string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl        = config('daraja.base_url');
        $this->consumerKey    = config('daraja.consumer_key');
        $this->consumerSecret = config('daraja.consumer_secret');
        $this->shortcode      = config('daraja.shortcode');
        $this->passkey        = config('daraja.passkey');
        $this->callbackUrl    = config('daraja.callback_url');
    }

    // -------------------------------------------------------------------------
    // AUTH TOKEN
    // -------------------------------------------------------------------------

    private function getAccessToken(): string
    {
        return Cache::remember('mpesa_access_token', config('daraja.token_cache_ttl'), function () {
            $start    = microtime(true);
            $endpoint = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';

            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($endpoint);

            $duration = (int) ((microtime(true) - $start) * 1000);

            $this->log(
                direction: 'OUTBOUND',
                status: $response->successful() ? 'success' : 'failed',
                error: $response->successful() ? null : $response->body(),
                durationMs: $duration,
                endpoint: $endpoint,
                response: $response->json(),
            );

            if (! $response->successful() || empty($response->json('access_token'))) {
                throw new \RuntimeException('M-Pesa auth failed: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    // -------------------------------------------------------------------------
    // STK PUSH
    // -------------------------------------------------------------------------

    public function initiateSTKPush(string $phone, float $amount, string $reference): array
    {
        return $this->callWithRetry(function () use ($phone, $amount, $reference) {
            $token     = $this->getAccessToken();
            $timestamp = now()->format('YmdHis');
            $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);
            $endpoint  = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'TransactionType'   => 'CustomerPayBillOnline',
                'Amount'            => (int) ceil($amount),
                'PartyA'            => $this->normalisePhone($phone),
                'PartyB'            => $this->shortcode,
                'PhoneNumber'       => $this->normalisePhone($phone),
                'CallBackURL'       => $this->callbackUrl,
                'AccountReference'  => substr($reference, 0, 12),
                'TransactionDesc'   => 'Dawa Mtaani payment',
            ];

            $start    = microtime(true);
            $response = Http::withToken($token)->post($endpoint, $payload);
            $duration = (int) ((microtime(true) - $start) * 1000);

            $this->log(
                direction: 'OUTBOUND',
                status: $response->successful() ? 'success' : 'failed',
                error: $response->successful() ? null : $response->body(),
                durationMs: $duration,
                endpoint: $endpoint,
                request: $this->redactPayload($payload),
                response: $response->json(),
            );

            if (! $response->successful()) {
                throw new \RuntimeException('STK push failed: ' . $response->body());
            }

            return $response->json();
        });
    }

    // -------------------------------------------------------------------------
    // TRANSACTION STATUS QUERY
    // -------------------------------------------------------------------------

    public function queryTransactionStatus(string $checkoutRequestId): array
    {
        return $this->callWithRetry(function () use ($checkoutRequestId) {
            $token     = $this->getAccessToken();
            $timestamp = now()->format('YmdHis');
            $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);
            $endpoint  = $this->baseUrl . '/mpesa/stkpushquery/v1/query';

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ];

            $start    = microtime(true);
            $response = Http::withToken($token)->post($endpoint, $payload);
            $duration = (int) ((microtime(true) - $start) * 1000);

            $this->log(
                direction: 'OUTBOUND',
                status: $response->successful() ? 'success' : 'failed',
                error: $response->successful() ? null : $response->body(),
                durationMs: $duration,
                endpoint: $endpoint,
                request: $payload,
                response: $response->json(),
            );

            if (! $response->successful()) {
                throw new \RuntimeException('STK status query failed: ' . $response->body());
            }

            return $response->json();
        });
    }

    // -------------------------------------------------------------------------
    // CALLBACK VERIFICATION
    // -------------------------------------------------------------------------

    public function verifyCallback(array $payload): bool
    {
        // Safaricom does not sign sandbox callbacks with a secret.
        // IP whitelisting (MpesaIpWhitelist middleware) is the primary
        // control. In production, the middleware enforces the IP list
        // from config('daraja.callback_ips').
        // This method confirms the payload has the required structure.

        return isset(
            $payload['Body']['stkCallback']['MerchantRequestID'],
            $payload['Body']['stkCallback']['CheckoutRequestID'],
            $payload['Body']['stkCallback']['ResultCode']
        );
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function normalisePhone(string $phone): string
    {
        // Accepts 07xxxxxxxx or +2547xxxxxxxx → returns 2547xxxxxxxx
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }

        if (str_starts_with($phone, '+')) {
            $phone = ltrim($phone, '+');
        }

        return $phone;
    }

    private function redactPayload(array $payload): array
    {
        // Never log the password or passkey
        return array_merge($payload, [
            'Password' => '[REDACTED]',
        ]);
    }
}
