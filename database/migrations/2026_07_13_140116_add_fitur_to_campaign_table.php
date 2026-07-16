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
        Schema::table('campaign', function (Blueprint $table) {
            // Fitur campaign
            $table->boolean('enable_quantity')->default(true);
            $table->boolean('enable_nama_donatur')->default(true);
            $table->boolean('enable_custom_nominal')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->dropColumn('enable_quantity');
            $table->dropColumn('enable_nama_donatur');
            $table->dropColumn('enable_custom_nominal');
        });
    }
};
