<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penggalang_dana', function (Blueprint $table): void {
            $table->string('foto_profil')->nullable()->change();
        });

        if (!Schema::hasColumn('penggalang_dana', 'pending_changes')) {
            Schema::table('penggalang_dana', function (Blueprint $table): void {
                $table->json('pending_changes')->nullable()->after('revision_count');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penggalang_dana', 'pending_changes')) {
            Schema::table('penggalang_dana', function (Blueprint $table): void {
                $table->dropColumn('pending_changes');
            });
        }

        // Jangan mengubah kembali ke NOT NULL agar data pengguna tanpa foto tetap aman.
    }
};
