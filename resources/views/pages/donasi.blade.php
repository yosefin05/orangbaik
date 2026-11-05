@php
    // Kategori yang sedang dipilih (dari ?kategori=...).
    // Cocokkan ke slug ATAU id; jika tidak ditemukan dianggap tidak ada kategori.
    $kategoriDipilih = (string) request('kategori');

    $kategoriAktif = $kategoriDipilih !== ''
        ? $kategori->first(
            fn ($item) => (string) $item->slug === $kategoriDipilih
                || (string) $item->id === $kategoriDipilih
        )
        : null;

    // Ikon kategori (dipakai grid kategori & panel filter)
    $kategoriIcons = [
        'zakat' => 'assets/zakat.svg',
        'wakaf' => 'assets/wakaf.svg',
        'infaq' => 'assets/infaq.svg',
        'kemanusiaan' => 'assets/kemanusiaan.svg',
        'sedekah rutin' => 'assets/sedekah-rutin.svg',
    ];

    // Status filter aktif (untuk tombol & badge di bar filter)
    $jenisAktif = in_array(request('jenis'), ['individu', 'organisasi'], true)
        ? request('jenis')
        : null;

    $jumlahFilter = count($selectedFilterIds ?? []);

    $adaFilter = $kategoriAktif
        || $jenisAktif
        || $jumlahFilter > 0
        || request()->filled('penggalang_id');

    $jenisOptions = [
        '' => ['Semua jenis', 'bi-people'],
        'individu' => ['Individu', 'bi-person'],
        'organisasi' => ['Organisasi', 'bi-building'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $kategoriAktif?->nama_kategori ?? 'Donasi' }} - OrangBaik</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>

<body>

    {{-- headerTitle: bila terisi, header (mobile) menampilkan nama kategori
         menggantikan search bar --}}
