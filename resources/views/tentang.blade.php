<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tentang.css') }}">
</head>
<body>

@php
    $legalities = [
        ['image' => 'assets/legal-1.png', 'name' => 'Legalitas 1'],
        ['image' => 'assets/legal-2.png', 'name' => 'Legalitas 2'],
        ['image' => 'assets/legal-3.png', 'name' => 'Legalitas 3'],
        ['image' => 'assets/legal-4.png', 'name' => 'Legalitas 4'],
        ['image' => 'assets/legal-5.png', 'name' => 'Legalitas 5'],
    ];

    $years = ['2025', '2024', '2023', '2022', '2021', '2020', '2019', '2018', '2017', '2016'];

    $faqs = [
        'Apakah orangbaik.id memiliki izin legalitas dan diawasi oleh Pemerintah?',
        'Bagaimana orangbaik.id memastikan keaslian galang dana?',
        'Apakah ada potongan untuk biaya operasional orangbaik.id?',
        'Bagaimana cara mendapatkan laporan perkembangan program yang saya dukung?',
        'Bagaimana Cara Mendaftar menjadi Penggalang Dana?',
        'Apakah orangbaik.id memiliki izin legalitas dan diawasi oleh Pemerintah?',
        'Bagaimana orangbaik.id memastikan keaslian galang dana?',
    ];
@endphp

<main class="about-page">

    <div class="about-container">

        {{-- BACK --}}
        <button class="about-back" type="button" onclick="history.back()">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 18L9 12L15 6" />
            </svg>
            <span>Kembali</span>
        </button>

        {{-- INTRO --}}
        <section class="about-intro">
            <h1>Siapa <span>orangbaik.id?</span></h1>

            <p>
                Situs orangbaik.id merupakan platform donasi dan galang dana secara online
                yang dikelola oleh Yayasan Dompet Al-Qur'an Indonesia.
            </p>

            <p>
                Dompet Al-Qur'an Indonesia adalah Lembaga Amil Zakat yang mengelola dana
                Zakat, Infaq, Sedekah, dan Wakaf (ZISWAF) untuk kesejahteraan masyarakat
                dengan program-program pendidikan, perekonomian, dakwah, dan kemanusiaan.
            </p>
        </section>

    </div>

    {{-- VISI MISI --}}
    <section class="vision-section">
        <div class="about-container vision-wrapper">

            <div class="vision-content">

                <span class="section-pill">Visi & Misi Lembaga</span>

                <div class="vision-item">
                    <div class="vision-icon">☼</div>

                    <div>
                        <h2>Visi Lembaga</h2>
                        <p>
                            Menjadi Lembaga Profesional dalam Pemberdayaan dan Pelayanan
                            serta membangun masyarakat yang akrab dengan Al-Qur'an.
                        </p>
                    </div>
                </div>

                <div class="vision-item">
                    <div class="vision-icon">▣</div>

                    <div>
                        <h2>Misi Lembaga</h2>

                        <ol>
                            <li>Aktif dalam membangun jaringan filantropi yang profesional.</li>
                            <li>Meningkatkan kemandirian dan mengakrabkan masyarakat Indonesia dengan Al-Qur'an.</li>
                            <li>Meningkatkan sumber daya melalui keunggulan lembaga.</li>
                        </ol>
                    </div>
                </div>

                <p class="vision-note">
                    Menjadi landasan dalam menciptakan masa depan yang lebih baik melalui
                    inovasi, integritas, dan pelayanan.
                </p>

            </div>

            <div class="vision-image-wrap">
                <img src="{{ asset('assets/about-person.png') }}" alt="Relawan OrangBaik.id">
            </div>

        </div>
    </section>

    {{-- LEGALITAS --}}
    <section class="legal-section">
        <div class="about-container">

            <span class="section-pill">Legalitas</span>

            <p class="legal-desc">
                Lembaga kami beroperasi secara resmi dan profesional dengan legalitas yang sah
                sesuai peraturan yang berlaku, sebagai bentuk komitmen dalam membangun
                kepercayaan, transparansi, dan pelayanan yang bertanggung jawab.
            </p>

            <div class="legal-grid">
                @foreach ($legalities as $legal)
                    <article class="legal-card">
                        <img src="{{ asset($legal['image']) }}" alt="{{ $legal['name'] }}">

                        <a href="#">
                            Lihat Izin
                            <span>↗</span>
                        </a>
                    </article>
                @endforeach
            </div>

        </div>
    </section>

    {{-- LAPORAN KEUANGAN --}}
    <section class="report-section">
        <div class="about-container">

            <span class="section-pill">Laporan Keuangan</span>

            <p class="report-desc">
                Lihat laporan keuangan orangbaik.id yang telah diaudit secara independen
                setiap tahun sebagai wujud komitmen kami terhadap transparansi dan
                pengelolaan dana yang amanah.
            </p>

            <div class="year-tabs">
                @foreach ($years as $index => $year)
                    <button class="{{ $index === 0 ? 'active' : '' }}" type="button">
                        {{ $year }}
                    </button>
                @endforeach
            </div>

            <a href="#" class="report-link">
                <span>▣</span>
                <strong>Laporan Keuangan 2025</strong>
                <i>›</i>
            </a>

        </div>
    </section>

    {{-- FAQ --}}
    <section class="faq-section">
        <div class="about-container">

            <h2>Pertanyaan Yang Sering Diajukan Tentang Kitabisa</h2>

            <div class="faq-list">
                @foreach ($faqs as $faq)
                    <details class="faq-item">
                        <summary>
                            <span>{{ $faq }}</span>
                            <b>+</b>
                        </summary>

                        <p>
                            OrangBaik.id berkomitmen menjaga transparansi, keamanan, dan
                            kepercayaan dalam setiap proses donasi maupun galang dana.
                        </p>
                    </details>
                @endforeach
            </div>

        </div>
    </section>

</main>

@include('components.footer')

</body>
</html>