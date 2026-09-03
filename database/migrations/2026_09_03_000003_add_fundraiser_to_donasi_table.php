<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->foreignId('fundraiser_id')
                ->nullable()
                ->after('campaign_id')
                ->constrained('campaign_fundraiser')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fundraiser_id');
        });
    }
};