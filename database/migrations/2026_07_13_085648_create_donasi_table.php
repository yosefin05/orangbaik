<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('donasi', function (Blueprint $table) {

            $table->id();
            // Relasi ke campaign
            $table->foreignId('campaign_id')
                ->constrained('campaign')
                ->cascadeOnDelete();
            // Nullable jika donatur tidak login
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            // Data Donatur
            $table->string('nama_donatur');
            $table->string('email')->nullable();
            $table->string('no_hp', 20)->nullable();
            // Informasi Donasi
            $table->unsignedBigInteger('nominal');
            $table->text('pesan_doa')
                ->nullable();
            $table->boolean('is_anonim')
                ->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donasi');
    }
};