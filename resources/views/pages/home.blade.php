<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body>

@include('components.header')

@php
    $heroSlides = [
        [
            'image' => 'assets/slide1.png',
            'title' => 'Peduli Banjir Sumatera',
        ],
        [
            'image' => 'assets/sedekah.png',
            'title' => 'Sedekah Tidak Mengurangi Harta',
        ],
        [
            'image' => 'assets/slide1.png',
            'title' => 'Bantu Pendidikan Anak Yatim',
        ],
    ];

    $sideNews = [
        [
            'image' => 'assets/gngerti.jpg',
            'label' => 'Berita',
            'title' => 'Update terbaru program kebaikan OrangBaik.id',
            'url' => '#',
        ],
        [
            'image' => 'assets/sedekah.png',
            'label' => 'Artikel',
            'title' => 'Keutamaan sedekah dan manfaatnya untuk sesama',
            'url' => '#',
        ],
        [
            'image' => 'assets/slide1.png',
            'label' => 'Kabar Baik',
            'title' => 'Campaign pendidikan mulai menjangkau penerima manfaat',
            'url' => '#',
        ],
    ];

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
            'progress' => '55%',
        ],
        [
            'title' => 'Bantu Korban Bencana Banjir Sumatera',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 85.000.000',
            'image' => 'assets/slide1.png',
            'progress' => '40%',
        ],
        [
            'title' => 'Wakaf Pendidikan untuk Santri Penghafal Qur’an',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 125.000.000',
            'image' => 'assets/slide1.png',
            'progress' => '62%',
        ],
        [
            'title' => 'Sedekah Pangan untuk Keluarga Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 75.000.000',
            'image' => 'assets/slide1.png',
            'progress' => '35%',
        ],
    ];

    $listCampaigns = [
        [
            'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 200.000.000',
            'image' => 'assets/slide1.png',
            'progress' => '55%',
        ],
        [
            'title' => 'Bantu Pendidikan Anak Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 150.000.000',
            'image' => 'assets/slide1.png',
            'progress' => '48%',
        ],
        [
            'title' => 'Wakaf Al-Qur’an untuk Santri Pelosok Negeri',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 90.000.000',
            'image' => 'assets/slide1.png',
            'progress' => '38%',
        ],
        [
            'title' => 'Sedekah Pangan untuk Keluarga Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 75.000.000',
            'image' => 'assets/slide1.png',
            'progress' => '34%',
        ],
        [
            'title' => 'Bantu Renovasi Rumah Ibadah dan Pesantren',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 110.000.000',
            'image' => 'assets/slide1.png',
            'progress' => '46%',
        ],
    ];
@endphp

