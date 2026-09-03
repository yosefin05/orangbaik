<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $berita->judul ?? 'Detail Berita' }} - OrangBaik.id</title>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail-berita.css') }}">
</head>

<body>
    @include('components.header')
    <main class="detail-news-page">
        <div class="container detail-news-container">
            <div class="detail-news-layout">
                {{-- MAIN CONTENT --}}
                <article class="news-detail-main">
                    @if ($berita->thumbnail)
                        <img src="{{ asset('storage/' . $berita->thumbnail) }}" alt="{{ $berita->judul }}"
                            class="news-hero-image">
                    @endif
                    <h1>{{ $berita->judul }}</h1>
                    <div class="news-meta-row">
                        <span>{{ $berita->user->nama ?? 'Admin' }}</span>
                        <span>{{ $berita->created_at->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="news-content">
                        {!! $berita->isi !!}
                    </div>
                    {{-- COMMENT --}}
                    <section class="comment-section">
                        <div class="comment-header">
                            <strong>Komentar</strong>
                            <span>{{ $berita->komentar->count() }}</span>
                        </div>
                        @guest
                            <div class="comment-login-box">
                                <p>
                                    Anda harus <a
                                        href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">login</a>
                                    terlebih dahulu untuk memberikan komentar.
                                </p>
                            </div>
                        @endguest
                        @auth
                            <form action="{{ route('berita.komentar.store', $berita) }}" method="POST" class="comment-form"
                                id="commentForm" data-logged-in="{{ auth()->check() ? '1' : '0' }}"
                                data-login-url="{{ route('login') }}">
                                @csrf
                                <div class="comment-title">
                                    Tulis Komentar
                                </div>
                                <div class="comment-body">
                                    <textarea name="komentar" placeholder="Tulis komentar Anda di sini..."
                                        required></textarea>
                                    <button type="submit" class="comment-send" aria-label="Kirim komentar">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M3 20L22 12L3 4V10L16 12L3 14V20Z" />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        @endauth
                        @forelse($berita->komentar()->latest()->get() as $komentar)
                            <div class="comment-card" id="komentar-{{ $komentar->id }}">
                                <div class="comment-info">
                                    <strong>
                                        @if($komentar->user)
                                            {{ $komentar->user->name }}
                                        @else
                                            Pengguna
                                        @endif
                                    </strong>
                                    <span>
                                        {{ $komentar->created_at->format('d M Y • H:i') }}
                                    </span>
                                </div>
                                <div class="comment-text">
                                    {{ $komentar->komentar }}
                                </div>
                            </div>
                        @empty
                            <div class="empty-comment">
                                <div class="empty-icon">
                                    <svg viewBox="0 0 24 24">
                                        <path
                                            d="M7 18L4 21V6C4 4.9 4.9 4 6 4H18C19.1 4 20 4.9 20 6V16C20 17.1 19.1 18 18 18H7Z" />
                                        <path d="M8 10H8.01" />
                                        <path d="M12 10H12.01" />
                                        <path d="M16 10H16.01" />
                                    </svg>
                                </div>
                                <p>Tidak Ada Komentar</p>
                            </div>
                        @endforelse
                    </section>
                </article>

                {{-- SIDEBAR --}}
                <aside class="news-sidebar">
                    <h3>Berita Lainnya</h3>

                    @forelse($relatedNews as $news)
                        <a href="{{ route('berita.show', $news->custom_slug ?? $news->slug) }}" class="sidebar-news-card">
                            <img src="{{ asset('storage/' . $news->thumbnail) }}" alt="{{ $news->judul }}">
                            <div class="sidebar-news-content">
                                <h4>{{ Str::limit($news->judul, 55) }}</h4>
                                <span>
                                    {{ $news->user->nama ?? 'Admin' }}
                                </span>
                                <span>
                                    {{ $news->created_at->translatedFormat('d F Y') }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="empty-news">
                            Belum ada berita terkait.
                        </p>
                    @endforelse
                </aside>
            </div>
        </div>
        <div class="gallery-modal" id="galleryModal">
            <span class="gallery-close">&times;</span>
            <button class="gallery-prev">&#10094;</button>
            <img id="galleryImage">
            <button class="gallery-next">&#10095;</button>
        </div>
    </main>
    @include('components.footer')
    <script src="{{ asset('js/detail-berita.js') }}"></script>
</body>

</html>