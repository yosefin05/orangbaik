<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggalang_dana', function (Blueprint $table) {
            $table->text('misi')->change()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('penggalang_dana', function (Blueprint $table) {
            $table->string('misi', 255)->change();
        });
    }
};