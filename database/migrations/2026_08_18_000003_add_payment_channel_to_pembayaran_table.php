<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Relasi ke payment channel yang digunakan donatur
            // Nullable agar data transaksi lama tetap valid
            $table->foreignId('payment_channel_id')
                ->nullable()
                ->after('donasi_id')
                ->constrained('payment_channels')
                ->nullOnDelete();

            // Menyimpan raw response dari gateway (JSON)
            // Berguna untuk debug dan audit
            $table->json('gateway_response')->nullable()->after('transaction_status');

            // Path file bukti transfer (untuk transfer manual)
            $table->string('bukti_transfer')->nullable()->after('gateway_response');

            // Alasan reject transfer manual
            $table->text('rejection_reason')->nullable()->after('bukti_transfer');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['payment_channel_id']);
            $table->dropColumn([
                'payment_channel_id',
                'gateway_response',
                'bukti_transfer',
                'rejection_reason',
            ]);
        });
    }
};
