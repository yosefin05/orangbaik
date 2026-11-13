<?php

namespace App\Services;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class PaymentWebhookProcessor
{
    public function process(
        int $paymentId,
        string $gatewayCode,
        string $status,
        array $payload,
        \Closure $validSettlement,
        array $attributes = []
    ): bool {
        if (!in_array($status, ['pending', 'settlement', 'failed', 'expired'], true)) {
            return false;
        }

        return DB::transaction(function () use ($paymentId, $gatewayCode, $status, $payload, $validSettlement, $attributes): bool {
            $payment = Pembayaran::with(['donasi', 'paymentChannel.gateway'])
                ->whereKey($paymentId)
                ->lockForUpdate()
                ->first();

            if (!$payment || $payment->paymentChannel?->gateway?->code !== $gatewayCode) {
                return false;
            }

            $currentStatus = $payment->transaction_status === 'expire'
                ? 'expired'
                : $payment->transaction_status;
            $terminalStatuses = ['settlement', 'failed', 'expired'];

            if (in_array($currentStatus, $terminalStatuses, true)) {
                return $currentStatus === $status;
            }

            if ($currentStatus !== 'pending') {
                return false;
            }

            if ($status === 'settlement' && !$validSettlement($payment)) {
                return false;
            }

            $gatewayResponse = $payment->gateway_response ?? [];
            $gatewayResponse['last_webhook'] = $payload;
            $updates = array_merge($attributes, [
                'transaction_status' => $status,
                'gateway_response'   => $gatewayResponse,
            ]);

            if ($status === 'settlement') {
                $updates['paid_at'] = now();
            }

            $payment->update($updates);

            return true;
        });
    }
}
