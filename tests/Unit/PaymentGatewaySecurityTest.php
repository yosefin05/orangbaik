<?php

namespace Tests\Unit;

use App\Services\PaymentGateways\PublicGatewayEndpoint;
use App\Support\PaymentAmount;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PaymentGatewaySecurityTest extends TestCase
{
    public function test_idr_amount_parser_accepts_integer_amounts_and_zero_decimal_strings(): void
    {
        self::assertSame(50000, PaymentAmount::parseIdr(50000));
        self::assertSame(50000, PaymentAmount::parseIdr('50000.00'));
        self::assertSame(0, PaymentAmount::parseIdr('000.0'));
    }

    public function test_idr_amount_parser_rejects_fractional_and_overflow_amounts(): void
    {
        self::assertNull(PaymentAmount::parseIdr('50000.50'));
        self::assertNull(PaymentAmount::parseIdr('-1'));
        self::assertNull(PaymentAmount::parseIdr('1e5'));
        self::assertNull(PaymentAmount::parseIdr('999999999999999999999999'));
    }

    public function test_generic_gateway_endpoint_requires_public_https_hostname_on_standard_port(): void
    {
        PublicGatewayEndpoint::assertSafeUrl('https://api.payment-provider.example/v1/payments');

        foreach ([
            'http://api.payment-provider.example/pay',
            'https://localhost/pay',
            'https://service.local/pay',
            'https://127.0.0.1/pay',
            'https://user:secret@api.payment-provider.example/pay',
            'https://api.payment-provider.example:8443/pay',
        ] as $unsafeUrl) {
            try {
                PublicGatewayEndpoint::assertSafeUrl($unsafeUrl);
                self::fail("Unsafe endpoint was accepted: {$unsafeUrl}");
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }
}
