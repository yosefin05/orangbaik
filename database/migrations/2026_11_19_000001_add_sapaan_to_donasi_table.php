<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->string('sapaan', 10)->default('Kak')->after('nama_donatur');
        });
    }

    public function down(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->dropColumn('sapaan');
        });
    }
};
