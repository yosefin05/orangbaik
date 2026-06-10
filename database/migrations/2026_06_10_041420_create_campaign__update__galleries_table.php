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
        Schema::create('campaign_update_gallery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_update_id')
                ->constrained('campaign_update')
                ->cascadeOnDelete();
            $table->string('foto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_update_gallery');
    }
};
    