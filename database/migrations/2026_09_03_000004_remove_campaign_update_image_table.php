<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('campaign_update_gambar');
    }

    public function down(): void
    {
        Schema::create('campaign_update_gambar', function (Blueprint $table) {
            $table->id();
            $table->string('gambar_update');
            $table->foreignId('campaign_update_id')
                ->constrained('campaign_update')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
