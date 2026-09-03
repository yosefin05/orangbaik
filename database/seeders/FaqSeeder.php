<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'pertanyaan' => 'Apakah OrangBaik.id memiliki izin legalitas dan diawasi oleh Pemerintah?',
                'jawaban' => 'OrangBaik.id berkomitmen menjaga transparansi, legalitas, serta pengelolaan dana yang amanah dalam setiap program yang ditampilkan.',
            ],
            [
                'pertanyaan' => 'Bagaimana OrangBaik.id memastikan keaslian galang dana?',
                'jawaban' => 'Setiap penggalang dana perlu melalui proses verifikasi data dan pemeriksaan informasi sebelum campaign ditampilkan kepada publik.',
            ],
            [
                'pertanyaan' => 'Apakah ada potongan untuk biaya operasional OrangBaik.id?',
                'jawaban' => 'Biaya operasional digunakan untuk mendukung layanan platform, verifikasi, sistem pembayaran, dan pelaporan program.',
            ],
            [
                'pertanyaan' => 'Bagaimana cara mendapatkan laporan perkembangan program yang saya dukung?',
                'jawaban' => 'Laporan perkembangan program dapat dilihat melalui update campaign atau informasi yang dibagikan oleh penggalang dana.',
            ],
            [
                'pertanyaan' => 'Bagaimana Cara Mendaftar menjadi Penggalang Dana?',
                'jawaban' => 'Kamu dapat mendaftar melalui menu penggalang dana, lalu melengkapi data dan dokumen verifikasi yang dibutuhkan.',
            ],
            [
                'pertanyaan' => 'Apakah donasi saya aman?',
                'jawaban' => 'Donasi diproses melalui sistem yang dirancang untuk menjaga keamanan transaksi dan transparansi penyaluran bantuan.',
            ],
            [
                'pertanyaan' => 'Apakah saya bisa mendapatkan e-kwitansi?',
                'jawaban' => 'Ya, e-kwitansi dapat diakses melalui halaman riwayat donasi setelah transaksi berhasil diproses.',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::updateOrCreate(
                ['pertanyaan' => $faq['pertanyaan']],
                [
                    'jawaban' => $faq['jawaban'],
                    'urutan' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
