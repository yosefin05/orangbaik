<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tentang.css') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

@include('components.header')

@php
    $legalities = [
        ['image' => 'assets/legal-1.png', 'name' => 'Legalitas 1'],
        ['image' => 'assets/legal-2.png', 'name' => 'Legalitas 2'],
        ['image' => 'assets/legal-3.png', 'name' => 'Legalitas 3'],
        ['image' => 'assets/legal-4.png', 'name' => 'Legalitas 4'],
        ['image' => 'assets/legal-5.png', 'name' => 'Legalitas 5'],
    ];

    $years = ['2025', '2024', '2023', '2022', '2021', '2020', '2019', '2018', '2017', '2016'];
@endphp

<main class="about-page">

    {{-- HERO --}}
    <section class="about-hero">
        <div class="about-hero-decor" aria-hidden="true">
            <span class="help-blob help-blob-1"></span>
            <span class="help-blob help-blob-2"></span>
        </div>

        <div class="container about-hero-inner">

            <div class="about-hero-content">
                <span class="about-eyebrow">Tentang Kami</span>

                <h1>
                    Siapa <span>OrangBaik.id?</span>
                </h1>

                <p>
                    OrangBaik.id merupakan platform donasi dan galang dana online
                    yang dikelola untuk membantu masyarakat berbagi kebaikan secara
                    mudah, aman, dan transparan.
                </p>

                <p>
                    Platform ini hadir untuk mendukung pengelolaan dana zakat, infak,
                    sedekah, wakaf, serta program sosial, pendidikan, dakwah, ekonomi,
                    dan kemanusiaan.
                </p>

                <div class="about-trust-row">
                    <span class="about-trust-item"><i class="bi bi-patch-check-fill"></i> Legalitas Resmi</span>
                    <span class="about-trust-item"><i class="bi bi-graph-up"></i> Laporan Transparan</span>
                    <span class="about-trust-item"><i class="bi bi-heart-fill"></i> Amanah & Terpercaya</span>
                </div>
            </div>

            <div class="about-hero-image">
                <img
                    src="{{ asset('assets/about-person.png') }}"
                    alt="Relawan OrangBaik.id">

                <span class="about-hero-badge">
                    <i class="bi bi-patch-check-fill"></i>
                    Terverifikasi Legal
                </span>
            </div>

        </div>
    </section>

    {{-- VISI MISI --}}
    <section class="about-section about-vision-section">
        <div class="container about-vision-layout">

            <div class="about-section-heading">
                <span class="about-section-label">Visi & Misi</span>
                <h2>Visi dan Misi Lembaga</h2>
                <p>
                    Menjadi landasan dalam membangun layanan kebaikan yang profesional,
                    amanah, dan berdampak bagi masyarakat.
                </p>
            </div>

            <div class="about-vision-grid">

                <article class="about-vision-card">
                    <div class="about-card-icon">
                        <i class="bi bi-brightness-high-fill"></i>
                    </div>

                    <div>
                        <h3>Visi Lembaga</h3>

                        <p>
                            Menjadi lembaga profesional dalam pemberdayaan dan pelayanan,
                            serta membangun masyarakat yang akrab dengan Al-Qur'an.
                        </p>
                    </div>
                </article>

                <article class="about-vision-card">
                    <div class="about-card-icon">
                        <i class="bi bi-grid-1x2-fill"></i>
                    </div>

                    <div>
                        <h3>Misi Lembaga</h3>

                        <ol>
                            <li>Aktif dalam membangun jaringan filantropi yang profesional.</li>
                            <li>Meningkatkan kemandirian dan mengakrabkan masyarakat Indonesia dengan Al-Qur'an.</li>
                            <li>Meningkatkan sumber daya melalui keunggulan lembaga.</li>
                        </ol>
                    </div>
                </article>

            </div>

        </div>
    </section>

    {{-- LEGALITAS --}}
    <section class="about-section about-legal-section">
        <div class="container">

            <div class="about-section-heading">
                <span class="about-section-label">Legalitas</span>
                <h2>Legalitas Lembaga</h2>
                <p>
                    Lembaga kami beroperasi secara resmi dan profesional dengan legalitas
                    yang sah sebagai bentuk komitmen dalam membangun kepercayaan,
                    transparansi, dan pelayanan yang bertanggung jawab.
                </p>
            </div>

            <div class="about-legal-grid">
                @foreach ($legalities as $index => $legal)
                    <article class="about-legal-card">
                        <span class="about-legal-index">{{ sprintf('%02d', $index + 1) }}</span>

                        <img
                            src="{{ asset($legal['image']) }}"
                            alt="{{ $legal['name'] }}">

                        <a href="#">
                            <span>Lihat Izin</span>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </article>
                @endforeach
            </div>

        </div>
    </section>

    {{-- LAPORAN KEUANGAN --}}
    <section class="about-section about-report-section">
        <div class="container">

            <div class="about-section-heading">
                <span class="about-section-label">Laporan Keuangan</span>
                <h2>Transparansi Laporan Keuangan</h2>
                <p>
                    Lihat laporan keuangan OrangBaik.id sebagai wujud komitmen terhadap
                    transparansi dan pengelolaan dana yang amanah.
                </p>
            </div>

            <div class="about-year-tabs">
                @foreach ($years as $index => $year)
                    <button
                        class="{{ $index === 0 ? 'active' : '' }}"
                        type="button">
                        {{ $year }}
                    </button>
                @endforeach
            </div>

            <a href="#" class="about-report-link">
                <span class="about-report-icon">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </span>

                <strong>Laporan Keuangan 2025</strong>

                <i class="bi bi-chevron-right"></i>
            </a>

        </div>
    </section>

    {{-- LOKASI & KONTAK (+ MAPS, dari Pusat Bantuan) --}}
    <section class="about-section about-location-section">
        <div class="container">

            <div class="about-section-heading">
                <span class="about-section-label">Lokasi & Kontak</span>
                <h2>Kunjungi Kantor Kami</h2>
                <p>
                    Datang langsung ke kantor kami untuk konsultasi, penyaluran donasi,
                    atau sekadar berkenalan lebih dekat dengan tim OrangBaik.id.
                </p>
            </div>

            <div class="about-location-layout">

                <div class="about-location-info">
                    <span class="about-location-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </span>

                    <h3>Lembaga Amil Zakat Dompet Alquran Indonesia</h3>
                    <p>
                        Ruko Citra City Blok R28, Sari Rogo, Sidoarjo,
                        Sidoarjo Regency, East Java 61234
                    </p>

                    <ul class="about-location-meta">
                        <li>
                            <i class="bi bi-clock-fill"></i>
                            Senin – Jumat, 08.00 – 16.00 WIB
                        </li>
                        <li>
                            <i class="bi bi-clock-fill"></i>
                            Sabtu, 08.00 – 12.00 WIB
                        </li>
                        <li>
                            <i class="bi bi-telephone-fill"></i>
                            +62 813-8500-2300
                        </li>
                        <li>
                            <i class="bi bi-envelope-fill"></i>
                            info@dompetalquran.or.id
                        </li>
                    </ul>

                </div>

                {{-- Map card, persis dari Pusat Bantuan --}}
                <div class="map-card">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.849706646301!2d112.7224737!3d-7.4655644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7e3d98f3cd39b%3A0xf9ba86c029a86e32!2sLembaga%20Amil%20Zakat%20Dompet%20Alquran%20Indonesia!5e0!3m2!1sid!2sid!4v1710000000000"
                        width="100%" height="260" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Lembaga Amil Zakat Dompet Alquran Indonesia - Ruko Citra City Blok R28, Sidoarjo">
                    </iframe>

                    <div class="map-address">
                        <span class="map-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </span>
                        <div class="map-text">
                            <span class="map-title">Peta Dompet Al Quran Indonesia</span>
                            <span class="map-subtitle">Lembaga Amil Zakat Dompet Alquran Indonesia</span>
                            <span class="map-location">
                                Ruko Citra City Blok R28, Sari Rogo, Sidoarjo, Sidoarjo Regency, East Java 61234
                            </span>
                        </div>
                        <a href="https://www.google.com/maps/dir//Lembaga+Amil+Zakat+Dompet+Alquran+Indonesia+Ruko+Citra+City+Blok+R28+Sari+Rogo+Sidoarjo"
                           target="_blank" class="map-open-btn">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Buka di Maps
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    {{-- FAQ --}}
    <section class="about-faq-section">
        <div class="container about-faq-layout">

            <div class="about-faq-intro">
                <span class="about-section-label">FAQ</span>
                <h2>Pertanyaan yang Sering Diajukan</h2>
                <p>
                    Beberapa pertanyaan umum seputar orangbaik.id, legalitas,
                    penggalang dana, dan laporan program.
                </p>
            </div>

            <div class="about-faq-list">
                @forelse ($faqs as $faq)
                    <details class="about-faq-item">
                        <summary>
                            <span>{{ $faq->pertanyaan }}</span>
                            <i class="bi bi-plus-lg about-faq-icon"></i>
                        </summary>
                        <div class="about-faq-answer">
                            <p>{{ $faq->jawaban }}</p>
                        </div>
                    </details>
                @empty
                    <p class="about-faq-empty">Belum ada pertanyaan yang ditampilkan.</p>
                @endforelse
            </div>

        </div>
    </section>

</main>

@include('components.footer')

<script src="{{ asset('js/header.js') }}"></script>
<script src="{{ asset('js/tentang.js') }}"></script>

</body>
</html>