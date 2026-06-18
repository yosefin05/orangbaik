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

    <main class="main">

        <section class="hero-section container">
            <div class="hero-slider">
                <div class="hero-card">
                    <img src="{{ asset('assets/banjir.jpg') }}" alt="Peduli Banjir Sumatera">
                </div>

                <div class="hero-card side-card">
                    <img src="{{ asset('assets/news.jpg') }}" alt="Berita Terbaru">
                </div>
            </div>
        </section>

        <section class="container section">
            <h2 class="section-title">Yuk, Berbuat Baik Hari Ini!</h2>

            <div class="category-grid">
                @php
                    $categories = ['Zakat', 'Wakaf', 'Sedekah', 'Kemanusiaan', 'Lainnya', 'Lainnya'];
                @endphp

                @foreach ($categories as $category)
                    <div class="category-item">
                        <div class="category-icon">
                            <img src="{{ asset('assets/category-icon.png') }}" alt="{{ $category }}">
                        </div>
                        <p>{{ $category }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="section-border">
            <div class="container section">
                <h2 class="section-title">Darurat! Bantu Sekarang</h2>

                <div class="campaign-grid">
                    @for ($i = 0; $i < 3; $i++)
                        <article class="campaign-card">
                            <img class="campaign-image" src="{{ asset('assets/banjir.jpg') }}" alt="Campaign">

                            <div class="campaign-body">
                                <h3>Sedekah Makan untuk Yatim dan Dhuafa</h3>
                                <p>Dompet Al-Qur'an Indonesia <span>●</span></p>

                                <div class="campaign-price">
                                    <strong>Rp 200.000.000</strong>
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
                    @endfor
                </div>
            </div>
        </section>

        <section class="container section">
            <h2 class="section-title">Yuk, Lihat yang Baru!</h2>

            <div class="new-grid">
                <img src="{{ asset('assets/sedekah.jpg') }}" alt="Sedekah Tidaklah Mengurangi Harta">
                <img src="{{ asset('assets/sedekah.jpg') }}" alt="Sedekah Tidaklah Mengurangi Harta">
            </div>
        </section>

        <section class="section-border">
            <div class="container section">
                <h2 class="section-title">Pemberdayaan Berkelanjutan</h2>

                <div class="campaign-grid">
                    @for ($i = 0; $i < 3; $i++)
                        <article class="campaign-card">
                            <img class="campaign-image" src="{{ asset('assets/banjir.jpg') }}" alt="Campaign">

                            <div class="campaign-body">
                                <h3>Sedekah Makan untuk Yatim dan Dhuafa</h3>
                                <p>Dompet Al-Qur'an Indonesia <span>●</span></p>

                                <div class="campaign-price">
                                    <strong>Rp 200.000.000</strong>
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
                    @endfor
                </div>
            </div>
        </section>

        <section class="testimonial section-border">
            <div class="container section">
                <h2 class="section-title">Apa Kata Mereka?</h2>

                <div class="testimonial-content">
                    <button class="slider-btn">‹</button>

                    <div class="testimonial-text">
                        <p>
                            “DQ adalah lembaga resmi dari pemerintah, serta Zakat Infaq dan Sedekah
                            yang diberikan oleh saudara muslimin-muslimat sekalian Insya Allah akan
                            disalurkan dengan amanah dan tepat sasaran.”
                        </p>

                        <img src="{{ asset('assets/testimoni.jpg') }}" alt="Elis Masitoh">

                        <h3>Elis Masitoh, S.SiT, MM.</h3>
                        <span>Direktur TTIKK Kementerian Perindustrian RI</span>
                    </div>

                    <button class="slider-btn">›</button>
                </div>
            </div>
        </section>

        <section class="container section">
            <h2 class="section-title">Pilih Kategori Favoritmu</h2>

            <div class="category-grid small">
                @foreach (['Zakat', 'Wakaf', 'Sedekah', 'Kemanusiaan', 'Lainnya', 'Lainnya'] as $category)
                    <div class="category-item">
                        <div class="category-icon">
                            <img src="{{ asset('assets/category-icon.png') }}" alt="{{ $category }}">
                        </div>
                        <p>{{ $category }}</p>
                    </div>
                @endforeach
            </div>

            <div class="list-campaign">
                @for ($i = 0; $i < 5; $i++)
                    <article class="list-card">
                        <img src="{{ asset('assets/yatim.jpg') }}" alt="Beasiswa Yatim Dhuafa">

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