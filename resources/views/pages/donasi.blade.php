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

    {{-- FILTER SECTION --}}
    <div class="donasi-filter-section">
        <div class="container">
            <form action="{{ route('donasi') }}" method="GET" id="filterForm" class="donasi-filter-form">
                <div class="filter-wrap">
                    <div class="filter-top">
                        <div class="filter-left">
                            <span class="filter-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="4" y1="6" x2="20" y2="6" />
                                    <line x1="8" y1="12" x2="16" y2="12" />
                                    <line x1="11" y1="18" x2="13" y2="18" />
                                </svg>
                            </span>
                            <span class="filter-label">Filter</span>
                        </div>
                        <div class="filter-right">
                            <div class="filter-select-wrap">
                                <select name="jenis" id="jenis" onchange="this.form.submit()">
                                    <option value="">Semua Penggalang</option>
                                    <option value="individu" {{ request('jenis') == 'individu' ? 'selected' : '' }}>
                                        Individu</option>
                                    <option value="organisasi" {{ request('jenis') == 'organisasi' ? 'selected' : '' }}>
                                        Organisasi</option>
                                </select>
                                <svg class="select-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </div>
                            {{-- Filter Nama Penggalang Dana --}}
                            <div class="filter-select-wrap">
                                <select name="penggalang_id" id="penggalang_id" onchange="this.form.submit()">
                                    <option value="">Semua Penggalang Dana</option>
                                    @foreach ($penggalangDana as $penggalang)
                                        <option value="{{ $penggalang->id }}" {{ request('penggalang_id') == $penggalang->id ? 'selected' : '' }}>
                                            {{ $penggalang->nama_penggalang }}
                                        </option>
                                    @endforeach
                                </select>
                                <svg class="select-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </div>
                            <div class="filter-chips">
                                @foreach ($filters as $filter)
                                    @php $isChecked = in_array($filter->id, (array) $selectedFilterIds); @endphp
                                    <label class="filter-chip {{ $isChecked ? 'active' : '' }}">
                                        <input type="checkbox" name="filter_ids[]" value="{{ $filter->id }}" {{ $isChecked ? 'checked' : '' }} onchange="this.form.submit()">
                                        {{ $filter->nama_filter }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @if(request()->hasAny(['jenis', 'filter_ids', 'kategori']))
                        <a href="{{ route('donasi') }}" class="filter-reset">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <main class="donasi-page">
        {{-- KATEGORI --}}
        <section class="donasi-section">
            <div class="container">

                <h1 class="donasi-title">
                    Yuk, Berbuat Baik Hari Ini!
                </h1>

                <div class="donasi-category-grid">
                    @foreach ($kategori as $item)
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

        {{-- DARURAT --}}
        <section class="donasi-border donasi-section">
            <div class="container">

                <h2 class="donasi-section-title">
                    Darurat! Bantu Sekarang
                </h2>

                <div class="campaign-carousel">
                    <div class="donasi-card-grid">

                        @forelse ($darurat as $campaign)
                            @php
                                // HANYA donasi dengan status SETTLEMENT
                                $donasiSettlement = $campaign->donasi->filter(function ($donasi) {
                                    return $donasi->pembayaran && $donasi->pembayaran->transaction_status === 'settlement';
                                });
                                $terkumpul = $donasiSettlement->sum('nominal');
                                $totalDonatur = $donasiSettlement->count();
                                $persen = $campaign->target_donasi
                                    ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                    : 0;
                                $hari = max(
                                    0,
                                    (int) now()->diffInDays($campaign->tanggal_berakhir, false)
                                );
                            @endphp

                            <article class="donasi-card">
                                <a href="{{ route('campaign.show', $campaign->slug) }}">
                                    <img class="donasi-card-image" src="{{ asset('storage/' . $campaign->thumbnail) }}"
                                        alt="{{ $campaign->judul }}" loading="lazy">

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
                                            <div class="donasi-progress-fill" style="width: {{ $persen }}%;">
                                            </div>
                                        </div>

                                        <div class="donasi-meta">
                                            <span>{{ $totalDonatur }} Donatur</span>
                                            <span>{{ $hari }} Hari</span>
                                        </div>
                                    </div>
                                </a>
                            </article>

                        @empty
                            <p>Belum ada campaign darurat.</p>
                        @endforelse

                    </div>
                </div>

            </div>
        </section>

        {{-- TERBARU --}}
        <section class="donasi-border donasi-section">
            <div class="container">

                <h2 class="donasi-section-title">
                    Yuk, Lihat yang Baru!
                </h2>

                <div class="donasi-new-grid">

                    @forelse ($campaignTerbaru as $campaign)
                        @php
                            // HANYA donasi dengan status SETTLEMENT
                            $donasiSettlement = $campaign->donasi->filter(function ($donasi) {
                                return $donasi->pembayaran && $donasi->pembayaran->transaction_status === 'settlement';
                            });
                            $terkumpul = $donasiSettlement->sum('nominal');
                            $totalDonatur = $donasiSettlement->count();
                            $persen = $campaign->target_donasi
                                ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                : 0;
                        @endphp
                        <a href="{{ route('campaign.show', ['slug' => $campaign->slug]) }}" class="donasi-new-item">
                            <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}"
                                loading="lazy">
                            <div class="donasi-new-overlay">
                                <h3>{{ $campaign->judul }}</h3>
                                <p>{{ $campaign->penggalangDana->nama_penggalang }}</p>
                                <div class="donasi-new-price">
                                    <strong>Rp {{ number_format($terkumpul, 0, ',', '.') }}</strong>
                                    <span>Terkumpul</span>
                                </div>
                                <div class="donasi-progress" style="background:rgba(255,255,255,.25);">
                                    <div class="donasi-progress-fill" style="width: {{ $persen }}%;"></div>
                                </div>
                                <div class="donasi-new-meta">
                                    <span>{{ $totalDonatur }} Donatur</span>
                                    <span>{{ round($persen) }}%</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <p>Belum ada campaign terbaru.</p>
                    @endforelse

                </div>

            </div>
        </section>

        {{-- PEMBERDAYAAN BERKELANJUTAN --}}
        <section class="donasi-border donasi-section">
            <div class="container">

                <h2 class="donasi-section-title">
                    Pemberdayaan Berkelanjutan
                </h2>

                <div class="campaign-carousel">
                    <div class="donasi-card-grid">

                        @forelse ($pemberdayaan as $campaign)
                            @php
                                // HANYA donasi dengan status SETTLEMENT
                                $donasiSettlement = $campaign->donasi->filter(function ($donasi) {
                                    return $donasi->pembayaran && $donasi->pembayaran->transaction_status === 'settlement';
                                });
                                $terkumpul = $donasiSettlement->sum('nominal');
                                $totalDonatur = $donasiSettlement->count();
                                $persen = $campaign->target_donasi
                                    ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                    : 0;
                                $hari = max(
                                    0,
                                    (int) now()->diffInDays($campaign->tanggal_berakhir, false)
                                );
                            @endphp

                            <article class="donasi-card">
                                <a href="{{ route('campaign.show', $campaign->slug) }}">
                                    <img class="donasi-card-image" src="{{ asset('storage/' . $campaign->thumbnail) }}"
                                        alt="{{ $campaign->judul }}" loading="lazy">

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
                                            <div class="donasi-progress-fill" style="width: {{ $persen }}%;">
                                            </div>
                                        </div>

                                        <div class="donasi-meta">
                                            <span>{{ $totalDonatur }} Donatur</span>
                                            <span>{{ $hari }} Hari</span>
                                        </div>
                                    </div>
                                </a>
                            </article>

                        @empty
                            <p>Belum ada campaign lainnya.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="donasi-border donasi-section">
            <div class="container">

                <h2 class="donasi-section-title">
                    Campaign Lainnya
                </h2>

                <div class="campaign-carousel">
                    <div class="donasi-card-grid">

                        @forelse ($campaigns as $campaign)
                            @php
                                // HANYA donasi dengan status SETTLEMENT
                                $donasiSettlement = $campaign->donasi->filter(function ($donasi) {
                                    return $donasi->pembayaran && $donasi->pembayaran->transaction_status === 'settlement';
                                });
                                $terkumpul = $donasiSettlement->sum('nominal');
                                $totalDonatur = $donasiSettlement->count();
                                $persen = $campaign->target_donasi
                                    ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                    : 0;
                                $hari = max(
                                    0,
                                    (int) now()->diffInDays($campaign->tanggal_berakhir, false)
                                );
                            @endphp

                            <article class="donasi-card">
                                <a href="{{ route('campaign.show', $campaign->slug) }}">
                                    <img class="donasi-card-image" src="{{ asset('storage/' . $campaign->thumbnail) }}"
                                        alt="{{ $campaign->judul }}" loading="lazy">

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
                                            <div class="donasi-progress-fill" style="width: {{ $persen }}%;">
                                            </div>
                                        </div>

                                        <div class="donasi-meta">
                                            <span>{{ $totalDonatur }} Donatur</span>
                                            <span>{{ $hari }} Hari</span>
                                        </div>
                                    </div>
                                </a>
                            </article>

                        @empty
                            <p>Belum ada campaign darurat.</p>
                        @endforelse

                    </div>
                </div>

            </div>
        </section>
    </main>

    <!-- FLOATING WHATSAPP BUTTON -->
    @if(env('ENABLE_WA_FLOATING', true))
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

</body>

</html>