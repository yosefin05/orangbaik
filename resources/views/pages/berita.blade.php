<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Berita - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/berita.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @php
        $urlTambahBerita = null;

        if (Auth::check()) {
            $user = Auth::user();

            $isPenggalang = $user->penggalangDana 
                && in_array($user->penggalangDana->status, ['approved', 'verified']);

            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                $urlTambahBerita = route('admin.berita.create');
            } elseif ($isPenggalang) {
                $urlTambahBerita = route('penggalang.berita.create');
            }
        }

        $punyaNomorHalaman = is_object($beritas) && method_exists($beritas, 'lastPage');
    @endphp

    {{-- HEADER UTAMA --}}
    @include('components.header')

    <main class="berita-page page-wrapper">

        <section class="berita-section">
            <div class="container">

                {{-- HEADER HALAMAN --}}
                <div class="berita-header">

                    <div class="berita-heading">
                        <h1>Berita Terbaru</h1>
                        <p>Informasi dan kabar terbaru dari OrangBaik.id</p>
                    </div>

                    @if ($urlTambahBerita)
                        <a href="{{ $urlTambahBerita }}" class="berita-add">
                            <i class="bi bi-plus-lg"></i>
                            Tambah Berita
                        </a>
                    @endif

                </div>


                {{-- DAFTAR BERITA GRID --}}
                <div class="berita-grid">

                    @forelse ($beritas as $berita)

                        @php
                            $penerbit = $berita->penggalangDana;

                            $namaPenerbit = $penerbit?->nama_penggalang ?? 'Orang Baik';

                            $inisialPenerbit = mb_strtoupper(
                                mb_substr($namaPenerbit, 0, 1)
                            );

                            $fotoPenerbit = $penerbit?->foto_profil
                                ? asset('storage/' . $penerbit->foto_profil)
                                : ($penerbit ? null : asset('assets/logo.png'));
                        @endphp

                        <a href="{{ route('berita.show', $berita->custom_slug ?? $berita->slug) }}" class="berita-card">

                            {{-- THUMBNAIL --}}
                            <div class="berita-thumb">
                                <img src="{{ asset('storage/' . $berita->thumbnail) }}" alt="{{ $berita->judul }}" loading="lazy">
                            </div>

                            {{-- CARD CONTENT --}}
                            <div class="berita-body">

                                <h2 class="berita-title">
                                    {{ $berita->judul }}
                                </h2>

                                {{-- PUBLISHER --}}
                                <div class="berita-publisher" title="{{ $namaPenerbit }}">

                                    <span class="berita-publisher-avatar">
                                        {{ $inisialPenerbit }}

                                        @if ($fotoPenerbit)
                                            <img src="{{ $fotoPenerbit }}" alt="" loading="lazy" onerror="this.remove()">
                                        @endif
                                    </span>

                                    <span class="berita-publisher-name">
                                        {{ $namaPenerbit }}
                                    </span>

                                </div>

                                {{-- CARD FOOTER --}}
                                <div class="berita-footer">

                                    <time class="berita-date" datetime="{{ optional($berita->created_at)->toDateString() }}">
                                        <i class="bi bi-calendar3"></i>
                                        {{ optional($berita->created_at)->translatedFormat('d M Y') }}
                                    </time>

                                    <span class="berita-tag">
                                        <i class="bi bi-newspaper"></i>
                                        Berita
                                    </span>

                                </div>

                            </div>

                        </a>

                    @empty

                        <div class="berita-empty">
                            <i class="bi bi-newspaper"></i>
                            <p>Belum ada berita.</p>
                        </div>

                    @endforelse

                </div>


                {{-- PAGINATION --}}
                @if ($punyaNomorHalaman && $beritas->hasPages())

                    @php
                        $halamanSekarang = $beritas->currentPage();
                        $halamanTerakhir = $beritas->lastPage();

                        $daftarHalaman = collect(range(1, $halamanTerakhir))
                            ->filter(fn ($halaman) =>
                                $halaman === 1
                                || $halaman === $halamanTerakhir
                                || abs($halaman - $halamanSekarang) <= 1
                            )
                            ->values();
                    @endphp

                    <nav class="berita-pagination" aria-label="Navigasi halaman berita">

                        @unless ($beritas->onFirstPage())
                            <a href="{{ $beritas->previousPageUrl() }}" class="berita-page" rel="prev" aria-label="Halaman sebelumnya">
                                <i class="bi bi-chevron-left"></i>
                                <span class="berita-page-label">Sebelumnya</span>
                            </a>
                        @endunless

                        @foreach ($daftarHalaman as $i => $halaman)

                            @if ($i > 0 && $halaman - $daftarHalaman[$i - 1] > 1)
                                <span class="berita-page-gap">…</span>
                            @endif

                            @if ($halaman === $halamanSekarang)
                                <span class="berita-page is-active" aria-current="page">
                                    {{ $halaman }}
                                </span>
                            @else
                                <a href="{{ $beritas->url($halaman) }}" class="berita-page" aria-label="Halaman {{ $halaman }}">
                                    {{ $halaman }}
                                </a>
                            @endif

                        @endforeach

                        @if ($beritas->hasMorePages())
                            <a href="{{ $beritas->nextPageUrl() }}" class="berita-page" rel="next" aria-label="Halaman berikutnya">
                                <span class="berita-page-label">Berikutnya</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @endif

                    </nav>

                @elseif (is_object($beritas) && method_exists($beritas, 'links'))

                    <div class="berita-pagination-fallback">
                        {{ $beritas->links() }}
                    </div>

                @endif

            </div>
        </section>

    </main>


    {{-- FLOATING WHATSAPP BUTTON --}}
    @if (env('ENABLE_WA_FLOATING', true))

        <div class="floating-wa-container">
            <a href="https://wa.me/{{ env('WHATSAPP_NUMBER', '6281385002300') }}?text={{ urlencode(env('WHATSAPP_MESSAGE', 'Halo tim OrangBaik.id, saya mau bertanya mengenai...')) }}"
                target="_blank" rel="noopener noreferrer" class="floating-wa-btn" aria-label="Hubungi Customer Service via WhatsApp">

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