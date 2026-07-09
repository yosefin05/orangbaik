<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggalang_dana', function (Blueprint $table) {

            $table->text('catatan_verifikasi')
                  ->nullable()
                  ->after('status');

            $table->boolean('status_read')
                  ->default(true)
                  ->after('catatan_verifikasi');

        });
    }

    public function down(): void
    {
        Schema::table('penggalang_dana', function (Blueprint $table) {

            $table->dropColumn([
                'catatan_verifikasi',
                'status_read'
            ]);

        });
    }
};