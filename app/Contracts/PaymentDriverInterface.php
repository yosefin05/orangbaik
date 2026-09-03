<?php

namespace App\Contracts;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

interface PaymentDriverInterface
{
    /**
     * Inisialisasi driver dengan model PaymentGateway terkait (opsional).
     */
    public function setGateway(PaymentGateway $gateway): self;

    /**
     * Buat transaksi pembayaran ke gateway.
     * Return array berisi informasi redirect_url / snap_token / virtual_account dsb.
     */
    public function createTransaction(Donasi $donasi, Pembayaran $pembayaran, PaymentChannel $channel): array;

    /**
     * Tangani notifikasi callback/webhook dari payment gateway.
     */
    public function handleWebhook(Request $request): bool;

    /**
     * Cek apakah driver sudah memiliki kredensial aktif.
     */
    public function isConfigured(): bool;
}
