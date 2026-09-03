<?php

namespace Database\Seeders;

use App\Models\SyaratKetentuan;
use Illuminate\Database\Seeder;

class SyaratKetentuanSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'judul' => 'Syarat dan Ketentuan',
                'isi' => "Situs OrangBaik.id merupakan platform berbagi dan galang dana yang dikelola oleh Yayasan Dompet Al-Qur’an Indonesia. Dengan menggunakan layanan ini, pengguna dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang berlaku.\nSyarat dan ketentuan ini mengatur penggunaan layanan, kewajiban pengguna, ketentuan donasi, penggalangan dana, serta kebijakan lain yang berkaitan dengan aktivitas di platform OrangBaik.id.",
            ],
            [
                'judul' => 'Ketentuan Umum',
                'isi' => "Pengguna wajib memberikan informasi yang benar, lengkap, dan dapat dipertanggungjawabkan saat menggunakan layanan OrangBaik.id.\nOrangBaik.id berhak melakukan verifikasi, peninjauan, pembatasan, atau penghapusan campaign apabila ditemukan informasi yang tidak sesuai, menyesatkan, atau melanggar ketentuan yang berlaku.\nSetiap aktivitas donasi dan penggalangan dana harus dilakukan dengan itikad baik, transparan, serta tidak bertentangan dengan hukum yang berlaku di Indonesia.",
            ],
            [
                'judul' => 'Prinsip Program Penggalangan Dana dan Donasi',
                'isi' => "Penggalangan dana dilakukan untuk tujuan sosial, kemanusiaan, pendidikan, dakwah, kesehatan, atau kegiatan lain yang sejalan dengan nilai kebaikan.\nDonatur memahami bahwa dana yang diberikan merupakan bentuk dukungan sukarela terhadap campaign yang dipilih.\nPenggalang dana bertanggung jawab atas kebenaran informasi, penggunaan dana, serta laporan perkembangan program kepada donatur.",
            ],
            [
                'judul' => 'Platform OrangBaik.id',
                'isi' => "OrangBaik.id menyediakan layanan teknologi untuk mempertemukan penggalang dana dan donatur secara online.\nOrangBaik.id dapat melakukan kurasi, moderasi, dan verifikasi terhadap campaign untuk menjaga keamanan dan kepercayaan pengguna.\nPlatform dapat mengalami perubahan fitur, tampilan, maupun kebijakan sesuai kebutuhan pengembangan layanan.",
            ],
            [
                'judul' => 'Ketentuan Donasi',
                'isi' => "Donatur dapat memilih nominal donasi dan metode pembayaran yang tersedia pada platform.\nSetelah transaksi berhasil, donasi akan tercatat dalam sistem dan diproses sesuai campaign yang dipilih.\nDonasi yang telah berhasil diproses tidak dapat dibatalkan, kecuali terdapat kondisi khusus sesuai kebijakan platform dan ketentuan hukum yang berlaku.",
            ],
            [
                'judul' => 'Ketentuan Penggalangan Dana',
                'isi' => "Penggalang dana wajib memberikan informasi campaign yang jelas, benar, dan tidak menyesatkan.\nCampaign yang dibuat harus mencantumkan tujuan, target dana, penerima manfaat, dan rencana penggunaan dana secara transparan.\nOrangBaik.id berhak menolak atau menonaktifkan campaign yang tidak sesuai dengan ketentuan.",
            ],
            [
                'judul' => 'Kewajiban Pengguna',
                'isi' => "Pengguna wajib menjaga kerahasiaan akun dan bertanggung jawab atas seluruh aktivitas yang dilakukan menggunakan akun tersebut.\nPengguna dilarang menggunakan platform untuk kegiatan penipuan, pencucian uang, penyebaran informasi palsu, ujaran kebencian, atau aktivitas yang melanggar hukum.\nPengguna wajib mematuhi seluruh aturan, kebijakan, dan arahan yang diberikan oleh OrangBaik.id.",
            ],
            [
                'judul' => 'Larangan',
                'isi' => "Pengguna dilarang membuat campaign palsu, menggunakan identitas orang lain, atau menyalahgunakan dana donasi.\nPengguna dilarang mengunggah konten yang mengandung unsur SARA, pornografi, kekerasan, perjudian, atau hal lain yang bertentangan dengan norma dan hukum.\nPengguna dilarang melakukan tindakan yang dapat merusak sistem, mengganggu layanan, atau mencuri data pengguna lain.",
            ],
            [
                'judul' => 'Biaya Operasional dan Administrasi',
                'isi' => "OrangBaik.id dapat mengenakan biaya operasional atau administrasi untuk mendukung keberlangsungan layanan.\nBesaran biaya dapat diinformasikan pada halaman campaign atau halaman pembayaran sesuai kebijakan yang berlaku.",
            ],
            [
                'judul' => 'Perubahan Ketentuan',
                'isi' => "OrangBaik.id berhak memperbarui syarat dan ketentuan ini sewaktu-waktu.\nPerubahan akan berlaku setelah dipublikasikan pada platform. Pengguna disarankan untuk membaca halaman ini secara berkala.",
            ],
        ];

        foreach ($sections as $index => $section) {
            SyaratKetentuan::updateOrCreate(
                ['judul' => $section['judul']],
                [
                    'isi' => $section['isi'],
                    'urutan' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
