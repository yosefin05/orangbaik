<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pencarian - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @php
        // Hanya untuk tampilan: dua kelompok hasil memakai markup card yang sama
        $sections = [
            'berita' => [
                'label' => 'Berita',
                'icon' => 'bi-newspaper',
                'route' => 'berita.show',
                'items' => $berita,
                'empty' => 'Tidak ada berita yang cocok dengan kata kunci tersebut.',
            ],
            'campaign' => [
                'label' => 'Campaign',
                'icon' => 'bi-heart',
                'route' => 'campaign.show',
                'items' => $campaigns,
                'empty' => 'Tidak ada campaign yang cocok dengan kata kunci tersebut.',
            ],
        ];
 
        $jenisCari = match ($type) {
            'berita' => 'berita',
            'campaign' => 'campaign',
            default => 'campaign atau berita',
        };
    @endphp

    @include('components.header')

    <main class="page-wrapper">
        <section class="search-section">
            <div class="container">

                @if ($keyword !== '')

                    @foreach ($sections as $key => $section)

                        {{-- tampilkan semua kelompok, atau hanya yang dipilih lewat $type --}}
                        @if (! in_array($type, ['berita', 'campaign']) || $type === $key)

                            <section class="search-result-section">

                                <div class="search-section-heading">
                                    <h2 class="search-subtitle">{{ $section['label'] }}</h2>
                                    <span class="search-count">{{ $section['items']->count() }}</span>
                                </div>

                                @if ($section['items']->isNotEmpty())

                                    <div class="search-list">
                                        @foreach ($section['items'] as $item)
                                            @php
                                                $publisher = $key === 'berita'
                                                    ? 'OrangBaik.id'
                                                    : ($item->penggalangDana?->nama_penggalang ?? 'OrangBaik');
                                            @endphp

                                            <a href="{{ route($section['route'], $item->custom_slug ?? $item->slug) }}"
                                                class="search-card">

                                                <div class="search-card-image">
                                                    <img src="{{ asset('storage/' . $item->thumbnail) }}"
                                                        alt="{{ $item->judul }}" loading="lazy">

                                                    <span class="search-card-badge">
                                                        <i class="bi {{ $section['icon'] }}"></i>
                                                        {{ $section['label'] }}
                                                    </span>
                                                </div>

                                                <div class="search-card-body">
                                                    <h3 class="search-card-title">{{ $item->judul }}</h3>

                                                    <p class="search-card-publisher">
                                                        <span class="search-card-avatar">{{ mb_strtoupper(mb_substr($publisher, 0, 1)) }}</span>
                                                        <span class="search-card-publisher-name">{{ $publisher }}</span>
                                                    </p>

                                                    <div class="search-card-footer">
                                                        <span class="search-card-date">
                                                            <i class="bi bi-calendar3"></i>
                                                            {{ $item->created_at->translatedFormat('d F Y') }}
                                                        </span>
                                                        <i class="bi bi-arrow-right search-card-arrow"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>

                                @else

                                    <div class="search-empty">
                                        <i class="bi {{ $section['icon'] }}"></i>
                                        <p>{{ $section['empty'] }}</p>
                                    </div>

                                @endif

                            </section>

                        @endif

                    @endforeach

                @else

                    <div class="search-placeholder">
                        <i class="bi bi-search"></i>
                        <h2>Temukan yang kamu cari</h2>
                        <p>Masukkan kata kunci untuk mencari {{ $jenisCari }}.</p>
                    </div>

                @endif

            </div>
        </section>
    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>

</body>

</html>