<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita - Orang Baik</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/berita.css') }}">
</head>

<body>

    @include('components.header')

    <main class="berita-page page-wrapper">
        <section class="berita-section">
            <div class="container">

                {{-- Header dengan tombol Tambah Berita --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h1 style="font-size: var(--fs-lg); font-weight: var(--fw-bold); margin: 0; color: var(--text-dark);">Berita Terbaru</h1>
                        <p style="color: var(--muted); margin-top: 4px; font-size: var(--fs-xs);">Informasi dan kabar terbaru dari OrangBaik.id</p>
                    </div>
                    @auth
                        @php
                            $isPenggalang = Auth::user()->penggalangDana &&
                                in_array(Auth::user()->penggalangDana->status, ['approved', 'verified']);
                        @endphp
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('admin.berita.create') }}"
                                style="display:inline-flex; align-items:center; gap:6px; padding: 10px 20px; background: var(--primary); color: #fff; border-radius: var(--radius-full); font-weight: 600; font-size: var(--fs-xs); text-decoration:none; transition:.2s ease; box-shadow: var(--shadow-soft);">
                                <i class="bi bi-plus-lg"></i> Tambah Berita
                            </a>
                        @elseif ($isPenggalang)
                            <a href="{{ route('penggalang.berita.create') }}"
                                style="display:inline-flex; align-items:center; gap:6px; padding: 10px 20px; background: var(--primary); color: #fff; border-radius: var(--radius-full); font-weight: 600; font-size: var(--fs-xs); text-decoration:none; transition:.2s ease; box-shadow: var(--shadow-soft);">
                                <i class="bi bi-plus-lg"></i> Tambah Berita
                            </a>
                        @endif
                    @endauth
                </div>

                <div class="berita-grid">
                    @forelse ($beritas as $berita)
                        <article class="berita-card">
                            <a href="{{ route('berita.show', $berita->custom_slug ?? $berita->slug) }}">
                                <img src="{{ asset('storage/' . $berita->thumbnail) }}" alt="{{ $berita->judul }}"
                                    class="berita-image" loading="lazy">
                                <div class="berita-body">
                                    <h2>
                                        {{ Str::limit($berita->judul, 55) }}
                                    </h2>
                                    <p>
                                        {{ $berita->created_at->translatedFormat('d F Y') }}
                                    </p>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="berita-empty">
                            <p>Belum ada berita.</p>
                        </div>
                    @endforelse
                </div>
                <div class="pagination-wrapper">
                    @if (is_object($beritas) && method_exists($beritas, 'links'))
                        {{ $beritas->links() }}
                    @endif
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