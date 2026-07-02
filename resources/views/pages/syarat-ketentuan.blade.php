<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/syarat-ketentuan.css') }}">
</head>
<body>

@include('components.header')

@php
    $terms = [
        [
            'title' => 'Syarat dan Ketentuan',
            'body' => [
                'Situs OrangBaik.id merupakan platform berbagi dan galang dana yang dikelola oleh Yayasan Dompet Al-Qur’an Indonesia. Dengan menggunakan layanan ini, pengguna dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang berlaku.',
                'Syarat dan ketentuan ini mengatur penggunaan layanan, kewajiban pengguna, ketentuan donasi, penggalangan dana, serta kebijakan lain yang berkaitan dengan aktivitas di platform OrangBaik.id.',
            ],
        ],
        [
            'title' => 'Ketentuan Umum',
            'body' => [
                'Pengguna wajib memberikan informasi yang benar, lengkap, dan dapat dipertanggungjawabkan saat menggunakan layanan OrangBaik.id.',
                'OrangBaik.id berhak melakukan verifikasi, peninjauan, pembatasan, atau penghapusan campaign apabila ditemukan informasi yang tidak sesuai, menyesatkan, atau melanggar ketentuan yang berlaku.',
                'Setiap aktivitas donasi dan penggalangan dana harus dilakukan dengan itikad baik, transparan, serta tidak bertentangan dengan hukum yang berlaku di Indonesia.',
            ],
        ],
        [
            'title' => 'Prinsip Program Penggalangan Dana dan Donasi',
            'body' => [
                'Penggalangan dana dilakukan untuk tujuan sosial, kemanusiaan, pendidikan, dakwah, kesehatan, atau kegiatan lain yang sejalan dengan nilai kebaikan.',
                'Donatur memahami bahwa dana yang diberikan merupakan bentuk dukungan sukarela terhadap campaign yang dipilih.',
                'Penggalang dana bertanggung jawab atas kebenaran informasi, penggunaan dana, serta laporan perkembangan program kepada donatur.',
            ],
        ],
        [
            'title' => 'Platform OrangBaik.id',
            'body' => [
                'OrangBaik.id menyediakan layanan teknologi untuk mempertemukan penggalang dana dan donatur secara online.',
                'OrangBaik.id dapat melakukan kurasi, moderasi, dan verifikasi terhadap campaign untuk menjaga keamanan dan kepercayaan pengguna.',
                'Platform dapat mengalami perubahan fitur, tampilan, maupun kebijakan sesuai kebutuhan pengembangan layanan.',
            ],
        ],
        [
            'title' => 'Ketentuan Donasi',
            'body' => [
                'Donatur dapat memilih nominal donasi dan metode pembayaran yang tersedia pada platform.',
                'Setelah transaksi berhasil, donasi akan tercatat dalam sistem dan diproses sesuai campaign yang dipilih.',
                'Donasi yang telah berhasil diproses tidak dapat dibatalkan, kecuali terdapat kondisi khusus sesuai kebijakan platform dan ketentuan hukum yang berlaku.',
            ],
        ],
        [
            'title' => 'Ketentuan Penggalangan Dana',
            'body' => [
                'Penggalang dana wajib memberikan informasi campaign yang jelas, benar, dan tidak menyesatkan.',
                'Campaign yang dibuat harus mencantumkan tujuan, target dana, penerima manfaat, dan rencana penggunaan dana secara transparan.',
                'OrangBaik.id berhak menolak atau menonaktifkan campaign yang tidak sesuai dengan ketentuan.',
            ],
        ],
        [
            'title' => 'Kewajiban Pengguna',
            'body' => [
                'Pengguna wajib menjaga kerahasiaan akun dan bertanggung jawab atas seluruh aktivitas yang dilakukan menggunakan akun tersebut.',
                'Pengguna dilarang menggunakan platform untuk kegiatan penipuan, pencucian uang, penyebaran informasi palsu, ujaran kebencian, atau aktivitas yang melanggar hukum.',
                'Pengguna wajib mematuhi seluruh aturan, kebijakan, dan arahan yang diberikan oleh OrangBaik.id.',
            ],
        ],
        [
            'title' => 'Larangan',
            'body' => [
                'Pengguna dilarang membuat campaign palsu, menggunakan identitas orang lain, atau menyalahgunakan dana donasi.',
                'Pengguna dilarang mengunggah konten yang mengandung unsur SARA, pornografi, kekerasan, perjudian, atau hal lain yang bertentangan dengan norma dan hukum.',
                'Pengguna dilarang melakukan tindakan yang dapat merusak sistem, mengganggu layanan, atau mencuri data pengguna lain.',
            ],
        ],
        [
            'title' => 'Biaya Operasional dan Administrasi',
            'body' => [
                'OrangBaik.id dapat mengenakan biaya operasional atau administrasi untuk mendukung keberlangsungan layanan.',
                'Besaran biaya dapat diinformasikan pada halaman campaign atau halaman pembayaran sesuai kebijakan yang berlaku.',
            ],
        ],
        [
            'title' => 'Perubahan Ketentuan',
            'body' => [
                'OrangBaik.id berhak memperbarui syarat dan ketentuan ini sewaktu-waktu.',
                'Perubahan akan berlaku setelah dipublikasikan pada platform. Pengguna disarankan untuk membaca halaman ini secara berkala.',
            ],
        ],
    ];

    $faqs = [
        [
            'question' => 'Apakah OrangBaik.id memiliki izin legalitas?',
            'answer' => 'OrangBaik.id dikelola oleh lembaga yang bertanggung jawab dan berkomitmen menjaga keamanan serta transparansi layanan.',
        ],
        [
            'question' => 'Bagaimana OrangBaik.id memastikan keaslian galang dana?',
            'answer' => 'Campaign dapat melalui proses peninjauan, verifikasi data, serta pemantauan informasi agar tetap sesuai dengan ketentuan platform.',
        ],
        [
            'question' => 'Apakah ada biaya operasional?',
            'answer' => 'Biaya operasional dapat diterapkan sesuai kebijakan platform dan akan diinformasikan pada halaman terkait apabila berlaku.',
        ],
        [
            'question' => 'Bagaimana cara mendapatkan laporan perkembangan program?',
            'answer' => 'Donatur dapat melihat update campaign atau informasi perkembangan yang dibagikan oleh penggalang dana melalui platform.',
        ],
        [
            'question' => 'Bagaimana cara mendaftar menjadi penggalang dana?',
            'answer' => 'Pengguna dapat mendaftar sebagai penggalang dana melalui fitur pendaftaran yang tersedia, kemudian mengikuti proses verifikasi.',
        ],
    ];
