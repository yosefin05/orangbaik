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
        Schema::create('penggalang_dana_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dokumen');
            $table->string('file_dokumen');
            $table->foreignId('penggalang_dana_id')->constrained('penggalang_dana')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggalang_dana_dokumen');
    }
};
