<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['nama_kategori' => 'Zakat', 'slug' => 'zakat'],
            ['nama_kategori' => 'Infaq', 'slug' => 'infaq'],
            ['nama_kategori' => 'Sedekah Rutin', 'slug' => 'sedekah-rutin'],
            ['nama_kategori' => 'Kemanusiaan', 'slug' => 'kemanusiaan'],
            ['nama_kategori' => 'Wakaf', 'slug' => 'wakaf'],
            ['nama_kategori' => 'Lainnya', 'slug' => 'donasi'],
        ];
        foreach ($categories as $kategori) {
            \App\Models\Kategori::create($kategori);
        }
    }
}
