<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filters = [
            ['nama_filter' => 'Bantuan Pendidikan', 'slug' => 'bantuan-pendidikan'],
            ['nama_filter' => 'Bencana Alam', 'slug' => 'bencana-alam'],
            ['nama_filter' => 'Panti Asuhan', 'slug' => 'panti-asuhan'],
            ['nama_filter' => 'Difabel', 'slug' => 'difabel'],
            ['nama_filter' => 'Pemberdayaan UMKM', 'slug' => 'pemberdayaan-umkm'],
            ['nama_filter' => 'Kesehatan', 'slug' => 'kesehatan'],
            ['nama_filter' => 'Lingkungan', 'slug' => 'lingkungan'],
            ['nama_filter' => 'Mualaf', 'slug' => 'mualaf'],
            ['nama_filter' => 'Masjid Berdaya', 'slug' => 'masjid-berdaya'],
            ['nama_filter' => 'Palestine', 'slug' => 'palestine'],
        ];
        foreach ($filters as $filter) {
            \App\Models\Filter::create($filter);
        }
    }
}
