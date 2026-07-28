<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/search.css') }}">
</head>

<body>

    @include('components.header')

    <main class="page-wrapper">

        <section class="search-section">
            <div class="container">
                @if($keyword)
                    <h2 class="search-title">
                        Hasil Pencarian untuk "<strong>{{ $keyword }}</strong>"
                    </h2>

                    {{-- ===================== --}}
                    {{-- 1. BERITA (Horizontal Scroll) --}}
                    {{-- ===================== --}}
                    <div class="search-result-section">
                        <div class="search-subtitle">
                            Berita <span>{{ $berita->count() }}</span>
                        </div>

                        @if($berita->count() > 0)
                            <div class="berita-scroll-wrapper">
                                <div class="berita-scroll">
                                    @foreach ($berita as $news)
                                        <article class="berita-card">
                                            <a href="{{ route('berita.show', $news->slug) }}">
                                                <img src="{{ asset('storage/' . $news->thumbnail) }}"
                                                    alt="{{ $news->judul }}"
                                                    loading="lazy">
                                                <div class="berita-body">
                                                    <h4>
                                                        {!! str_ireplace($keyword, '<strong>' . $keyword . '</strong>', $news->judul) !!}
                                                    </h4>
                                                    <p>{{ Str::limit($news->deskripsi ?? '', 80) }}</p>
                                                    <span class="berita-date">
                                                        {{ $news->created_at->translatedFormat('d F Y') }}
                                                    </span>
                                                </div>
                                            </a>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="empty-result">Tidak ada berita yang cocok.</p>
                        @endif
                    </div>

                    {{-- ===================== --}}
                    {{-- 2. CAMPAIGN (Grid 4 kolom) --}}
                    {{-- ===================== --}}
                    <div class="search-result-section" style="margin-top: 3rem;">
                        <div class="search-subtitle">
                            Campaign <span>{{ $campaigns->count() }}</span>
                        </div>

                        @if($campaigns->count() > 0)
                            <div class="campaign-grid-search">
                                @foreach ($campaigns as $campaign)
                                    @php
                                        $terkumpul = $campaign->donasi->sum('nominal');
                                        $persen = $campaign->target_donasi
                                            ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                            : 0;
                                        $hari = max(
                                            0,
                                            (int) now()->diffInDays($campaign->tanggal_berakhir, false)
                                        );
                                    @endphp

                                    <article class="campaign-card">
                                        <a href="{{ route('campaign.show', $campaign->slug) }}">
                                            <img class="campaign-image"
                                                src="{{ asset('storage/' . $campaign->thumbnail) }}"
                                                alt="{{ $campaign->judul }}"
                                                loading="lazy">
                                        </a>
                                        <div class="campaign-body">
                                            <h3>
                                                {!! str_ireplace($keyword, '<strong>' . $keyword . '</strong>', $campaign->judul) !!}
                                            </h3>
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
                                                <span>{{ $hari }} Hari</span>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <p class="empty-result">Tidak ada campaign yang cocok.</p>
                        @endif
                    </div>

                @else
                    <div class="search-placeholder">
                        <p>🔍 Masukkan kata kunci untuk mencari campaign atau berita.</p>
                    </div>
                @endif

            </div>
        </section>

    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>

</body>

</html>