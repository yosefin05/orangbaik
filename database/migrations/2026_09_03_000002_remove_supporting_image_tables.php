<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('campaign_gambar');
        Schema::dropIfExists('berita_gambar');
    }

    public function down(): void
    {
        Schema::create('berita_gambar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berita_id')->constrained('berita')->onDelete('cascade');
            $table->string('gambar');
            $table->timestamps();
        });

        Schema::create('campaign_gambar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaign')->onDelete('cascade');
            $table->string('gambar');
            $table->timestamps();
        });
    }
};