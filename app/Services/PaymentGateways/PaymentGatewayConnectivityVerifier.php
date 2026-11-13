<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class PaymentGatewayConnectivityVerifier
{
    public function verify(PaymentGateway $gateway): array
    {
        if ($gateway->code === 'manual') {
            return $this->failure('Transfer manual tidak memerlukan konektivitas provider.');
        }

        if (!PaymentGateway::encryptionKeyAvailable()) {
            return $this->failure('APP_KEY tidak tersedia atau tidak valid; kredensial tidak dapat dibaca dengan aman.');
        }

        if (!$gateway->isConfigured()) {
            return $this->failure('Konfigurasi wajib belum lengkap atau kredensial tidak dapat dibaca.');
        }

        return match ($gateway->code) {
            'midtrans' => $this->verifyMidtrans($gateway),
            'flip' => $this->verifyFlip($gateway),
            default => $this->verifyGeneric($gateway),
        };
    }

    protected function verifyMidtrans(PaymentGateway $gateway): array
    {
        try {
            $production = (bool) $gateway->getConfigValue('is_production', false);
            $baseUrl = $production
                ? 'https://api.midtrans.com'
                : 'https://api.sandbox.midtrans.com';
            $probeOrderId = 'orangbaik-connectivity-' . bin2hex(random_bytes(12));
            $response = Http::acceptJson()
                ->withBasicAuth((string) $gateway->getConfigValue('server_key'), '')
                ->connectTimeout(5)
                ->timeout(12)
                ->withOptions(['allow_redirects' => false, 'proxy' => ''])
                ->get("{$baseUrl}/v2/{$probeOrderId}/status");

            if ($response->successful() && $this->midtransStatusResponseIsValid($response, $probeOrderId)) {
                return $this->success('Koneksi dan autentikasi Midtrans berhasil diverifikasi.');
            }

            if ($response->status() === 404 && $this->midtransReportsMissingTransaction($response)) {
                return $this->success('Midtrans menerima kredensial; ID probe acak tidak memiliki transaksi.');
            }

            return $this->failure($response->status() === 401 || $response->status() === 403
                ? 'Midtrans menolak kredensial server key.'
                : 'Respons status Midtrans tidak sesuai; periksa kredensial dan mode sandbox/production.');
        } catch (Throwable) {
            return $this->failure('Endpoint Midtrans tidak dapat dijangkau atau responsnya tidak dapat diverifikasi.');
        }
    }

    protected function verifyFlip(PaymentGateway $gateway): array
    {
        try {
            $production = (bool) $gateway->getConfigValue('is_production', false);
            $baseUrl = $production
                ? (config('payment.flip.base_url_production') ?? 'https://bigflip.id/api')
                : (config('payment.flip.base_url_sandbox') ?? 'https://bigflip.id/big_sandbox_api');
            $response = Http::acceptJson()
                ->withBasicAuth((string) $gateway->getConfigValue('api_key'), '')
                ->connectTimeout(5)
                ->timeout(12)
                ->withOptions(['allow_redirects' => false, 'proxy' => ''])
                ->get(rtrim($baseUrl, '/') . '/v2/general/banks');

            if (!$response->successful() || !$this->flipBankListResponseIsValid($response)) {
                return $this->failure($response->status() === 401 || $response->status() === 403
                    ? 'Flip menolak API key.'
                    : 'Respons daftar bank Flip tidak sesuai; periksa API key dan mode.');
            }

            return $this->success('Koneksi dan autentikasi Flip berhasil diverifikasi melalui API daftar bank.');
        } catch (Throwable) {
            return $this->failure('Endpoint Flip tidak dapat dijangkau atau responsnya tidak dapat diverifikasi.');
        }
    }

    protected function verifyGeneric(PaymentGateway $gateway): array
    {
        $endpoint = (string) $gateway->getConfigValue('endpoint_url');
        $verificationUrl = (string) $gateway->getConfigValue('verification_url');

        try {
            PublicGatewayEndpoint::assertSafeUrl($verificationUrl);
            if (!$this->sameOrigin($endpoint, $verificationUrl)) {
                return $this->failure('URL health-check harus memakai origin HTTPS yang sama dengan endpoint transaksi.');
            }

            $resolveOptions = $this->resolveGenericEndpoint($verificationUrl);
            $response = Http::acceptJson()
                ->withToken((string) ($gateway->getConfigValue('api_key') ?: $gateway->getConfigValue('server_key')))
                ->connectTimeout(5)
                ->timeout(12)
                ->withOptions([
                    'allow_redirects' => false,
                    'proxy' => '',
                    'curl' => $resolveOptions,
                ])
                ->get($verificationUrl);

            $body = $response->json();
            if (
                $response->successful() &&
                is_array($body) &&
                ($body['status'] ?? null) === 'ok' &&
                ($body['authenticated'] ?? null) === true
            ) {
                return $this->success('Endpoint health-check mengonfirmasi koneksi dan autentikasi API.');
            }

            return $this->failure($response->status() === 401 || $response->status() === 403
                ? 'Endpoint health-check menolak API key.'
                : 'Health-check harus mengembalikan JSON status "ok" dan authenticated=true.');
        } catch (Throwable) {
            return $this->failure('Health-check Generic API gagal dijangkau atau tidak memenuhi kebijakan keamanan endpoint.');
        }
    }

    private function midtransStatusResponseIsValid(Response $response, string $probeOrderId): bool
    {
        $body = $response->json();

        return is_array($body)
            && ($body['status_code'] ?? null) === '200'
            && ($body['order_id'] ?? null) === $probeOrderId
            && is_string($body['transaction_status'] ?? null);
    }

    private function midtransReportsMissingTransaction(Response $response): bool
    {
        $body = $response->json();
        $message = is_array($body) ? strtolower((string) ($body['status_message'] ?? '')) : '';

        return is_array($body)
            && (string) ($body['status_code'] ?? '') === '404'
            && str_contains($message, 'transaction')
            && (str_contains($message, "doesn't exist") || str_contains($message, 'not found'));
    }

    private function flipBankListResponseIsValid(Response $response): bool
    {
        $body = $response->json();
        if (!is_array($body)) {
            return false;
        }

        $items = array_is_list($body)
            ? $body
            : ($body['data'] ?? $body['banks'] ?? null);

        if (!is_array($items) || $items === []) {
            return false;
        }

        $firstBank = reset($items);

        return is_array($firstBank)
            && is_string($firstBank['bank_code'] ?? null)
            && $firstBank['bank_code'] !== '';
    }

    private function sameOrigin(string $endpoint, string $verificationUrl): bool
    {
        $endpointParts = parse_url($endpoint);
        $verificationParts = parse_url($verificationUrl);

        return is_array($endpointParts)
            && is_array($verificationParts)
            && strtolower((string) ($endpointParts['scheme'] ?? '')) === 'https'
            && strtolower((string) ($verificationParts['scheme'] ?? '')) === 'https'
            && strtolower((string) ($endpointParts['host'] ?? '')) === strtolower((string) ($verificationParts['host'] ?? ''))
            && ($endpointParts['port'] ?? 443) === ($verificationParts['port'] ?? 443);
    }

    protected function resolveGenericEndpoint(string $url): array
    {
        return PublicGatewayEndpoint::curlResolveOption($url);
    }

    private function success(string $message): array
    {
        return ['success' => true, 'message' => $message];
    }

    private function failure(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
