
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pencarian - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @php
        /*
        |--------------------------------------------------------------------------
        | 1. JENIS PENCARIAN
        |--------------------------------------------------------------------------
        | Landing dan donasi : campaign
        | Halaman berita     : berita
        */

        $type = request()->query('type', $type ?? 'campaign');

        if (!in_array($type, ['campaign', 'berita'], true)) {
            $type = 'campaign';
        }

        /*
        |--------------------------------------------------------------------------
        | 2. FILTER CAMPAIGN KADALUWARSA
        |--------------------------------------------------------------------------
        | Campaign tanpa tanggal selesai tetap ditampilkan.
        | Campaign yang sudah melewati tanggal selesai disembunyikan.
        */

        if ($type === 'campaign') {
            $campaigns = $campaigns->filter(function ($campaign) {
                if (!$campaign->tanggal_selesai) {
                    return true;
                }

                return \Illuminate\Support\Carbon::parse(
                    $campaign->tanggal_selesai
                )->endOfDay()->isFuture();

            })->values();
        }

        /*
        |--------------------------------------------------------------------------
        | 3. DATA HASIL PENCARIAN
        |--------------------------------------------------------------------------
        */

        $sections = [
            'berita' => [
                'label' => 'Berita',
                'icon' => 'bi-newspaper',
                'items' => $berita,
                'empty_title' => 'Berita belum ditemukan',
                'empty' => 'Tidak ada berita yang cocok dengan kata kunci tersebut.',
            ],

            'campaign' => [
                'label' => 'Campaign',
                'icon' => 'bi-heart',
                'items' => $campaigns,
                'empty_title' => 'Campaign belum ditemukan',
                'empty' => 'Tidak ada campaign aktif yang cocok dengan kata kunci tersebut.',
            ],
        ];

        $section = $sections[$type];

        $jenisCari = $type === 'berita' ? 'berita' : 'campaign';
    @endphp

    {{-- HEADER --}}
    @include('components.header')

    <main class="page-wrapper">

        <section class="search-section">

            <div class="container">

                @if ($keyword !== '')

                    <section class="search-result-section">

                        {{-- HEADER HASIL PENCARIAN --}}
                        <div class="search-section-heading">

                            <h2 class="search-subtitle">
                                {{ $section['label'] }}
                            </h2>

                            <span class="search-count">
                                {{ $section['items']->count() }}
                            </span>

                        </div>

                        {{-- HASIL PENCARIAN --}}
                        @if ($section['items']->isNotEmpty())

                            <div class="search-list {{ $type === 'berita' ? 'search-news-list' : 'search-campaign-list' }}">

                                @foreach ($section['items'] as $item)

                                    @if ($type === 'campaign')

                                        {{-- ==================================================
                                             CAMPAIGN CARD
                                             Tetap menggunakan komponen utama
                                        ================================================== --}}

                                        <x-campaign-card
                                            :campaign="$item"
                                            variant="list"
                                        />

                                    @else

                                        {{-- ==================================================
                                             BERITA CARD
                                             Mengikuti desain halaman Berita
                                        ================================================== --}}

                                        @php
                                            $penerbit = $item->penggalangDana;

                                            $namaPenerbit = $penerbit?->nama_penggalang
                                                ?? 'Orang Baik';

                                            $inisialPenerbit = mb_strtoupper(
                                                mb_substr($namaPenerbit, 0, 1)
                                            );

                                            $fotoPenerbit = $penerbit?->foto_profil
                                                ? asset('storage/' . $penerbit->foto_profil)
                                                : ($penerbit ? null : asset('assets/logo.png'));
                                        @endphp

                                        <a
                                            href="{{ route('berita.show', $item->custom_slug ?? $item->slug) }}"
                                            class="search-card search-news-card"
                                        >

                                            {{-- THUMBNAIL --}}
                                            <div class="search-card-thumb">

                                                <img
                                                    src="{{ asset('storage/' . $item->thumbnail) }}"
                                                    alt="{{ $item->judul }}"
                                                    loading="lazy"
                                                >

                                            </div>

                                            {{-- CARD BODY --}}
                                            <div class="search-card-body">

                                                {{-- JUDUL --}}
                                                <h3 class="search-card-title">
                                                    {{ $item->judul }}
                                                </h3>

                                                {{-- PUBLISHER --}}
                                                <div
                                                    class="search-card-publisher"
                                                    title="{{ $namaPenerbit }}"
                                                >

                                                    <span class="search-card-publisher-avatar">

                                                        {{ $inisialPenerbit }}

                                                        @if ($fotoPenerbit)
                                                            <img
                                                                src="{{ $fotoPenerbit }}"
                                                                alt=""
                                                                loading="lazy"
                                                                onerror="this.remove()"
                                                            >
                                                        @endif

                                                    </span>

                                                    <span class="search-card-publisher-name">
                                                        {{ $namaPenerbit }}
                                                    </span>

                                                </div>

                                                {{-- FOOTER --}}
                                                <div class="search-card-footer">

                                                    <time
                                                        class="search-card-date"
                                                        datetime="{{ optional($item->created_at)->toDateString() }}"
                                                    >
                                                        <i class="bi bi-calendar3"></i>

                                                        {{ optional($item->created_at)->translatedFormat('d M Y') }}
                                                    </time>

                                                    <span class="search-card-tag">
                                                        <i class="bi bi-newspaper"></i>
                                                        Berita
                                                    </span>

                                                </div>

                                            </div>

                                        </a>

                                    @endif

                                @endforeach

                            </div>

                        @else

                            {{-- EMPTY STATE --}}
                            <div class="search-empty {{ $type === 'berita' ? 'search-news-empty' : '' }}">

                                <i class="bi {{ $section['icon'] }}"></i>

                                <h3>
                                    {{ $section['empty_title'] }}
                                </h3>

                                <p>
                                    {{ $section['empty'] }}
                                </p>

                                <span>
                                    Coba gunakan kata kunci yang berbeda.
                                </span>

                            </div>

                        @endif

                    </section>

                @else

                    {{-- BELUM ADA KATA KUNCI --}}
                    <div class="search-placeholder">

                        <i class="bi bi-search"></i>

                        <h2>Temukan yang kamu cari</h2>

                        <p>
                            Masukkan kata kunci untuk mencari {{ $jenisCari }}.
                        </p>

                    </div>

                @endif

            </div>

        </section>

    </main>

    {{-- FOOTER --}}
    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>

</body>
</html>
