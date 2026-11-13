<?php

namespace App\Services\PaymentGateways;

use InvalidArgumentException;

final class PublicGatewayEndpoint
{
    public static function assertSafeUrl(string $url): void
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            throw new InvalidArgumentException('Endpoint gateway harus berupa URL HTTPS publik yang valid.');
        }

        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));

        if (
            strtolower((string) ($parts['scheme'] ?? '')) !== 'https' ||
            $host === '' ||
            strpos($host, '.') === false ||
            isset($parts['user']) ||
            isset($parts['pass']) ||
            isset($parts['fragment']) ||
            isset($parts['port']) && $parts['port'] !== 443 ||
            filter_var($host, FILTER_VALIDATE_IP) ||
            !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) ||
            $host === 'localhost' ||
            str_ends_with($host, '.localhost') ||
            str_ends_with($host, '.local') ||
            str_ends_with($host, '.internal')
        ) {
            throw new InvalidArgumentException('Endpoint gateway harus berupa URL HTTPS publik yang valid.');
        }
    }

    /**
     * Resolve and pin a public IPv4 address to prevent DNS rebinding between validation and connect.
     */
    public static function curlResolveOption(string $url): array
    {
        self::assertSafeUrl($url);
        $parts = parse_url($url);
        $host = strtolower(rtrim((string) $parts['host'], '.'));
        $records = @dns_get_record($host, DNS_A);
        $addresses = [];

        foreach ($records ?: [] as $record) {
            $address = $record['ip'] ?? null;
            if (!$address || !filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                continue;
            }

            if (!filter_var(
                $address,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            )) {
                throw new InvalidArgumentException('Endpoint gateway mengarah ke alamat IP non-publik.');
            }

            $addresses[] = $address;
        }

        if (!$addresses) {
            throw new InvalidArgumentException('Endpoint gateway tidak memiliki alamat IPv4 publik.');
        }

        $port = $parts['port'] ?? 443;

        return [
            CURLOPT_RESOLVE => ["{$host}:{$port}:" . $addresses[0]],
        ];
    }

    public static function isSafeUrl(string $url): bool
    {
        try {
            self::assertSafeUrl($url);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
