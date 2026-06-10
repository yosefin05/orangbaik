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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor')->nullable();
            $table->string('foto_profil')->nullable();

            $table->enum('jenis_kelamin', [
                'L',
                'P'
            ])->nullable();

            $table->enum('role', [
                'user',
                'admin'
            ])->default('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nomor');
            $table->dropColumn('foto_profil');
            $table->dropColumn('jenis_kelamin');
            $table->dropColumn('role');
        });
    }
};
