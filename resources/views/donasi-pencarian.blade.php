<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Donasi - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-pencarian.css') }}">
</head>
<body>

    @php
        $programs = [
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
        ];
    @endphp

    <main class="search-page">
        <div class="search-container">

            {{-- BACK --}}
            <button class="back-button" type="button" onclick="history.back()">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M15 18L9 12L15 6" />
                </svg>
                <span>Kembali</span>
            </button>

            {{-- FILTER --}}
            <div class="filter-bar">
                <button class="filter-button" type="button">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7H14" />
                        <path d="M18 7H20" />
                        <path d="M16 5V9" />
                        <path d="M4 12H8" />
                        <path d="M12 12H20" />
                        <path d="M10 10V14" />
                        <path d="M4 17H13" />
                        <path d="M17 17H20" />
                        <path d="M15 15V19" />
                    </svg>
                    <span>Filter</span>
                </button>

                <button class="filter-button" type="button">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7H15" />
                        <path d="M4 12H12" />
                        <path d="M4 17H15" />
                        <path d="M18 6L21 9L18 12" />
                        <path d="M21 9H16" />
                        <path d="M18 18L21 15L18 12" />
                    </svg>
                    <span>Urutkan</span>
                </button>

                <button class="filter-button" type="button">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 5H20L14 12V18L10 20V12L4 5Z" />
                    </svg>
                    <span>Tipe Penggalang</span>
                </button>
            </div>

            {{-- RESULT --}}
            <section class="result-section">
                <h1>Program yang Kamu Cari</h1>

                <div class="result-list">
                    @foreach ($programs as $program)
                        <article class="result-card">
                            <img 
                                src="{{ asset($program['image']) }}" 
                                alt="{{ $program['title'] }}"
                                class="result-image"
                                loading="lazy"
                            >

                            <div class="result-body">
                                <h2>{{ $program['title'] }}</h2>

                                <p class="result-organizer">
                                    {{ $program['organizer'] }}
                                    <span>●</span>
                                </p>

                                <div class="result-amount">
                                    <strong>{{ $program['amount'] }}</strong>
                                    <span>Terkumpul</span>
                                </div>

                                <div class="result-progress">
                                    <div class="result-progress-fill"></div>
                                </div>

                                <div class="result-meta">
                                    <span>👤 100rb ± donatur</span>
                                    <span>∞</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

        </div>
    </main>

    @include('components.footer')

</body>
</html>