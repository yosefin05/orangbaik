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
            </div>

            <div class="about-hero-image">
                <img
                    src="{{ asset('assets/about-person.png') }}"
                    alt="Relawan OrangBaik.id">
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
                @foreach ($legalities as $legal)
                    <article class="about-legal-card">
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

    {{-- FAQ --}}
    <section class="about-section about-faq-section">
        <div class="container">

            <div class="about-section-heading">
                <span class="about-section-label">FAQ</span>
                <h2>Pertanyaan yang Sering Diajukan</h2>
                <p>
                    Beberapa pertanyaan umum seputar OrangBaik.id, legalitas,
                    penggalang dana, dan laporan program.
                </p>
            </div>

            <div class="about-faq-list">
                @forelse ($faqs as $faq)
                    <details class="about-faq-item">
                        <summary>
                            <span>{{ $faq->pertanyaan }}</span>
                            <i class="bi bi-plus-lg"></i>
                        </summary>

                        <p>{{ $faq->jawaban }}</p>
                    </details>
                @empty
                    <p>Belum ada pertanyaan yang ditampilkan.</p>
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