@include('components.header', [
    'headerTitle' => $kategoriAktif?->nama_kategori,
    'headerIcon'  => $kategoriAktif
        ? ($kategoriIcons[strtolower($kategoriAktif->nama_kategori)] ?? 'assets/lainnya.svg')
        : null,
])
    <main class="main donasi-page">

        {{-- ==========================================
            1. FILTER DONASI
            Bar: Kategori (pilih satu) • Jenis penggalang (pilih satu)
                 • Filter (checklist)
            Desktop: dropdown di bawah tombol. Mobile: bottom sheet.
            Semua input satu <form>; "Terapkan" mengirim semuanya.
        =========================================== --}}
        <section class="donasi-filter-section" id="donasiFilter">
            <div class="container">

                <form
                    action="{{ route('donasi') }}"
                    method="GET"
                    id="filterForm"
                    class="donasi-filter-form"
                >

                    {{-- pertahankan filter penggalang bila datang dari profil penggalang --}}
                    @if (request()->filled('penggalang_id'))
                        <input type="hidden" name="penggalang_id" value="{{ request('penggalang_id') }}">
                    @endif

                    <div class="filter-bar">

                        {{-- ---------- KATEGORI (pilih satu) ---------- --}}
                        <div class="filter-item" data-filter-item>

                            <button
                                type="button"
                                class="filter-trigger {{ $kategoriAktif ? 'is-active' : '' }}"
                                data-filter-trigger
                                aria-haspopup="dialog"
                                aria-expanded="false"
                                aria-controls="filterPanelKategori"
                            >
                                <i class="bi bi-grid filter-trigger-icon"></i>
                                <span class="filter-trigger-text">
                                    {{ $kategoriAktif?->nama_kategori ?? 'Kategori' }}
                                </span>
                                <i class="bi bi-chevron-down filter-trigger-caret"></i>
                            </button>

                            <div
                                class="filter-panel"
                                id="filterPanelKategori"
                                role="dialog"
                                aria-modal="true"
                                aria-labelledby="filterTitleKategori"
                                aria-hidden="true"
                                tabindex="-1"
                            >
                                <div class="filter-panel-head">
                                    <h2 id="filterTitleKategori">Pilih kategori</h2>

                                    <button type="button" class="filter-close" data-filter-close aria-label="Tutup">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div class="filter-panel-body">

                                    <label class="filter-option">
                                        <input
                                            type="radio"
                                            name="kategori"
                                            value=""
                                            {{ $kategoriAktif ? '' : 'checked' }}
                                        >
                                        <span class="filter-option-main">
                                            <img class="filter-option-icon" src="{{ asset('assets/lainnya.svg') }}" alt="">
                                            <span>Semua kategori</span>
                                        </span>
                                        <span class="filter-option-mark" aria-hidden="true"></span>
                                    </label>

                                    @foreach ($kategori as $item)
                                        <label class="filter-option">
                                            <input
                                                type="radio"
                                                name="kategori"
                                                value="{{ $item->slug }}"
                                                {{ $kategoriAktif && $kategoriAktif->id == $item->id ? 'checked' : '' }}
                                            >
                                            <span class="filter-option-main">
                                                <img
                                                    class="filter-option-icon"
                                                    src="{{ asset($kategoriIcons[strtolower($item->nama_kategori)] ?? 'assets/lainnya.svg') }}"
                                                    alt=""
                                                >
                                                <span>{{ $item->nama_kategori }}</span>
                                            </span>
                                            <span class="filter-option-mark" aria-hidden="true"></span>
                                        </label>
                                    @endforeach

                                </div>

                                <div class="filter-panel-foot">
                                    <button type="button" class="btn btn-outline btn-sm" data-filter-clear>Atur ulang</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                                </div>
                            </div>

                        </div>

                        {{-- ---------- JENIS PENGGALANG (pilih satu) ---------- --}}
                        <div class="filter-item" data-filter-item>

                            <button
                                type="button"
                                class="filter-trigger {{ $jenisAktif ? 'is-active' : '' }}"
                                data-filter-trigger
                                aria-haspopup="dialog"
                                aria-expanded="false"
                                aria-controls="filterPanelJenis"
                            >
                                <i class="bi bi-people filter-trigger-icon"></i>
                                <span class="filter-trigger-text">
                                    {{ $jenisAktif ? ucfirst($jenisAktif) : 'Jenis' }}
                                </span>
                                <i class="bi bi-chevron-down filter-trigger-caret"></i>
                            </button>

                            <div
                                class="filter-panel"
                                id="filterPanelJenis"
                                role="dialog"
                                aria-modal="true"
                                aria-labelledby="filterTitleJenis"
                                aria-hidden="true"
                                tabindex="-1"
                            >
                                <div class="filter-panel-head">
                                    <h2 id="filterTitleJenis">Jenis penggalang</h2>

                                    <button type="button" class="filter-close" data-filter-close aria-label="Tutup">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div class="filter-panel-body">

                                    @foreach ($jenisOptions as $value => [$label, $icon])
                                        <label class="filter-option">
                                            <input
                                                type="radio"
                                                name="jenis"
                                                value="{{ $value }}"
                                                {{ (string) $jenisAktif === (string) $value ? 'checked' : '' }}
                                            >
                                            <span class="filter-option-main">
                                                <span class="filter-option-glyph">
                                                    <i class="bi {{ $icon }}"></i>
                                                </span>
                                                <span>{{ $label }}</span>
                                            </span>
                                            <span class="filter-option-mark" aria-hidden="true"></span>
                                        </label>
                                    @endforeach

                                </div>

                                <div class="filter-panel-foot">
                                    <button type="button" class="btn btn-outline btn-sm" data-filter-clear>Atur ulang</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                                </div>
                            </div>

                        </div>

                        {{-- ---------- FILTER (checklist) ---------- --}}
                        <div class="filter-item" data-filter-item>

                            <button
                                type="button"
                                class="filter-trigger {{ $jumlahFilter > 0 ? 'is-active' : '' }}"
                                data-filter-trigger
                                aria-haspopup="dialog"
                                aria-expanded="false"
                                aria-controls="filterPanelFilter"
                            >
                                <i class="bi bi-funnel filter-trigger-icon"></i>
                                <span class="filter-trigger-text">Filter</span>

                                @if ($jumlahFilter > 0)
                                    <span class="filter-trigger-count">{{ $jumlahFilter }}</span>
                                @endif

                                <i class="bi bi-chevron-down filter-trigger-caret"></i>
                            </button>

                            <div
                                class="filter-panel"
                                id="filterPanelFilter"
                                role="dialog"
                                aria-modal="true"
                                aria-labelledby="filterTitleFilter"
                                aria-hidden="true"
                                tabindex="-1"
                            >
                                <div class="filter-panel-head">
                                    <h2 id="filterTitleFilter">Filter campaign</h2>

                                    <button type="button" class="filter-close" data-filter-close aria-label="Tutup">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <div class="filter-panel-body">

                                    @forelse ($filters as $filter)
                                        <label class="filter-option">
                                            <input
                                                type="checkbox"
                                                name="filter_ids[]"
                                                value="{{ $filter->id }}"
                                                {{ in_array($filter->id, $selectedFilterIds) ? 'checked' : '' }}
                                            >
                                            <span class="filter-option-main">
                                                <span>{{ $filter->nama_filter }}</span>
                                            </span>
                                            <span class="filter-option-mark is-check" aria-hidden="true"></span>
                                        </label>
                                    @empty
                                        <p class="filter-empty">Belum ada filter tersedia.</p>
                                    @endforelse

                                </div>

                                <div class="filter-panel-foot">
                                    <button type="button" class="btn btn-outline btn-sm" data-filter-clear>Atur ulang</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                                </div>
                            </div>

                        </div>

                        {{-- Hapus semua filter --}}
                        @if ($adaFilter)
                            <a
                                href="{{ route('donasi') }}"
                                class="filter-reset"
                                aria-label="Hapus semua filter"
                            >
                                <i class="bi bi-x-lg"></i>
                                <span>Hapus semua</span>
                            </a>
                        @endif

                    </div>

                    {{-- penangkap klik di luar panel --}}
                    <div class="filter-backdrop" data-filter-backdrop></div>

                </form>

            </div>
        </section>


        {{-- ==========================================
            2. KATEGORI DONASI
            - Tanpa kategori terpilih (atau "Lainnya"): tampil ikon kategori
            - Kategori terpilih: ikon hilang, langsung ke campaign.
              Desktop menampilkan judul kategori ringkas;
              di mobile nama kategori ada di header.
        =========================================== --}}
        @if ($kategoriAktif)

            <section class="donasi-kategori-bar">
                <div class="container">
                    <div class="donasi-kategori-title">
                        <h1>{{ $kategoriAktif->nama_kategori }}</h1>

                        <a href="{{ route('donasi') }}">
                            <i class="bi bi-grid"></i>
                            Semua kategori
                        </a>
                    </div>
                </div>
            </section>

        @else

            <section class="donasi-category-section">
                <div class="container">

                    <div class="category-grid">

                        @foreach ($kategori as $item)

                            @php
                                $namaKategori = strtolower($item->nama_kategori);
                                $icon = $kategoriIcons[$namaKategori] ?? 'assets/lainnya.svg';
                            @endphp

                            <a
                                href="{{ route('donasi', ['kategori' => $item->slug]) }}"
                                class="category-item"
                            >
                                <div class="category-icon">
                                    <img
                                        src="{{ asset($icon) }}"
                                        alt="{{ $item->nama_kategori }}"
                                    >
                                </div>

                                <p>{{ $item->nama_kategori }}</p>
                            </a>

                        @endforeach

                        {{-- Kategori lainnya --}}
                        <a
                            href="{{ route('donasi') }}"
                            class="category-item"
                        >
                            <div class="category-icon">
                                <img
                                    src="{{ asset('assets/lainnya.svg') }}"
                                    alt="Lainnya"
                                >
                            </div>

                            <p>Lainnya</p>
                        </a>

                    </div>

                </div>
            </section>

        @endif


        {{-- ==========================================
            3. CAMPAIGN DARURAT
        =========================================== --}}
        <section class="section donasi-campaign-section">
            <div class="container">

                <h2 class="section-title">
                    Campaign Darurat
                </h2>

                @if ($darurat->isNotEmpty())

                    <div class="campaign-grid">

                        @foreach ($darurat as $campaign)
                            <x-campaign-card :campaign="$campaign" />
                        @endforeach

                    </div>

                @else

                    <p class="donasi-empty">
                        Belum ada campaign darurat saat ini.
                    </p>

                @endif

            </div>
        </section>


        {{-- ==========================================
            4. CAMPAIGN TERBARU
            Di mobile: hanya thumbnail (judul disembunyikan secara visual)
        =========================================== --}}
        <section class="section section-new">
            <div class="container">

                <h2 class="section-title new-title">Yuk, Lihat yang Baru!</h2>

                <div class="new-grid">
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

                        <a href="{{ route('campaign.show', $campaign->custom_slug ?? $campaign->slug) }}" class="new-item">
                            <img
                                src="{{ asset('storage/' . $campaign->thumbnail) }}"
                                alt="{{ $campaign->judul }}"
                                loading="lazy"
                            >

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
                    @empty
                        <p>Belum ada campaign terbaru.</p>
                    @endforelse
                </div>

            </div>
        </section>


        {{-- ==========================================
            5. PEMBERDAYAAN BERKELANJUTAN
        =========================================== --}}
        <section class="section donasi-campaign-section">
            <div class="container">

                <h2 class="section-title">
                    Pemberdayaan Berkelanjutan
                </h2>

                @if ($pemberdayaan->isNotEmpty())

                    <div class="campaign-grid">

                        @foreach ($pemberdayaan as $campaign)
                            <x-campaign-card :campaign="$campaign" />
                        @endforeach

                    </div>

                @else

                    <p class="donasi-empty">
                        Belum ada campaign pemberdayaan berkelanjutan.
                    </p>

                @endif

            </div>
        </section>


        {{-- ==========================================
            6. CAMPAIGN LAINNYA
            Model list (sama seperti "Pilih Kategori Favoritmu" di landing)
        =========================================== --}}
        <section class="section donasi-campaign-section">
            <div class="container">

                <h2 class="section-title">
                    Campaign Lainnya
                </h2>

                @if ($campaigns->isNotEmpty())

                    <div class="list-campaign">

                        @foreach ($campaigns as $campaign)
                            <x-campaign-card :campaign="$campaign" variant="list" />
                        @endforeach

                    </div>

                @else

                    <p class="donasi-empty">
                        Belum ada campaign lainnya.
                    </p>

                @endif

            </div>
        </section>

    </main>


    {{-- ==========================================
        7. WHATSAPP FLOATING
    =========================================== --}}
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
    <script src="{{ asset('js/donasi.js') }}"></script>
</body>
</html>