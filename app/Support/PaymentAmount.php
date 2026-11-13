<?php

namespace App\Support;

final class PaymentAmount
{
    public static function parseIdr(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (is_float($value)) {
            return is_finite($value) && $value >= 0 && floor($value) === $value && $value < PHP_INT_MAX
                ? (int) $value
                : null;
        }

        if (!is_string($value) || !preg_match('/^\d+(?:\.0+)?$/', trim($value))) {
            return null;
        }

        $wholeAmount = ltrim(explode('.', trim($value), 2)[0], '0');
        $wholeAmount = $wholeAmount === '' ? '0' : $wholeAmount;
        $maxAmount = (string) PHP_INT_MAX;

        if (
            strlen($wholeAmount) > strlen($maxAmount) ||
            strlen($wholeAmount) === strlen($maxAmount) && strcmp($wholeAmount, $maxAmount) > 0
        ) {
            return null;
        }

        return (int) $wholeAmount;
    }
}
