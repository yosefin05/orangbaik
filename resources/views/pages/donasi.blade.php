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

    <main class="donasi-page page-wrapper">

        <section class="donasi-section">
            <div class="container">

                <h1 class="donasi-title">
                    Yuk, Berbuat Baik Hari Ini!
                </h1>

                <div class="donasi-category-grid">
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
                            class="donasi-category-item {{ request('kategori') == $item->id ? 'active' : '' }}">
                            <div class="donasi-category-icon">
                                <img src="{{ asset($icon) }}" alt="{{ $item->nama_kategori }}">
                            </div>
                            <p>{{ $item->nama_kategori }}</p>
                        </a>
                    @endforeach
                    {{-- Semua / Lainnya --}}
                    <a href="{{ route('donasi') }}"
                        class="donasi-category-item {{ request()->filled('kategori') ? '' : 'active' }}">

                        <div class="donasi-category-icon">
                            <img src="{{ asset('assets/lainnya.svg') }}" alt="Semua Campaign">
                        </div>

                        <p>Lainnya</p>

                    </a>
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
                            @php
                                $terkumpul = $campaign->donasi->sum('nominal');
                                $persen = $campaign->target_donasi
                                    ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                    : 0;
                                $hari =
                                    max(
                                        0,
                                        (int) now()->diffInDays($campaign->tanggal_berakhir, false)
                                    );
                            @endphp
                            <article class="donasi-card">
                                <a href=" {{ route('campaign.show', $campaign->slug) }}">
                                    <img class="donasi-card-image" src="{{ asset('storage/' . $campaign->thumbnail) }}"
                                        alt="{{ $campaign->judul }}">
                                    <div class="donasi-card-body">
                                        <h3>{{ $campaign->judul }}</h3>
                                        <p class="donasi-organizer">
                                            {{ $campaign->penggalangDana->nama_penggalang }}
                                            <span>●</span>
                                        </p>
                                        <div class="donasi-amount">
                                            <strong>
                                                Rp {{ number_format($terkumpul, 0, ',', '.') }}
                                            </strong>
                                            <span>Terkumpul</span>
                                        </div>
                                        <div class="donasi-progress">
                                            <div class="donasi-progress-fill" style="width:{{ $persen }}%"></div>
                                        </div>
                                        <div class="donasi-meta">
                                            <span>
                                                {{ $campaign->donasi->count() }} Donatur
                                            </span>
                                            <span>
                                                {{ $hari }} Hari
                                            </span>
                                        </div>
                                    </div>
                                </a>
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
                    @forelse ($campaignTerbaru as $campaign)
                        <a href="{{ route('campaign.show', ['slug' => $campaign->slug]) }}" class="donasi-new-item">
                            <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}"
                                loading="lazy">
                        </a>
                    @empty
                        <p>Belum ada campaign terbaru.</p>
                    @endforelse
                </div>
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
                        <a href="{{ route('campaign.show', $campaign->slug) }}" class="donasi-card"> <img
                                class="donasi-card-image" src="{{ asset($campaign['image']) }}"
                                alt="{{ $campaign['title'] }}" loading="lazy">

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
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('components.footer')
    <script src="{{ asset('js/header.js') }}"></script>

</body>

</html>