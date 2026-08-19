<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();

            // Nama provider (tampil di admin)
            $table->string('name');

            // Kode unik provider (digunakan di kode)
            // Contoh: midtrans, flip, manual
            $table->string('code')->unique();

            // Status aktif/nonaktif
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
