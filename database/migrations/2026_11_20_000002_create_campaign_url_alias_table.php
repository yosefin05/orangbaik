<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_url_alias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaign')->cascadeOnDelete();
            $table->string('custom_slug', 120)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_url_alias');
    }
};
