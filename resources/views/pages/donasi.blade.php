<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi.css') }}">
</head>
<body>

    @include('components.header')

    @php
        $categories = [
            ['name' => 'Zakat', 'icon' => 'assets/zakat.svg'],
            ['name' => 'Wakaf', 'icon' => 'assets/wakaf.svg'],
            ['name' => 'Infaq', 'icon' => 'assets/infaq.svg'],
            ['name' => 'Kemanusiaan', 'icon' => 'assets/kemanusiaan.svg'],
            ['name' => 'Sedekah Rutin', 'icon' => 'assets/sedekah-rutin.svg'],
            ['name' => 'Lainnya', 'icon' => 'assets/lainnya.svg'],
        ];

        $campaigns = [
            [
                'title' => 'Sedekah Makan untuk Yatim dan Dhuafa',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Bantu Korban Banjir Sumatera',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 180.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Wakaf Pendidikan Santri Penghafal Qur’an',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 125.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Sedekah Pangan untuk Keluarga Dhuafa',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 90.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Bantu Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 150.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Bantu Anak Yatim dan Dhuafa',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 75.000.000',
                'image' => 'assets/slide1.png',
            ],
        ];

        $programs = [
            [
                'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Bantu Pendidikan Anak Yatim dan Dhuafa',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 150.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Wakaf Al-Qur’an untuk Santri Pelosok Negeri',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 90.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Sedekah Pangan untuk Keluarga Dhuafa',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 75.000.000',
                'image' => 'assets/slide1.png',
            ],
            [
                'title' => 'Bantu Renovasi Rumah Ibadah dan Pesantren',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 110.000.000',
                'image' => 'assets/slide1.png',
            ],
        ];
    @endphp

    <main class="donasi-page">

        {{-- CATEGORY --}}
        <section class="container donasi-section">
            <h1 class="donasi-title">Yuk, Berbuat Baik Hari Ini!</h1>

            <div class="donasi-category-grid">
                @foreach ($categories as $category)
                    <a href="#" class="donasi-category-item">
                        <div class="donasi-category-icon">
                            <img src="{{ asset($category['icon']) }}" alt="{{ $category['name'] }}">
                        </div>
                        <p>{{ $category['name'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- DARURAT --}}
        <section class="donasi-border">
            <div class="container donasi-section">
                <h2 class="donasi-section-title">Darurat! Bantu Sekarang</h2>

                <div class="campaign-carousel">
                    <div class="donasi-card-grid">
                        @foreach ($campaigns as $campaign)
                            <article class="donasi-card">
                                <img 
                                    class="donasi-card-image"
                                    src="{{ asset($campaign['image']) }}"
                                    alt="{{ $campaign['title'] }}"
                                    loading="lazy"
                                >

                                <div class="donasi-card-body">
                                    <h3>{{ $campaign['title'] }}</h3>

                                    <p class="donasi-organizer">
                                        {{ $campaign['organizer'] }}
                                        <span>●</span>
                                    </p>

                                    <div class="donasi-amount">
                                        <strong>{{ $campaign['amount'] }}</strong>
                                        <span>Terkumpul</span>
                                    </div>

                                    <div class="donasi-progress">
                                        <div class="donasi-progress-fill"></div>
                                    </div>

                                    <div class="donasi-meta">
                                        <span>👤 100rb ± donatur</span>
                                        <span>∞</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- YANG BARU --}}
        <section class="donasi-border">
            <div class="container donasi-section">
                <h2 class="donasi-section-title">Yuk, Lihat yang Baru!</h2>

                <div class="donasi-new-grid">
                    <img 
                        src="{{ asset('assets/sedekah.png') }}" 
                        alt="Sedekah Tidaklah Mengurangi Harta"
                        loading="lazy"
                    >

                    <img 
                        src="{{ asset('assets/sedekah.png') }}" 
                        alt="Sedekah Tidaklah Mengurangi Harta"
                        loading="lazy"
                    >
                </div>
            </div>
        </section>

        {{-- PEMBERDAYAAN --}}
        <section class="donasi-border">
            <div class="container donasi-section">
                <h2 class="donasi-section-title">Pemberdayaan Berkelanjutan</h2>

                <div class="campaign-carousel">
                    <div class="donasi-card-grid">
                        @foreach ($campaigns as $campaign)
                            <article class="donasi-card">
                                <img 
                                    class="donasi-card-image"
                                    src="{{ asset($campaign['image']) }}"
                                    alt="{{ $campaign['title'] }}"
                                    loading="lazy"
                                >

                                <div class="donasi-card-body">
                                    <h3>{{ $campaign['title'] }}</h3>

                                    <p class="donasi-organizer">
                                        {{ $campaign['organizer'] }}
                                        <span>●</span>
                                    </p>

                                    <div class="donasi-amount">
                                        <strong>{{ $campaign['amount'] }}</strong>
                                        <span>Terkumpul</span>
                                    </div>

                                    <div class="donasi-progress">
                                        <div class="donasi-progress-fill"></div>
                                    </div>

                                    <div class="donasi-meta">
                                        <span>👤 100rb ± donatur</span>
                                        <span>∞</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- PROGRAM LAINNYA --}}
        <section class="container donasi-section">
            <h2 class="donasi-section-title">Program Lainnya</h2>

            <div class="program-list">
                @foreach ($programs as $program)
                    <article class="program-card">
                        <img 
                            class="program-image"
                            src="{{ asset($program['image']) }}"
                            alt="{{ $program['title'] }}"
                            loading="lazy"
                        >

                        <div class="program-body">
                            <h3>{{ $program['title'] }}</h3>

                            <p class="donasi-organizer">
                                {{ $program['organizer'] }}
                                <span>●</span>
                            </p>

                            <div class="program-amount">
                                <strong>{{ $program['amount'] }}</strong>
                                <span>Terkumpul</span>
                            </div>

                            <div class="donasi-progress">
                                <div class="donasi-progress-fill"></div>
                            </div>

                            <div class="donasi-meta">
                                <span>👤 100rb ± donatur</span>
                                <span>∞</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

    </main>

    @include('components.footer')

</body>
</html>