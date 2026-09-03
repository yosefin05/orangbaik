<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->date('tanggal_berakhir')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('campaign', function (Blueprint $table) {
            $table->date('tanggal_berakhir')->nullable(false)->change();
        });
    }
};
