<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel donasi
            $table->foreignId('donasi_id')
                ->constrained('donasi')
                ->cascadeOnDelete();

            // Data dari Midtrans
            $table->string('order_id')->unique();
            $table->text('snap_token')->nullable();

            $table->string('payment_type')->nullable();
            $table->string('transaction_id')->nullable();

            // Status pembayaran dari Midtrans
            $table->string('transaction_status')
                ->default('pending');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};