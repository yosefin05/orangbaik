<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_channels', function (Blueprint $table) {
            $table->id();

            // Relasi ke payment_gateways
            $table->foreignId('payment_gateway_id')
                ->constrained('payment_gateways')
                ->cascadeOnDelete();

            // Nama metode pembayaran (tampil ke donatur)
            // Contoh: QRIS, GoPay, Bank BCA
            $table->string('name');

            // Kode channel di provider
            // Contoh: qris, gopay, bca, bni
            $table->string('channel_code');

            // Nama rekening / akun (opsional, untuk VA dan manual)
            $table->string('account_name')->nullable();

            // Nomor rekening (opsional, untuk transfer manual)
            $table->string('account_number')->nullable();

            // Tipe pembayaran:
            // instant  = langsung (QRIS, e-wallet via Midtrans)
            // va       = virtual account (via Flip)
            // transfer = transfer manual
            $table->enum('payment_type', ['instant', 'va', 'transfer'])->default('instant');

            // Urutan tampilan ke donatur (semakin kecil semakin atas)
            $table->integer('sort_order')->default(0);

            // Status aktif/nonaktif
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_channels');
    }
};
