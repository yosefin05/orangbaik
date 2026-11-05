
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi - OrangBaik</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>

<body>

    @include('components.header')

    <main class="main donasi-page">

        {{-- ==========================================
            1. FILTER DONASI
        =========================================== --}}
        <section class="donasi-filter-section">
            <div class="container">

                <form
                    action="{{ route('donasi') }}"
                    method="GET"
                    id="filterForm"
                    class="donasi-filter-form"
                >

                    <div class="donasi-filter-top">

                        <div class="donasi-filter-selects">

                            {{-- Filter jenis penggalang --}}
                            <select
                                name="jenis"
                                id="jenisFilter"
                                onchange="this.form.submit()"
                            >
                                <option value="">Semua Jenis</option>

                                <option
                                    value="individu"
                                    {{ request('jenis') === 'individu' ? 'selected' : '' }}
                                >
                                    Individu
                                </option>

                                <option
                                    value="organisasi"
                                    {{ request('jenis') === 'organisasi' ? 'selected' : '' }}
                                >
                                    Organisasi
                                </option>
                            </select>

                            {{-- Filter penggalang dana --}}
                            <select
                                name="penggalang_id"
                                id="penggalangFilter"
                                onchange="this.form.submit()"
                            >
                                <option value="">Semua Penggalang</option>

                                @foreach ($penggalangDana as $penggalang)
                                    <option
                                        value="{{ $penggalang->id }}"
                                        {{ request('penggalang_id') == $penggalang->id ? 'selected' : '' }}
                                    >
                                        {{ $penggalang->nama_penggalang }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        {{-- Tombol reset --}}
                        @if (request()->hasAny([
                            'jenis',
                            'penggalang_id',
                            'filter_ids',
                            'kategori'
                        ]))
                            <button
                                type="button"
                                id="resetFilterBtn"
                                class="donasi-reset-filter"
                            >
                                Reset Filter
                            </button>
                        @endif

                    </div>

                    {{-- Filter berdasarkan pilihan filter --}}
                    <div class="donasi-filter-chips">

                        @foreach ($filters as $filter)
                            <label class="donasi-filter-chip {{ in_array($filter->id, $selectedFilterIds) ? 'active' : '' }}">

                                <input
                                    type="checkbox"
                                    name="filter_ids[]"
                                    value="{{ $filter->id }}"
                                    {{ in_array($filter->id, $selectedFilterIds) ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                >

                                <span>{{ $filter->nama_filter }}</span>

                            </label>
                        @endforeach

                    </div>

                </form>

            </div>
        </section>


        {{-- ==========================================
            2. KATEGORI DONASI
            Menggunakan styling kategori Home
        =========================================== --}}
        <section class="donasi-category-section">
            <div class="container">

                <div class="category-grid">

                    @php
                        $kategoriIcons = [
                            'zakat' => 'assets/zakat.svg',
                            'wakaf' => 'assets/wakaf.svg',
                            'infaq' => 'assets/infaq.svg',
                            'kemanusiaan' => 'assets/kemanusiaan.svg',
                            'sedekah rutin' => 'assets/sedekah-rutin.svg',
                        ];
                    @endphp

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


        {{-- ==========================================
            3. CAMPAIGN DARURAT
            Menggunakan campaign-card Home
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
            Menggunakan variant new dari komponen Home
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
                    @empty
                        <p>Belum ada campaign terbaru.</p>
                    @endforelse
                </div>
            </div>
        </section>


        {{-- ==========================================
            5. PEMBERDAYAAN BERKELANJUTAN
            Menggunakan campaign-card Home
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
            Menggunakan campaign-card Home
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
    <script src="{{ asset('js/donasi.js') }}"></script>

</body>
</html>
