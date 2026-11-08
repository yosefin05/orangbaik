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
        Schema::table('penggalang_dana_dokumen', function (Blueprint $table) {
            if (!Schema::hasColumn('penggalang_dana_dokumen', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('file_dokumen')->index();
            }
            if (!Schema::hasColumn('penggalang_dana_dokumen', 'storage_disk')) {
                $table->string('storage_disk', 50)->default('private')->after('is_public');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggalang_dana_dokumen', function (Blueprint $table) {
            if (Schema::hasColumn('penggalang_dana_dokumen', 'storage_disk')) {
                $table->dropColumn('storage_disk');
            }
            if (Schema::hasColumn('penggalang_dana_dokumen', 'is_public')) {
                $table->dropColumn('is_public');
            }
        });
    }
};
