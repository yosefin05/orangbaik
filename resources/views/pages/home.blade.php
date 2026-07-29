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

        {{-- HERO --}}
        <section class="hero-section">
            <div class="container hero-layout">

                <div class="hero-card hero-main-slider">
                    @foreach ($campaigns as $index => $campaign)
                        <div class="hero-slide {{ $index === 0 ? 'active' : '' }}">
                            <a href="{{ route('campaign.show', $campaign->custom_slug ?? $campaign->slug) }}">
                                <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}">
                            </a>
                        </div>
                    @endforeach

                    <div class="hero-dots"></div>
                </div>

                <div class="hero-side-slider">
                    @foreach ($berita as $index => $news)
                        <a href="{{ route('berita.show', $news->slug) }}"
                            class="hero-side-card {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ asset('storage/' . $news->thumbnail) }}" alt="{{ $news->judul }}">

                            <div class="hero-side-body">
                                <span>Berita</span>
                                <h3>{{ $news->judul }}</h3>
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
                    @foreach($kategori as $item)
                        @php
                            $icon = match (strtolower($item->nama_kategori)) {
                                'zakat' => 'assets/zakat.svg',
                                'wakaf' => 'assets/wakaf.svg',
                                'infaq' => 'assets/infaq.svg',
                                'kemanusiaan' => 'assets/kemanusiaan.svg',
                                'sedekah rutin' => 'assets/sedekah-rutin.svg',
                                default => 'assets/lainnya.svg',
                            };
                        @endphp

                        <a href="{{ route('donasi', ['kategori' => $item->id]) }}"
                            class="category-item {{ request('kategori') == $item->id ? 'active' : '' }}">

                            <div class="category-icon">
                                <img src="{{ asset($icon) }}" alt="{{ $item->nama_kategori }}">
                            </div>

                            <p>{{ $item->nama_kategori }}</p>
                        </a>
                    @endforeach

                    <a href="{{ route('donasi') }}"
                        class="category-item {{ request()->filled('kategori') ? '' : 'active' }}">

                        <div class="category-icon">
                            <img src="{{ asset('assets/lainnya.svg') }}" alt="Semua Campaign">
                        </div>

                        <p>Lainnya</p>
                    </a>
                </div>
            </div>
        </section>

        {{-- DARURAT --}}
        <section class="section section-border">
            <div class="container">

                <h2 class="section-title">
                    Darurat! Bantu Sekarang
                </h2>

                <div class="campaign-grid">
                    @forelse ($campaignDarurat as $campaign)
                        @php
                            $terkumpul = $campaign->donasi->sum('nominal');
                            $persen = $campaign->target_donasi
                                ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                : 0;

                            // ♻️ PERHITUNGAN HARI – PASTI INTEGER
                            $now = \Carbon\Carbon::now();
                            $end = \Carbon\Carbon::parse($campaign->tanggal_berakhir);
                            $diff = (int) $now->diffInDays($end, false);

                            if ($diff < 0) {
                                $labelHari = 'Selesai';
                            } elseif ($diff == 0) {
                                $labelHari = 'Hari terakhir';
                            } else {
                                $labelHari = $diff . ' Hari';
                            }
                        @endphp
                        <article class="campaign-card">
                            <a href="{{ route('campaign.show', $campaign->custom_slug ?? $campaign->slug) }}">
                                <img class="campaign-image" src="{{ asset('storage/' . $campaign->thumbnail) }}"
                                    alt="{{ $campaign->judul }}" loading="lazy">
                            </a>
                            <div class="campaign-body">
                                <h3>{{ $campaign->judul }}</h3>
                                <p>
                                    {{ $campaign->penggalangDana->nama_penggalang }}
                                    <span>●</span>
                                </p>
                                <div class="campaign-price">
                                    <strong>
                                        Rp {{ number_format($terkumpul, 0, ',', '.') }}
                                    </strong>
                                    <span>Terkumpul</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-fill" style="width: {{ $persen }}%;"></div>
                                </div>
                                <div class="campaign-meta">
                                    <span>{{ $campaign->donasi->count() }} Donatur</span>
                                    <span>{{ $labelHari }}</span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p>Belum ada campaign darurat.</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- YANG BARU --}}
        <section class="section">
            <div class="container">
                <h2 class="section-title">Yuk, Lihat yang Baru!</h2>
                <div class="new-grid">
                    @forelse ($campaignTerbaru as $campaign)
                        <a href="{{ route('campaign.show', $campaign->custom_slug ?? $campaign->slug) }}" class="donasi-new-item">
                            <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}"
                                loading="lazy">
                        </a>
                    @empty
                        <p>Belum ada campaign terbaru.</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- PEMBERDAYAAN --}}
        <section class="section section-border">
            <div class="container">
                <h2 class="section-title">Pemberdayaan Berkelanjutan</h2>

                <div class="campaign-grid">
                    @forelse ($campaignBerkelanjutan as $campaign)
                        @php
                            $terkumpul = $campaign->donasi->sum('nominal');
                            $persen = $campaign->target_donasi
                                ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                : 0;

                            // ♻️ SAMA PERSIS DENGAN DARURAT
                            $now = \Carbon\Carbon::now();
                            $end = \Carbon\Carbon::parse($campaign->tanggal_berakhir);
                            $diff = (int) $now->diffInDays($end, false);

                            if ($diff < 0) {
                                $labelHari = 'Selesai';
                            } elseif ($diff == 0) {
                                $labelHari = 'Hari terakhir';
                            } else {
                                $labelHari = $diff . ' Hari';
                            }
                        @endphp
                        <article class="campaign-card">
                            <a href="{{ route('campaign.show', $campaign->custom_slug ??$campaign->slug) }}">
                                <img class="campaign-image" src="{{ asset('storage/' . $campaign->thumbnail) }}"
                                    alt="{{ $campaign->judul }}" loading="lazy">
                            </a>
                            <div class="campaign-body">
                                <h3>{{ $campaign->judul }}</h3>
                                <p>
                                    {{ $campaign->penggalangDana->nama_penggalang }}
                                    <span>●</span>
                                </p>
                                <div class="campaign-price">
                                    <strong>
                                        Rp {{ number_format($terkumpul, 0, ',', '.') }}
                                    </strong>
                                    <span>Terkumpul</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-fill" style="width: {{ $persen }}%;"></div>
                                </div>
                                <div class="campaign-meta">
                                    <span>{{ $campaign->donasi->count() }} Donatur</span>
                                    <span>{{ $labelHari }}</span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p>Belum ada campaign berkelanjutan.</p>
                    @endforelse
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
                                <img src="{{ asset('storage/' . $item->foto_profil) }}" alt="{{ $item->nama }}" loading="lazy">
                            @else
                                <img src="{{ asset('assets/logo.png') }}" alt="{{ $item->nama }}" loading="lazy">
                            @endif

                            <h3>{{ $item->nama }}</h3>
                            <span>{{ $item->jabatan }}</span>
                        </div>
                    @empty
                        <div class="testimonial-item active">
                            <p class="testimonial-description">
                                "OrangBaik.id memudahkan kami untuk ikut berbagi dan mendukung program kebaikan."
                            </p>

                            <img src="{{ asset('assets/logo.png') }}" alt="OrangBaik.id" loading="lazy">

                            <h3>OrangBaik.id</h3>
                            <span>Platform Donasi</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- KATEGORI FAVORIT --}}
        <section class="section" id="kategori-favorit">
            <div class="container">
                <h2 class="section-title">Pilih Kategori Favoritmu</h2>

                <div class="category-grid category-grid-small">
                    @foreach($kategori as $item)
                        @php
                            $icon = match (strtolower($item->nama_kategori)) {
                                'zakat' => 'assets/zakat.svg',
                                'wakaf' => 'assets/wakaf.svg',
                                'infaq' => 'assets/infaq.svg',
                                'kemanusiaan' => 'assets/kemanusiaan.svg',
                                'sedekah rutin' => 'assets/sedekah-rutin.svg',
                                default => 'assets/lainnya.svg',
                            };
                        @endphp

                        <a href="{{ route('home', ['kategori' => $item->id]) }} #kategori-favorit"
                            class="category-item {{ request('kategori') == $item->id ? 'active' : '' }}">

                            <div class="category-icon">
                                <img src="{{ asset($icon) }}" alt="{{ $item->nama_kategori }}">
                            </div>

                            <p>{{ $item->nama_kategori }}</p>
                        </a>
                    @endforeach

                    <a href="{{ route('home') }}#kategori-favorit"
                        class="category-item {{ request()->filled('kategori') ? '' : 'active' }}">

                        <div class="category-icon">
                            <img src="{{ asset('assets/lainnya.svg') }}" alt="Semua Campaign">
                        </div>

                        <p>Lainnya</p>
                    </a>
                </div>

                <div class="list-campaign">
                    @forelse ($campaigns as $campaign)
                        @php
                            $terkumpul = $campaign->donasi->sum('nominal');
                            $persen = $campaign->target_donasi
                                ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                : 0;

                            // ♻️ SAMA LAGI
                            $now = \Carbon\Carbon::now();
                            $end = \Carbon\Carbon::parse($campaign->tanggal_berakhir);
                            $diff = (int) $now->diffInDays($end, false);

                            if ($diff < 0) {
                                $labelHari = 'Selesai';
                            } elseif ($diff == 0) {
                                $labelHari = 'Hari terakhir';
                            } else {
                                $labelHari = $diff . ' Hari';
                            }
                        @endphp

                        <article class="list-card">
                            <a href="{{ route('campaign.show', $campaign->custom_slug ?? $campaign->slug) }}">
                                <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}"
                                    loading="lazy">
                            </a>

                            <div class="list-body">
                                <h3>{{ $campaign->judul }}</h3>

                                <p>
                                    {{ $campaign->penggalangDana->nama_penggalang }}
                                    <span>●</span>
                                </p>

                                <strong>Rp {{ number_format($terkumpul, 0, ',', '.') }}</strong>

                                <div class="progress">
                                    <div class="progress-fill" style="width: {{ $persen }}%;"></div>
                                </div>

                                <div class="campaign-meta">
                                    <span>{{ $campaign->donasi->count() }} Donatur</span>
                                    <span>{{ $labelHari }}</span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p>Belum ada campaign aktif.</p>
                    @endforelse
                </div>
            </div>
        </section>

    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/home.js') }}"></script>

</body>

</html>