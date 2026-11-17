<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('penggalang_dana', function (Blueprint $table) {
            // Ubah jenis_penggalang menjadi string agar fleksibel menampung 4 kategori:
            // organisasi, yayasan_lembaga, komunitas, individu
            $table->string('jenis_penggalang', 50)->default('individu')->change();

            if (!Schema::hasColumn('penggalang_dana', 'slug')) {
                $table->string('slug', 255)->nullable()->unique()->after('nama_penggalang');
            }
        });

        // Generate slug awal untuk data yang belum memiliki slug
        $fundraisers = DB::table('penggalang_dana')->whereNull('slug')->get();
        foreach ($fundraisers as $f) {
            $baseSlug = \Illuminate\Support\Str::slug($f->nama_penggalang) ?: 'penggalang-' . $f->id;
            $slug = $baseSlug;
            $count = 1;
            while (DB::table('penggalang_dana')->where('slug', $slug)->where('id', '!=', $f->id)->exists()) {
                $slug = $baseSlug . '-' . (++$count);
            }
            DB::table('penggalang_dana')->where('id', $f->id)->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggalang_dana', function (Blueprint $table) {
            if (Schema::hasColumn('penggalang_dana', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
