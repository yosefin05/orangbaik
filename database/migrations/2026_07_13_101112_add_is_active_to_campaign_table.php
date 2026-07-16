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
        Schema::table('campaign', function (Blueprint $table) {
            $table->boolean('is_active')->after('deskripsi');
            $table->unsignedBigInteger('minimal_donasi')->after('target_donasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('minimal_donasi');
        });
    }
};
