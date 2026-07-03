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
                        {!! nl2br(e($berita->isi)) !!}
                    </div>

                    <div class="news-content">
                        {!! nl2br(e($berita->isi)) !!}
                    </div>

                    @if($berita->gambar->count())
                        <section class="news-gallery">

                            <h3>Galeri Dokumentasi</h3>

                            <div class="news-gallery-grid">

                                @foreach($berita->gambar as $gambar)

                                    <a href="{{ asset('storage/' . $gambar->gambar) }}" target="_blank"
                                        class="news-gallery-item">

                                        <img src="{{ asset('storage/' . $gambar->gambar) }}" alt="{{ $berita->judul }}">

                                    </a>

                                @endforeach

                            </div>

                        </section>
                    @endif

                    {{-- COMMENT --}}
                    <section class="comment-section">

                        <div class="comment-header">
                            <strong>Komentar</strong>
                            <span>{{ $berita->komentar->count() }}</span>
                        </div>

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

                    <p style="color:#888; margin-top:10px;">
                        Belum ada berita terkait.
                    </p>

                </aside>

            </div>

        </div>
    </main>

    {{-- MODAL KONFIRMASI LOGIN --}}
    <div class="login-modal-overlay" id="loginModalOverlay">
        <div class="login-modal">

            <div class="login-modal-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" />
                    <path d="M7 11V7a5 5 0 0110 0v4" />
                </svg>
            </div>

            <h3>Masuk Diperlukan</h3>
            <p>Silakan login terlebih dahulu untuk memberikan komentar pada berita ini.</p>

            <div class="login-modal-actions">
                <a href="{{ route('login') }}" class="modal-btn modal-btn-confirm">
                    Login Sekarang
                </a>
                <button type="button" class="modal-btn modal-btn-cancel" id="modalCancelBtn">
                    Batal
                </button>
            </div>

        </div>
    </div>

    @include('components.footer')
    <script>
        const commentForm = document.getElementById('commentForm');
        const loginModalOverlay = document.getElementById('loginModalOverlay');
        const modalCancelBtn = document.getElementById('modalCancelBtn');

        commentForm.addEventListener('submit', function (e) {
            if (this.dataset.loggedIn !== '1') {
                e.preventDefault();
                loginModalOverlay.classList.add('active');
            }
        });

        modalCancelBtn.addEventListener('click', function () {
            loginModalOverlay.classList.remove('active');
        });

        loginModalOverlay.addEventListener('click', function (e) {
            if (e.target === loginModalOverlay) {
                loginModalOverlay.classList.remove('active');
            }
        });
    </script>
</body>

</html>