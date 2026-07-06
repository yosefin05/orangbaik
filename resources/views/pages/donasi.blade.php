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
            'donatur' => '100rb ± donatur',
        ],
        [
            'title' => 'Bantu Korban Bencana Banjir Sumatera',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 180.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '82rb ± donatur',
        ],
        [
            'title' => 'Wakaf Pendidikan untuk Santri Penghafal Qur’an',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 125.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '61rb ± donatur',
        ],
        [
            'title' => 'Sedekah Pangan untuk Keluarga Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 90.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '45rb ± donatur',
        ],
        [
            'title' => 'Bantu Layanan Ambulans Gratis',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 150.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '73rb ± donatur',
        ],
        [
            'title' => 'Bantu Anak Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 75.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '39rb ± donatur',
        ],
    ];

    $programs = [
        [
            'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 200.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '100rb ± donatur',
        ],
        [
            'title' => 'Bantu Pendidikan Anak Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 150.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '73rb ± donatur',
        ],
        [
            'title' => 'Wakaf Al-Qur’an untuk Santri Pelosok Negeri',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 90.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '45rb ± donatur',
        ],
        [
            'title' => 'Sedekah Pangan untuk Keluarga Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 75.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '39rb ± donatur',
        ],
        [
            'title' => 'Bantu Renovasi Rumah Ibadah dan Pesantren',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 110.000.000',
            'image' => 'assets/slide1.png',
            'donatur' => '58rb ± donatur',
        ],
    ];
@endphp

<main class="donasi-page page-wrapper">

    <section class="donasi-section">
        <div class="container">

            <h1 class="donasi-title">
                Yuk, Berbuat Baik Hari Ini!
            </h1>

            <div class="donasi-category-grid">
                @foreach ($categories as $category)
                    <a href="#" class="donasi-category-item">
                        <div class="donasi-category-icon">
                            <img
                                src="{{ asset($category['icon']) }}"
                                alt="{{ $category['name'] }}"
                                loading="lazy"
                            >
                        </div>

                        <p>{{ $category['name'] }}</p>
                    </a>
                @endforeach
            </div>

        </div>
    </section>

    <section class="donasi-border donasi-section">
        <div class="container">

            <h2 class="donasi-section-title">
                Darurat! Bantu Sekarang
            </h2>

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
                                    <span aria-hidden="true">●</span>
                                </p>

                                <div class="donasi-amount">
                                    <strong>{{ $campaign['amount'] }}</strong>
                                    <span>Terkumpul</span>
                                </div>

                                <div class="donasi-progress">
                                    <div class="donasi-progress-fill"></div>
                                </div>

                                <div class="donasi-meta">
                                    <span>{{ $campaign['donatur'] }}</span>
                                    <span>∞</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    <section class="donasi-border donasi-section">
        <div class="container">

            <h2 class="donasi-section-title">
                Yuk, Lihat yang Baru!
            </h2>

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

    <section class="donasi-border donasi-section">
        <div class="container">

            <h2 class="donasi-section-title">
                Pemberdayaan Berkelanjutan
            </h2>

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
                                    <span aria-hidden="true">●</span>
                                </p>

                                <div class="donasi-amount">
                                    <strong>{{ $campaign['amount'] }}</strong>
                                    <span>Terkumpul</span>
                                </div>

                                <div class="donasi-progress">
                                    <div class="donasi-progress-fill"></div>
                                </div>

                                <div class="donasi-meta">
                                    <span>{{ $campaign['donatur'] }}</span>
                                    <span>∞</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    <section class="donasi-section">
        <div class="container">

            <h2 class="donasi-section-title">
                Program Lainnya
            </h2>

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
                                <span aria-hidden="true">●</span>
                            </p>

                            <div class="program-amount">
                                <strong>{{ $program['amount'] }}</strong>
                                <span>Terkumpul</span>
                            </div>

                            <div class="donasi-progress">
                                <div class="donasi-progress-fill"></div>
                            </div>

                            <div class="donasi-meta">
                                <span>{{ $program['donatur'] }}</span>
                                <span>∞</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

        </div>
    </section>

</main>

@include('components.footer')
<script src="{{ asset('js/header.js') }}"></script>

</body>
</html>