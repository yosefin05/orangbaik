<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        Kategori::insert([
            [
                'nama_kategori' => 'Bantuan Pendidikan',
                'slug' => 'bantuan-pendidikan',
                'is_featured' => false,
            ],
            [
                'nama_kategori' => 'Kemanusiaan',
                'slug' => 'kemanusiaan',
                'is_featured' => true,
            ],
            [
                'nama_kategori' => 'Bencana Alam',
                'slug' => 'bencana-alam',
                'is_featured' => false,
            ],
            [
                'nama_kategori' => 'Zakat',
                'slug' => 'zakat',
                'is_featured' => true,
            ],
            [
                'nama_kategori' => 'Panti Asuhan',
                'slug' => 'panti-asuhan',
                'is_featured' => false,
            ],
            [
                'nama_kategori' => 'Difabel',
                'slug' => 'difabel',
                'is_featured' => false,
            ],
            [
                'nama_kategori' => 'Pemberdayaan UMKM',
                'slug' => 'pemberdayaan-umkm',
                'is_featured' => false,
            ],
            [
                'nama_kategori' => 'Lingkungan',
                'slug' => 'lingkungan',
                'is_featured' => false,
            ],
            [
                'nama_kategori' => 'Wakaf',
                'slug' => 'wakaf',
                'is_featured' => true,
            ],
            [
                'nama_kategori' => 'Masjid Berdaya',
                'slug' => 'masjid-berdaya',
                'is_featured' => false,
            ],
            [
                'nama_kategori' => 'Mualaf',
                'slug' => 'mualaf',
                'is_featured' => false,
            ],
            [
                'nama_kategori' => 'Bantuan Kesehatan',
                'slug' => 'bantuan-kesehatan',
                'is_featured' => false,
            ],
            [
                'nama_kategori' => 'Sedekah',
                'slug' => 'sedekah',
                'is_featured' => true,
            ],
        ]);
    }
}