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
        Schema::create('penggalang_dana', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_penggalang', ['individu', 'organisasi']);
            $table->string('foto_profil');
            $table->string('nama_penggalang');
            $table->string('alamat');
            $table->text('deskripsi')->nullable();
            $table->string('visi')->nullable();
            $table->string('misi')->nullable();
            $table->string('email')->unique();
            $table->string('no_telepon')->unique();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('youtube')->nullable();
            $table->string('tiktok')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('verified_by')->constrained('users')->onDelete('cascade')->nullable();
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