@endphp

<main class="terms-page">

    {{-- HERO --}}
    <section class="terms-hero">
        <div class="container">

            <div class="terms-hero-card">
                <div class="terms-hero-content">
                    <span class="terms-eyebrow">
                        Syarat & Ketentuan
                    </span>

                    <h1>
                        Pahami Syarat dan Ketentuan Kami
                    </h1>

                    <p>
                        Pelajari ketentuan layanan yang berlaku agar Anda dapat menggunakan
                        platform OrangBaik.id dengan aman, nyaman, dan sesuai aturan.
                    </p>
                </div>
            </div>

        </div>
    </section>

    {{-- CONTENT --}}
    <section class="terms-content-section">
        <div class="container terms-layout">

            <aside class="terms-sidebar">
                <h3>Daftar Isi</h3>

                <nav>
                    @foreach ($terms as $index => $term)
                        <a href="#term-{{ $index + 1 }}">
                            {{ $term['title'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <article class="terms-document">
                @foreach ($terms as $index => $term)
                    <section class="terms-block" id="term-{{ $index + 1 }}">
                        <h2>{{ $term['title'] }}</h2>

                        @foreach ($term['body'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </section>
                @endforeach
            </article>

        </div>
    </section>

    {{-- FAQ --}}
    <section class="terms-faq">
        <div class="container">

            <div class="terms-faq-header">
                <h2>Pertanyaan yang Sering Diajukan</h2>

                <p>
                    Beberapa informasi umum terkait penggunaan layanan OrangBaik.id.
                </p>
            </div>

            <div class="faq-list">
                @foreach ($faqs as $faq)
                    <details class="faq-item">
                        <summary>
                            <span>{{ $faq['question'] }}</span>
                            <i class="bi bi-plus-lg"></i>
                        </summary>

                        <p>
                            {{ $faq['answer'] }}
                        </p>
                    </details>
                @endforeach
            </div>

        </div>
    </section>

</main>

@include('components.footer')

<script src="{{ asset('js/header.js') }}"></script>

</body>
</html>