<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Orang Baik</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @php
        // Hanya untuk tampilan: icon kategori, statistik donasi (status SETTLEMENT), testimoni
        $iconKategori = fn($nama) => 'assets/' . match (strtolower($nama)) {
            'zakat' => 'zakat',
            'wakaf' => 'wakaf',
            'infaq' => 'infaq',
            'kemanusiaan' => 'kemanusiaan',
            'sedekah rutin' => 'sedekah-rutin',
            default => 'lainnya',
        } . '.svg';

        $statistik = function ($campaign) {
            $settlement = $campaign->donasi->filter(
                fn($donasi) => $donasi->pembayaran && $donasi->pembayaran->transaction_status === 'settlement'
            );
            $terkumpul = $settlement->sum('nominal');
            $persen = $campaign->target_donasi ? min(100, ($terkumpul / $campaign->target_donasi) * 100) : 0;

            return [$terkumpul, $settlement->count(), $persen];
        };

        $testimoni = $testimoni ?? collect();
    @endphp

    @include('components.header')

    <main class="main">

        {{-- HERO --}}
        <section class="hero-section">
            <div class="container hero-layout">
                <div class="hero-card hero-main-slider">
                    @foreach ($heroCampaigns as $index => $campaign)
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
                        <a href="{{ route('berita.show', $news->custom_slug ?? $news->slug) }}"
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

        {{-- KATEGORI --}}
        <section class="section">
            <div class="container">
                <h2 class="section-title">Yuk, Berbuat Baik Hari Ini!</h2>

                <div class="category-grid">
                    @foreach ($kategori as $item)
                        <a href="{{ route('donasi', ['kategori' => $item->id]) }}"
                            class="category-item {{ request('kategori') == $item->id ? 'active' : '' }}">
                            <div class="category-icon">
                                <img src="{{ asset($iconKategori($item->nama_kategori)) }}"
                                    alt="{{ $item->nama_kategori }}">
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
        <section class="section section-border tone-danger">
            <div class="container">
                <h2 class="section-title">Darurat! Bantu Sekarang</h2>

                @if (count($darurat) > 0)

                    <div class="campaign-grid">
                        @foreach ($darurat as $campaign)
                            <x-campaign-card :campaign="$campaign" />
                        @endforeach
                    </div>

                @else

                    <div class="empty-state">
                        <span class="empty-state-icon" aria-hidden="true">
                            <i class="bi bi-lightning-charge"></i>
                        </span>

                        <h3 class="empty-state-title">Belum ada campaign darurat</h3>

                        <p class="empty-state-text">
                            Saat ini belum ada penggalangan yang bersifat mendesak.
                        </p>
                    </div>

                @endif
            </div>
        </section>

        {{-- YANG BARU --}}
        <section class="section section-new tone-secondary">
            <div class="container">
                <h2 class="section-title new-title">Yuk, Lihat yang Baru!</h2>

                @if (count($campaignTerbaru) > 0)

                    <div class="new-grid">
                        @foreach ($campaignTerbaru as $campaign)
                            @php [$terkumpul, $totalDonatur, $persen] = $statistik($campaign); @endphp

                            <a href="{{ route('campaign.show', $campaign->custom_slug ?? $campaign->slug) }}" class="new-item">
                                <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}"
                                    loading="lazy">

                                <div class="new-item-overlay">
                                    <h3>{{ $campaign->judul }}</h3>
                                    <p>{{ $campaign->penggalangDana->nama_penggalang }}</p>

                                    <div class="new-item-price">
                                        <strong>Rp {{ number_format($terkumpul, 0, ',', '.') }}</strong>
                                        <span>Terkumpul</span>
                                    </div>

                                    <div class="progress">
                                        <div class="progress-fill" style="width: {{ $persen }}%;"></div>
                                    </div>

                                    <div class="new-item-meta">
                                        <span>{{ $totalDonatur }} Donatur</span>
                                        <span>{{ round($persen) }}%</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                @else

                    <div class="empty-state">
                        <span class="empty-state-icon" aria-hidden="true">
                            <i class="bi bi-clock-history"></i>
                        </span>

                        <h3 class="empty-state-title">Belum ada campaign terbaru</h3>

                        <p class="empty-state-text">
                            Campaign yang baru dibuat akan muncul di sini.
                        </p>
                    </div>

                @endif
            </div>
        </section>

        {{-- PEMBERDAYAAN --}}
        <section class="section section-border tone-success">
            <div class="container">
                <h2 class="section-title">Pemberdayaan Berkelanjutan</h2>

                @if (count($pemberdayaan) > 0)

                    <div class="campaign-grid">
                        @foreach ($pemberdayaan as $campaign)
                            <x-campaign-card :campaign="$campaign" />
                        @endforeach
                    </div>

                @else

                    <div class="empty-state">
                        <span class="empty-state-icon" aria-hidden="true">
                            <i class="bi bi-graph-up"></i>
                        </span>

                        <h3 class="empty-state-title">Belum ada program pemberdayaan</h3>

                        <p class="empty-state-text">
                            Program pemberdayaan berkelanjutan akan tampil di sini.
                        </p>
                    </div>

                @endif
            </div>
        </section>

        {{-- CAMPAIGN LAINNYA --}}
        <section class="section section-border tone-primary">
            <div class="container">
                <h2 class="section-title">Campaign Lainnya</h2>

                @if (count($campaigns) > 0)

                    <div class="campaign-grid">
                        @foreach ($campaigns as $campaign)
                            <x-campaign-card :campaign="$campaign" />
                        @endforeach
                    </div>

                @else

                    <div class="empty-state">
                        <span class="empty-state-icon" aria-hidden="true">
                            <i class="bi bi-collection"></i>
                        </span>

                        <h3 class="empty-state-title">Belum ada campaign lainnya</h3>

                        <p class="empty-state-text">
                            Campaign lain akan muncul di sini.
                        </p>
                    </div>

                @endif
            </div>
        </section>

        {{-- TESTIMONIAL --}}
        <section class="section testimonial">
            <div class="container">
                <h2 class="section-title">Apa Kata Mereka?</h2>

                <div class="testimonial-wrapper">
                    @forelse ($testimoni as $item)
                        <div class="testimonial-item {{ $loop->first ? 'is-active' : ($loop->index === 1 ? 'is-next' : 'is-hidden') }}"
                            data-index="{{ $loop->index }}">
                            <div class="testimonial-card">
                                <div class="testimonial-head">
                                    <img class="testimonial-avatar"
                                        src="{{ $item->foto_profil ? asset('storage/' . $item->foto_profil) : asset('assets/logo.png') }}"
                                        alt="{{ $item->nama }}" loading="lazy">

                                    <div class="testimonial-person-info">
                                        <h3>{{ $item->nama }}</h3>
                                        <span>{{ $item->jabatan }}</span>
                                    </div>
                                </div>

                                <p class="testimonial-description">{{ $item->isi_testimoni }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="testimonial-item is-active" data-index="0">
                            <div class="testimonial-card">
                                <div class="testimonial-head">
                                    <img class="testimonial-avatar" src="{{ asset('assets/logo.png') }}" alt="OrangBaik.id"
                                        loading="lazy">

                                    <div class="testimonial-person-info">
                                        <h3>OrangBaik.id</h3>
                                        <span>Platform Donasi</span>
                                    </div>
                                </div>

                                <p class="testimonial-description">
                                    OrangBaik.id memudahkan kami untuk ikut berbagi dan mendukung program kebaikan.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if ($testimoni->count() > 1)
                    <div class="testimonial-nav">
                        <button type="button" class="testimonial-arrow" data-dir="prev"
                            aria-label="Testimoni sebelumnya">‹</button>

                        <div class="testimonial-dots">
                            @foreach ($testimoni as $item)
                                <button type="button" class="testimonial-dot {{ $loop->first ? 'active' : '' }}"
                                    data-index="{{ $loop->index }}"
                                    aria-label="Lihat testimoni {{ $loop->iteration }}"></button>
                            @endforeach
                        </div>

                        <button type="button" class="testimonial-arrow" data-dir="next"
                            aria-label="Testimoni berikutnya">›</button>
                    </div>
                @endif
            </div>
        </section>

        {{-- KATEGORI FAVORIT --}}
        <section class="section tone-primary" id="kategori-favorit">
            <div class="container">
                <h2 class="section-title">Pilih Kategori Favoritmu</h2>

                <div class="category-grid category-grid-small">
                    @foreach ($kategori as $item)
                        <a href="{{ route('home', ['kategori' => $item->id]) }}#kategori-favorit"
                            class="category-item {{ request('kategori') == $item->id ? 'active' : '' }}">
                            <div class="category-icon">
                                <img src="{{ asset($iconKategori($item->nama_kategori)) }}"
                                    alt="{{ $item->nama_kategori }}">
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

                @if (count($campaigns) > 0)

                    <div class="list-campaign">
                        @foreach ($campaigns as $campaign)
                            <x-campaign-card :campaign="$campaign" variant="list" />
                        @endforeach
                    </div>

                @else

                    <div class="empty-state">
                        <span class="empty-state-icon" aria-hidden="true">
                            <i class="bi bi-collection"></i>
                        </span>

                        <h3 class="empty-state-title">Belum ada campaign aktif</h3>

                        <p class="empty-state-text">
                            {{ request()->filled('kategori')
                                ? 'Belum ada campaign pada kategori ini.'
                                : 'Campaign yang sedang berjalan akan muncul di sini.' }}
                        </p>
                    </div>

                @endif
            </div>
        </section>

    </main>

    {{-- FLOATING WHATSAPP BUTTON --}}
    @if (env('ENABLE_WA_FLOATING', true))
        <div class="floating-wa-container">
            <a href="https://wa.me/{{ env('WHATSAPP_NUMBER', '6281385002300') }}?text={{ urlencode(env('WHATSAPP_MESSAGE', 'Halo tim OrangBaik.id, saya mau bertanya mengenai...')) }}"
                target="_blank" rel="noopener noreferrer" class="floating-wa-btn"
                aria-label="Hubungi Customer Service via WhatsApp">
                <div class="wa-icon-wrapper">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <span class="wa-tooltip">Hubungi CS</span>
            </a>
        </div>
    @endif

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/home.js') }}"></script>

    {{-- Testimonial carousel --}}
    <script>
        (() => {
            const wrapper = document.querySelector('.testimonial-wrapper');
            if (!wrapper) return;

            const items = [...wrapper.querySelectorAll('.testimonial-item')];
            const dots = [...document.querySelectorAll('.testimonial-dot')];
            const arrows = [...document.querySelectorAll('.testimonial-arrow')];
            const total = items.length;
            if (total <= 1) return;

            const states = { '0': 'is-active', '-1': 'is-prev', '1': 'is-next' };
            let current = 0;
            let timer = null;
            let startX = 0;

            // jarak item ke slide aktif, melingkar (infinite loop)
            const offsetOf = (i) => {
                let d = (i - current) % total;
                if (d > total / 2) d -= total;
                if (d < -total / 2) d += total;
                return d;
            };

            const render = () => {
                items.forEach((item, i) => {
                    item.classList.remove('is-active', 'is-prev', 'is-next', 'is-hidden');
                    item.classList.add(states[offsetOf(i)] ?? 'is-hidden');
                });
                dots.forEach((dot, i) => dot.classList.toggle('active', i === current));
            };

            const goTo = (i) => {
                current = (i + total) % total;
                render();
            };

            const startAuto = () => {
                clearInterval(timer);
                timer = setInterval(() => goTo(current + 1), 5000);
            };

            const stopAuto = () => clearInterval(timer);

            const goByUser = (i) => {
                goTo(i);
                startAuto();
            };

            dots.forEach((dot) => dot.addEventListener('click', () => goByUser(+dot.dataset.index)));
            arrows.forEach((btn) =>
                btn.addEventListener('click', () => goByUser(current + (btn.dataset.dir === 'next' ? 1 : -1)))
            );

            // pause saat hover, geser (swipe) di layar sentuh
            wrapper.addEventListener('mouseenter', stopAuto);
            wrapper.addEventListener('mouseleave', startAuto);

            wrapper.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                stopAuto();
            }, { passive: true });

            wrapper.addEventListener('touchend', (e) => {
                const diffX = startX - e.changedTouches[0].clientX;
                if (Math.abs(diffX) > 40) goTo(current + (diffX > 0 ? 1 : -1));
                startAuto();
            }, { passive: true });

            render();
            startAuto();
        })();
    </script>

</body>

</html>