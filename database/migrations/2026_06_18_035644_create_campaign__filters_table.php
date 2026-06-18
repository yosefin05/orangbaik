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
        Schema::create('campaign_filter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaign')->onDelete('cascade');
            $table->foreignId('filter_id')->constrained('filter')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_filter');
    }
};
