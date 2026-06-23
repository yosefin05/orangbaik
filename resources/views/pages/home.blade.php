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
        $categories = [
            [
                'name' => 'Zakat',
                'icon' => 'assets/zakat.png',
            ],
            [
                'name' => 'Wakaf',
                'icon' => 'assets/wakaf.png',
            ],
            [
                'name' => 'infaq',
                'icon' => 'assets/infaq.png',
            ],
            [
                'name' => 'Kemanusiaan',
                'icon' => 'assets/kemanusiaan.png',
            ],
            [
                'name' => 'Sedekah rutin',
                'icon' => 'assets/sedekah-rutin.png',
            ],
            [
                'name' => 'Lainnya',
                'icon' => 'assets/lainnya.png',
            ],
        ];

        $campaigns = [
            [
                'title' => 'Sedekah Makan untuk Yatim dan Dhuafa',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 200.000.000',
                'image' => 'assets/slide3.png',
            ],
            [
                'title' => 'Bantu Korban Bencana Banjir Sumatera',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 85.000.000',
                'image' => 'assets/slide3.png',
            ],
            [
                'title' => 'Wakaf Pendidikan untuk Santri Penghafal Qur’an',
                'organizer' => "Dompet Al-Qur'an Indonesia",
                'amount' => 'Rp 125.000.000',
                'image' => 'assets/slide3.png',
            ],
        ];
    @endphp

    <main class="main">

        {{-- HERO --}}
        <section class="hero-section container">
            <div class="hero-slider">
                <div class="hero-card main-hero-card">
                    <img src="{{ asset('assets/slide1.png') }}" alt="Peduli Banjir Sumatera">
                </div>

                <div class="hero-card side-card">
                    <img src="{{ asset('assets/gngerti.jpg') }}" alt="Berita Terbaru">
                </div>
            </div>
        </section>

        {{-- CATEGORY --}}
        <section class="container section">
            <h2 class="section-title">Yuk, Berbuat Baik Hari Ini!</h2>

            <div class="category-grid">
                @foreach ($categories as $category)
                    <div class="category-item">
                        <div class="category-icon">
                            <img src="{{ asset($category['icon']) }}" alt="{{ $category['name'] }}">
                        </div>
                        <p>{{ $category['name'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- DARURAT --}}
        <section class="section-border">
            <div class="container section">
                <h2 class="section-title">Darurat! Bantu Sekarang</h2>

                <div class="campaign-grid">
                    @foreach ($campaigns as $campaign)
                        <article class="campaign-card">
                            <img 
                                class="campaign-image" 
                                src="{{ asset($campaign['image']) }}" 
                                alt="{{ $campaign['title'] }}"
                                loading="lazy"
                            >

                            <div class="campaign-body">
                                <h3>{{ $campaign['title'] }}</h3>
                                <p>{{ $campaign['organizer'] }} <span>●</span></p>

                                <div class="campaign-price">
                                    <strong>{{ $campaign['amount'] }}</strong>
                                    <span>Terkumpul</span>
                                </div>

                                <div class="progress">
                                    <div class="progress-fill"></div>
                                </div>

                                <div class="campaign-meta">
                                    <span>👤 100rb + donatur</span>
                                    <span>∞</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- YANG BARU --}}
        <section class="container section">
            <h2 class="section-title">Yuk, Lihat yang Baru!</h2>

            <div class="new-grid">
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
        </section>

        {{-- PEMBERDAYAAN --}}
        <section class="section-border">
            <div class="container section">
                <h2 class="section-title">Pemberdayaan Berkelanjutan</h2>

                <div class="campaign-grid">
                    @foreach ($campaigns as $campaign)
                        <article class="campaign-card">
                            <img 
                                class="campaign-image" 
                                src="{{ asset($campaign['image']) }}" 
                                alt="{{ $campaign['title'] }}"
                                loading="lazy"
                            >

                            <div class="campaign-body">
                                <h3>{{ $campaign['title'] }}</h3>
                                <p>{{ $campaign['organizer'] }} <span>●</span></p>

                                <div class="campaign-price">
                                    <strong>{{ $campaign['amount'] }}</strong>
                                    <span>Terkumpul</span>
                                </div>

                                <div class="progress">
                                    <div class="progress-fill"></div>
                                </div>

                                <div class="campaign-meta">
                                    <span>👤 100rb + donatur</span>
                                    <span>∞</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- TESTIMONIAL --}}
        <section class="testimonial section-border">
            <div class="container section">
                <h2 class="section-title">Apa Kata Mereka?</h2>

                <div class="testimonial-content">
                    <button class="slider-btn" type="button">‹</button>

                    <div class="testimonial-text">
                        <p>
                            “DQ adalah lembaga resmi dari pemerintah, serta Zakat Infaq dan Sedekah
                            yang diberikan oleh saudara muslimin-muslimat sekalian Insya Allah akan
                            disalurkan dengan amanah dan tepat sasaran.”
                        </p>

                        <img 
                            src="{{ asset('assets/testimoni.jpg') }}" 
                            alt="Elis Masitoh"
                            loading="lazy"
                        >

                        <h3>Elis Masitoh, S.SiT, MM.</h3>
                        <span>Direktur TTIKK Kementerian Perindustrian RI</span>
                    </div>

                    <button class="slider-btn" type="button">›</button>
                </div>
            </div>
        </section>

        {{-- LIST CAMPAIGN --}}
        <section class="container section">
            <h2 class="section-title">Pilih Kategori Favoritmu</h2>

            <div class="category-grid small">
                @foreach ($categories as $category)
                    <div class="category-item">
                        <div class="category-icon">
                            <img src="{{ asset($category['icon']) }}" alt="{{ $category['name'] }}">
                        </div>
                        <p>{{ $category['name'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="list-campaign">
                @for ($i = 0; $i < 5; $i++)
                    <article class="list-card">
                        <img 
                            src="{{ asset('assets/yatim.jpg') }}" 
                            alt="Beasiswa Yatim Dhuafa"
                            loading="lazy"
                        >

                        <div class="list-body">
                            <h3>Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulans Gratis</h3>
                            <p>Dompet Al-Qur'an Indonesia <span>●</span></p>

                            <strong>Rp 200.000.000</strong>

                            <div class="progress">
                                <div class="progress-fill"></div>
                            </div>

                            <div class="campaign-meta">
                                <span>👤 100rb + donatur</span>
                                <span>∞</span>
                            </div>
                        </div>
                    </article>
                @endfor
            </div>
        </section>

    </main>

    @include('components.footer')

</body>
</html>