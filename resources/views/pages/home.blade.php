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

        <button type="button" onclick="document.getElementById('modalDaftar').style.display='flex'"
            style="background:#2563eb; color:#fff; border:none; padding:10px 20px; border-radius:6px; font-size:14px; cursor:pointer;">
            Daftar Penggalang Dana
        </button>

        <div id="modalDaftar"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
            <div style="background:#fff; border-radius:8px; padding:24px; width:340px;">
                <h3 style="margin:0 0 4px; font-size:18px;">Daftar sebagai apa?</h3>
                <p style="margin:0 0 16px; font-size:14px; color:#666;">Pilih jenis penggalang dana yang ingin kamu
                    daftarkan.</p>

                <a href="/penggalang_dana_organisasi"
                    style="display:flex; align-items:center; gap:10px; padding:12px; margin-bottom:8px; border:1px solid #ddd; border-radius:6px; text-decoration:none; color:#000;">
                    <span style="font-size:20px;">🏢</span>
                    <span>
                        <div style="font-weight:bold; font-size:14px;">Organisasi</div>
                        <div style="font-size:12px; color:#888;">Lembaga, yayasan, atau komunitas</div>
                    </span>
                </a>

                <a href="/penggalang_dana/individu"
                    style="display:flex; align-items:center; gap:10px; padding:12px; border:1px solid #ddd; border-radius:6px; text-decoration:none; color:#000;">
                    <span style="font-size:20px;">👤</span>
                    <span>
                        <div style="font-weight:bold; font-size:14px;">Individu</div>
                        <div style="font-size:12px; color:#888;">Atas nama pribadi</div>
                    </span>
                </a>

                <div style="display:flex; justify-content:flex-end; margin-top:16px;">
                    <button type="button" onclick="document.getElementById('modalDaftar').style.display='none'"
                        style="background:#e5e7eb; border:none; padding:8px 16px; border-radius:6px; cursor:pointer;">Batal</button>
                </div>
            </div>
        </div>

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
                            <img class="campaign-image" src="{{ asset($campaign['image']) }}" alt="{{ $campaign['title'] }}"
                                loading="lazy">

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
                <img src="{{ asset('assets/sedekah.png') }}" alt="Sedekah Tidaklah Mengurangi Harta" loading="lazy">

                <img src="{{ asset('assets/sedekah.png') }}" alt="Sedekah Tidaklah Mengurangi Harta" loading="lazy">
            </div>
        </section>

        {{-- PEMBERDAYAAN --}}
        <section class="section-border">
            <div class="container section">
                <h2 class="section-title">Pemberdayaan Berkelanjutan</h2>

                <div class="campaign-grid">
                    @foreach ($campaigns as $campaign)
                        <article class="campaign-card">
                            <img class="campaign-image" src="{{ asset($campaign['image']) }}" alt="{{ $campaign['title'] }}"
                                loading="lazy">

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
                <h2 class="section-title">
                    Apa Kata Mereka?
                </h2>

                <div class="testimonial-content">

                    <button class="slider-btn" id="prevBtn" type="button">
                        ‹
                    </button>

                    <div class="testimonial-wrapper">

                        @foreach($testimoni as $item)
                            <div class="testimonial-item">

                                <p class="testimonial-description">
                                    "{{ $item->isi_testimoni }}"
                                </p>

                                <img src="{{ asset('storage/' . $item->foto_profil) }}" alt="{{ $item->nama }}"
                                    loading="lazy">

                                <h3>
                                    {{ $item->nama }}
                                </h3>

                                <span>
                                    {{ $item->jabatan }}
                                </span>

                            </div>
                        @endforeach

                    </div>

                    <button class="slider-btn" id="nextBtn" type="button">
                        ›
                    </button>

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
                        <img src="{{ asset('assets/yatim.jpg') }}" alt="Beasiswa Yatim Dhuafa" loading="lazy">

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
    <script>
        const slides = document.querySelectorAll('.testimonial-item');

        let current = 0;

        function showSlide(index) {

            slides.forEach(slide => {
                slide.classList.remove('active');
            });

            slides[index].classList.add('active');
        }

        document.getElementById('nextBtn')
            .addEventListener('click', () => {

                current++;

                if (current >= slides.length) {
                    current = 0;
                }

                showSlide(current);
            });

        document.getElementById('prevBtn')
            .addEventListener('click', () => {

                current--;

                if (current < 0) {
                    current = slides.length - 1;
                }

                showSlide(current);
            });

        setInterval(() => {

            current++;

            if (current >= slides.length) {
                current = 0;
            }

            showSlide(current);

        }, 5000);

        showSlide(0);
    </script>

</html>