<main class="main">

    {{-- HERO --}}
    <section class="hero-section">
        <div class="container hero-layout">

            <div class="hero-card hero-main-slider">
                @foreach ($heroSlides as $index => $slide)
                    <div class="hero-slide {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ asset($slide['image']) }}" alt="{{ $slide['title'] }}">
                    </div>
                @endforeach

                <div class="hero-dots"></div>
            </div>

            <div class="hero-side-slider">
                @foreach ($sideNews as $index => $news)
                    <a href="{{ $news['url'] }}" class="hero-side-card {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ asset($news['image']) }}" alt="{{ $news['title'] }}">

                        <div class="hero-side-body">
                            <span>{{ $news['label'] }}</span>
                            <h3>{{ $news['title'] }}</h3>
                        </div>
                    </a>
                @endforeach

                <div class="hero-side-dots"></div>
            </div>

        </div>
    </section>

    {{-- CATEGORY ATAS --}}
    <section class="section">
        <div class="container">
            <h2 class="section-title">Yuk, Berbuat Baik Hari Ini!</h2>

            <div class="category-grid">
                @foreach ($categories as $category)
                    <a href="{{ url('donasi') }}" class="category-item">
                        <div class="category-icon">
                            <img src="{{ asset($category['icon']) }}" alt="{{ $category['name'] }}">
                        </div>

                        <p>{{ $category['name'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- DARURAT --}}
    <section class="section section-border">
        <div class="container">
            <h2 class="section-title">Darurat! Bantu Sekarang</h2>

            <div class="campaign-grid">
                @foreach ($campaigns as $campaign)
                    <article class="campaign-card">
                        <a href="{{ url('donasi') }}">
                            <img
                                class="campaign-image"
                                src="{{ asset($campaign['image']) }}"
                                alt="{{ $campaign['title'] }}"
                                loading="lazy">
                        </a>

                        <div class="campaign-body">
                            <h3>{{ $campaign['title'] }}</h3>

                            <p>
                                {{ $campaign['organizer'] }}
                                <span>●</span>
                            </p>

                            <div class="campaign-price">
                                <strong>{{ $campaign['amount'] }}</strong>
                                <span>Terkumpul</span>
                            </div>

                            <div class="progress">
                                <div class="progress-fill" style="width: {{ $campaign['progress'] }}"></div>
                            </div>

                            <div class="campaign-meta">
                                <span>100rb+ donatur</span>
                                <span>∞</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- YANG BARU --}}
    <section class="section">
        <div class="container">
            <h2 class="section-title">Yuk, Lihat yang Baru!</h2>

            <div class="new-grid">
                <img
                    src="{{ asset('assets/sedekah.png') }}"
                    alt="Sedekah Tidaklah Mengurangi Harta"
                    loading="lazy">

                <img
                    src="{{ asset('assets/sedekah.png') }}"
                    alt="Sedekah Tidaklah Mengurangi Harta"
                    loading="lazy">
            </div>
        </div>
    </section>

    {{-- PEMBERDAYAAN --}}
    <section class="section section-border">
        <div class="container">
            <h2 class="section-title">Pemberdayaan Berkelanjutan</h2>

            <div class="campaign-grid">
                @foreach ($campaigns as $campaign)
                    <article class="campaign-card">
                        <a href="{{ url('donasi') }}">
                            <img
                                class="campaign-image"
                                src="{{ asset($campaign['image']) }}"
                                alt="{{ $campaign['title'] }}"
                                loading="lazy">
                        </a>

                        <div class="campaign-body">
                            <h3>{{ $campaign['title'] }}</h3>

                            <p>
                                {{ $campaign['organizer'] }}
                                <span>●</span>
                            </p>

                            <div class="campaign-price">
                                <strong>{{ $campaign['amount'] }}</strong>
                                <span>Terkumpul</span>
                            </div>

                            <div class="progress">
                                <div class="progress-fill" style="width: {{ $campaign['progress'] }}"></div>
                            </div>

                            <div class="campaign-meta">
                                <span>100rb+ donatur</span>
                                <span>∞</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- TESTIMONIAL --}}
    <section class="section testimonial">
        <div class="container">
            <h2 class="section-title">Apa Kata Mereka?</h2>

            <div class="testimonial-wrapper">
                @forelse(($testimoni ?? []) as $item)
                    <div class="testimonial-item {{ $loop->first ? 'active' : '' }}">
                        <p class="testimonial-description">
                            "{{ $item->isi_testimoni }}"
                        </p>

                        @if($item->foto_profil)
                            <img
                                src="{{ asset('storage/' . $item->foto_profil) }}"
                                alt="{{ $item->nama }}"
                                loading="lazy">
                        @else
                            <img
                                src="{{ asset('assets/logo.png') }}"
                                alt="{{ $item->nama }}"
                                loading="lazy">
                        @endif

                        <h3>{{ $item->nama }}</h3>
                        <span>{{ $item->jabatan }}</span>
                    </div>
                @empty
                    <div class="testimonial-item active">
                        <p class="testimonial-description">
                            "OrangBaik.id memudahkan kami untuk ikut berbagi dan mendukung program kebaikan."
                        </p>

                        <img
                            src="{{ asset('assets/logo.png') }}"
                            alt="OrangBaik.id"
                            loading="lazy">

                        <h3>OrangBaik.id</h3>
                        <span>Platform Donasi</span>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- KATEGORI FAVORIT --}}
    <section class="section">
        <div class="container">
            <h2 class="section-title">Pilih Kategori Favoritmu</h2>

            <div class="category-grid category-grid-small">
                @foreach ($categories as $category)
                    <a href="{{ url('donasi') }}" class="category-item">
                        <div class="category-icon">
                            <img src="{{ asset($category['icon']) }}" alt="{{ $category['name'] }}">
                        </div>

                        <p>{{ $category['name'] }}</p>
                    </a>
                @endforeach
            </div>

            <div class="list-campaign">
                @foreach ($listCampaigns as $campaign)
                    <article class="list-card">
                        <a href="{{ url('donasi') }}">
                            <img
                                src="{{ asset($campaign['image']) }}"
                                alt="{{ $campaign['title'] }}"
                                loading="lazy">
                        </a>

                        <div class="list-body">
                            <h3>{{ $campaign['title'] }}</h3>

                            <p>
                                {{ $campaign['organizer'] }}
                                <span>●</span>
                            </p>

                            <strong>{{ $campaign['amount'] }}</strong>

                            <div class="progress">
                                <div class="progress-fill" style="width: {{ $campaign['progress'] }}"></div>
                            </div>

                            <div class="campaign-meta">
                                <span>100rb+ donatur</span>
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
<script src="{{ asset('js/home.js') }}"></script>

</body>
</html>