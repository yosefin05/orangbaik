<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penggalang_dana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('jenis_penggalang', [
                'individual',
                'organisasi'
            ]);
            $table->string('nama_tampilan');
            $table->string('foto_profil')->nullable();
            $table->text('biografi')->nullable();
            $table->text('visi_misi')->nullable();
            $table->text('informasi')->nullable();
            $table->string('dokumen_verifikasi')->nullable();
            $table->enum('status_verifikasi', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users');
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggalang_dana');

    }
};
