<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Campaign - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail-campaign.css') }}">

    {{-- QR Code library (Client-side) --}}
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
</head>

<body>

    @include('components.header')

    <main class="campaign-detail-page">

        @if (session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <div class="container detail-container">
            <div class="detail-layout">

                {{-- ========================================================== --}}
                {{-- LEFT CONTENT                                               --}}
                {{-- ========================================================== --}}
                <div class="detail-main">

                    <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}"
                        class="campaign-hero-image">

                    <section class="description-section">
                        <h1>{{ $campaign->judul }}</h1>
                        <p>{{ $campaign->deskripsi }}</p>
                    </section>

                    {{-- ========================================================== --}}
                    {{-- KABAR TERBARU - UPDATE CAMPAIGN                            --}}
                    {{-- ========================================================== --}}
                    <section class="latest-news-section">
                        <div class="section-header">
                            <h2>Kabar Terbaru</h2>
                            @auth
                                @if ($campaign->isOwner(Auth::id()))
                                    <a href="{{ route('campaign.update.create', $campaign->slug) }}" class="btn-add-update">
                                        <i class="bi bi-plus-circle"></i> Tambah Update
                                    </a>
                                @endif
                            @endauth
                        </div>

                        @if ($campaign->campaignUpdates->count() > 0)
                            <div class="updates-list">
                                @foreach ($campaign->campaignUpdates as $update)
                                    @php
                                        $firstImage = $update->campaign_update_gambar->first();
                                        $thumbnailImage = $firstImage
                                            ? asset('storage/' . $firstImage->gambar_update)
                                            : asset('storage/' . $campaign->thumbnail);
                                    @endphp

                                    <div class="update-card" id="update-{{ $update->id }}">
                                        <div class="update-thumbnail">
                                            <img src="{{ $thumbnailImage }}" alt="{{ $update->judul_update }}">
                                        </div>

                                        <div class="update-content-wrapper">
                                            <h3 class="update-title">{{ $update->judul_update }}</h3>

                                            <div class="update-meta">
                                                <span class="update-date">
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ $update->created_at->translatedFormat('d F Y') }}
                                                </span>
                                            </div>

                                            <div class="update-preview-text">
                                                {{ Str::limit($update->isi_update, 120) }}
                                            </div>

                                            {{-- Tombol Baca Selengkapnya (expand) --}}
                                            <button class="btn-read-more" onclick="toggleUpdateDetail({{ $update->id }})">
                                                <span id="toggleText-{{ $update->id }}">Baca Selengkapnya</span>
                                                <i class="bi bi-chevron-down" id="toggleIcon-{{ $update->id }}"></i>
                                            </button>

                                            {{-- Detail Lengkap (expand) --}}
                                            <div class="update-detail-wrapper" id="detailContent-{{ $update->id }}">
                                                <div class="update-detail-content">
                                                    <div class="update-full-body">
                                                        {!! nl2br(e($update->isi_update)) !!}
                                                    </div>

                                                    @if ($update->campaign_update_gambar->count() > 0)
                                                        @php
                                                            $allImages = $update->campaign_update_gambar->map(function ($g) {
                                                                return asset('storage/' . $g->gambar_update);
                                                            })->toArray();
                                                            $allImagesJson = json_encode($allImages);
                                                        @endphp
                                                        <div class="update-detail-gallery">
                                                            @foreach ($update->campaign_update_gambar as $gambar)
                                                                <div class="update-detail-gallery-item"
                                                                    onclick="openLightbox('{{ asset('storage/' . $gambar->gambar_update) }}', {{ $allImagesJson }})">
                                                                    <img src="{{ asset('storage/' . $gambar->gambar_update) }}" alt="Gambar update">
                                                                    <div class="zoom-overlay">
                                                                        <i class="bi bi-zoom-in"></i>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            @auth
                                                @if ($campaign->isOwner(Auth::id()))
                                                    <div class="update-footer">
                                                        <form action="{{ route('campaign.update.destroy', [$campaign->slug, $update->id]) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-delete-update" onclick="return confirm('Yakin ingin menghapus update ini?')">
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-updates">
                                <i class="bi bi-megaphone"></i>
                                <p>Belum ada update dari campaign ini.</p>
                                @auth
                                    @if ($campaign->isOwner(Auth::id()))
                                        <a href="{{ route('campaign.update.create', $campaign->slug) }}" class="btn btn-outline">
                                            Buat Update Pertama
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    </section>

                </div>

                {{-- ========================================================== --}}
                {{-- RIGHT SIDEBAR                                              --}}
                {{-- ========================================================== --}}
                <aside class="detail-sidebar">

                    {{-- Donation Summary --}}
                    <div class="donation-summary-card">
                        <h2>{{ $campaign->judul }}</h2>

                        <div class="summary-amount">
                            <strong>Rp {{ number_format($campaign->terkumpul ?? 0, 0, ',', '.') }}</strong>
                            <span>
                                Terkumpul dari
                                <b>Rp {{ number_format($campaign->target_donasi, 0, ',', '.') }}</b>
                            </span>
                        </div>

                        @php
                            $persen = $campaign->target_donasi > 0
                                ? min(100, (($campaign->terkumpul ?? 0) / $campaign->target_donasi) * 100)
                                : 0;
                        @endphp

                        <div class="summary-progress">
                            <div style="width: {{ $persen }}%"></div>
                        </div>

                        <div class="summary-meta">
                            <span>👤 {{ $campaign->donasi_count ?? 0 }} donatur</span>
                            <span>
                                @php
                                    use Carbon\Carbon;

                                    $hariIni = Carbon::today();
                                    $mulai = Carbon::parse($campaign->tanggal_mulai)->startOfDay();
                                    $akhir = Carbon::parse($campaign->tanggal_berakhir)->endOfDay();

                                    if ($hariIni->lt($mulai)) {
                                        $statusHari = 'Mulai dalam ' . $hariIni->diffInDays($mulai) . ' hari';
                                    } elseif ($hariIni->gt($akhir)) {
                                        $statusHari = 'Campaign berakhir';
                                    } else {
                                        $sisaHari = (int) $hariIni->diffInDays($akhir);
                                        $statusHari = $sisaHari == 0 ? 'Hari terakhir' : $sisaHari . ' Hari lagi';
                                    }
                                @endphp
                                {{ $statusHari }}
                            </span>
                        </div>

                        <a href="#" class="donate-button">Donasi Sekarang</a>
                    </div>

                    {{-- Fundraiser Info --}}
                    <div class="fundraiser-info-card">
                        <h3>Informasi Penggalang Dana</h3>

                        <a href="{{ route('profil.penggalang', $campaign->penggalangDana->id) }}" class="fundraiser-profile">
                            <img src="{{ asset('storage/' . ($campaign->penggalangDana->foto_profil ?? 'assets/logo-icon.png')) }}"
                                alt="{{ $campaign->penggalangDana->nama_penggalang }}">
                            <div>
                                <h4>{{ $campaign->penggalangDana->nama_penggalang ?? 'Orang Baik' }}</h4>
                            </div>
                        </a>
                    </div>

                    {{-- Donasi Terbaru --}}
                    <div class="side-list-card">
                        <h3>Donasi Terbaru</h3>

                        @if ($campaign->donasi->count() > 0)
                            @foreach ($campaign->donasi()->latest()->take(5)->get() as $donasi)
                                <div class="side-list-item">
                                    <div class="avatar-circle">
                                        @if (!$donasi->is_anonymous && $donasi->user && $donasi->user->foto_profil)
                                            <img src="{{ asset('storage/' . $donasi->user->foto_profil) }}"
                                                alt="{{ $donasi->user->name }}">
                                        @else
                                            👤
                                        @endif
                                    </div>
                                    <div>
                                        <h4>{{ $donasi->is_anonymous ? 'Anonim' : ($donasi->nama_donatur ?? $donasi->user->name ?? 'Donatur') }}
                                        </h4>
                                        <p>Berdonasi sebesar <b>Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</b></p>
                                        <span>{{ $donasi->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">Belum ada donasi.</p>
                        @endif
                    </div>

                    {{-- ========================================================== --}}
                    {{-- FUNDRAISER SECTION                                         --}}
                    {{-- ========================================================== --}}
                    @auth
                        @php
                            $isFundraiser = $campaign->isFundraiser(Auth::id());
                            $isOwner = $campaign->isOwner(Auth::id());
                        @endphp

                        @if (!$isFundraiser && !$isOwner)
                            <div class="fundraiser-join-card">
                                <h3>Jadi Fundraiser</h3>
                                <p>Bantu sebarkan campaign ini dan dapatkan reward!</p>
                                <form action="{{ route('fundraiser.store', $campaign->slug) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-fundraiser-join">
                                        <i class="bi bi-megaphone"></i> Gabung Jadi Fundraiser
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if ($isFundraiser && !$isOwner)
                            @php
                                $fundraiser = $campaign->getFundraiserByUser(Auth::id());
                            @endphp
                            <div class="fundraiser-referral-card">
                                <h4>🔗 Link Referral Anda</h4>
                                <p>Bagikan link ini untuk mengajak donasi:</p>
                                <div class="referral-link-box">
                                    <input type="text" id="referralLink" value="{{ $fundraiser->referral_url }}" readonly>
                                    <button onclick="copyReferralLink()" class="btn-copy-link">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                                <small>Setiap donasi melalui link ini akan tercatat atas nama Anda.</small>

                                {{-- QR Code langsung tampil, tanpa perlu diklik --}}
                                <div class="referral-qr-section">
                                    <div class="qr-canvas-wrapper">
                                        <canvas id="referralQrCanvas" data-url="{{ $fundraiser->referral_url }}"></canvas>
                                    </div>
                                    <button type="button" class="btn-download-qr" onclick="downloadReferralQr()">
                                        <i class="bi bi-download"></i> Download QR Code
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if ($isFundraiser && !$isOwner)
                            <div class="fundraiser-join-card joined">
                                <h3>✅ Anda Fundraiser</h3>
                                <p>Terima kasih telah menjadi fundraiser untuk campaign ini!</p>
                                <form action="{{ route('fundraiser.destroy', $campaign->slug) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-fundraiser-leave" onclick="return confirm('Yakin ingin berhenti menjadi fundraiser?')">
                                        <i class="bi bi-x-circle"></i> Berhenti
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth

                    @guest
                        <div class="fundraiser-join-card">
                            <h3>Jadi Fundraiser</h3>
                            <p>Login terlebih dahulu untuk menjadi fundraiser.</p>
                            <a href="{{ route('login') }}" class="btn-fundraiser-login">Login</a>
                        </div>
                    @endguest

                </aside>

            </div>
        </div>

    </main>

    {{-- ========================================================== --}}
    {{-- LIGHTBOX MODAL (GALERI UPDATE)                             --}}
    {{-- ========================================================== --}}
    <div class="lightbox-overlay" id="lightboxOverlay">
        <button class="lightbox-close" onclick="closeLightbox()"><i class="bi bi-x-lg"></i></button>
        <button class="lightbox-prev" onclick="changeImage(-1)"><i class="bi bi-chevron-left"></i></button>
        <div class="lightbox-image-wrapper">
            <img id="lightboxImage" src="" alt="Gambar">
        </div>
        <button class="lightbox-next" onclick="changeImage(1)"><i class="bi bi-chevron-right"></i></button>
        <div class="lightbox-footer">
            <span id="lightboxCounter">1 / 1</span>
            <div class="lightbox-dots" id="lightboxDots"></div>
        </div>
    </div>

    @include('components.footer')

    {{-- ========================================================== --}}
    {{-- SCRIPTS                                                    --}}
    {{-- ========================================================== --}}
    <script>
        /* ============================================================
           REFERRAL LINK - COPY
           ============================================================ */
        function copyReferralLink() {
            const input = document.getElementById('referralLink');
            if (!input) return;
            input.select();
            document.execCommand('copy');
            alert('Link referral berhasil disalin!');
        }

        /* ============================================================
           TOGGLE UPDATE DETAIL (EXPAND/COLLAPSE)
           ============================================================ */
        function toggleUpdateDetail(id) {
            const wrapper = document.getElementById('detailContent-' + id);
            const toggleText = document.getElementById('toggleText-' + id);
            const icon = document.getElementById('toggleIcon-' + id);
            if (!wrapper || !toggleText || !icon) return;

            const isOpen = wrapper.classList.contains('is-open');

            if (isOpen) {
                wrapper.style.maxHeight = wrapper.scrollHeight + 'px';
                requestAnimationFrame(() => {
                    wrapper.style.maxHeight = '0px';
                });
                wrapper.classList.remove('is-open');
                toggleText.textContent = 'Baca Selengkapnya';
                icon.classList.remove('rotated');
            } else {
                wrapper.classList.add('is-open');
                wrapper.style.maxHeight = wrapper.scrollHeight + 'px';
                toggleText.textContent = 'Sembunyikan';
                icon.classList.add('rotated');

                wrapper.addEventListener('transitionend', function handler(e) {
                    if (e.propertyName === 'max-height' && wrapper.classList.contains('is-open')) {
                        wrapper.style.maxHeight = 'none';
                    }
                    wrapper.removeEventListener('transitionend', handler);
                });
            }
        }

        /* ============================================================
           LIGHTBOX GALLERY
           ============================================================ */
        let lightboxImages = [];
        let currentImageIndex = 0;

        function openLightbox(imageSrc, allImages) {
            lightboxImages = allImages || [imageSrc];
            currentImageIndex = lightboxImages.indexOf(imageSrc);
            if (currentImageIndex === -1) {
                currentImageIndex = 0;
                lightboxImages = [imageSrc];
            }

            const overlay = document.getElementById('lightboxOverlay');
            const img = document.getElementById('lightboxImage');
            const counter = document.getElementById('lightboxCounter');
            const dots = document.getElementById('lightboxDots');

            img.src = lightboxImages[currentImageIndex];
            counter.textContent = (currentImageIndex + 1) + ' / ' + lightboxImages.length;

            dots.innerHTML = '';
            lightboxImages.forEach((_, i) => {
                const dot = document.createElement('span');
                dot.className = 'lightbox-dot' + (i === currentImageIndex ? ' active' : '');
                dot.onclick = function(e) {
                    e.stopPropagation();
                    goToImage(i);
                };
                dots.appendChild(dot);
            });

            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', lightboxKeydown);
        }

        function closeLightbox() {
            document.getElementById('lightboxOverlay').classList.remove('active');
            document.body.style.overflow = '';
            document.removeEventListener('keydown', lightboxKeydown);
        }

        function changeImage(direction) {
            const total = lightboxImages.length;
            if (total <= 1) return;
            currentImageIndex = (currentImageIndex + direction + total) % total;
            updateLightboxImage();
        }

        function goToImage(index) {
            if (index < 0 || index >= lightboxImages.length) return;
            currentImageIndex = index;
            updateLightboxImage();
        }

        function updateLightboxImage() {
            const img = document.getElementById('lightboxImage');
            const counter = document.getElementById('lightboxCounter');
            const dots = document.querySelectorAll('.lightbox-dot');
            img.src = lightboxImages[currentImageIndex];
            counter.textContent = (currentImageIndex + 1) + ' / ' + lightboxImages.length;
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentImageIndex);
            });
        }

        function lightboxKeydown(e) {
            if (e.key === 'ArrowLeft') changeImage(-1);
            if (e.key === 'ArrowRight') changeImage(1);
            if (e.key === 'Escape') closeLightbox();
        }

        let touchStartX = 0,
            touchEndX = 0;
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.querySelector('.lightbox-image-wrapper');
            if (wrapper) {
                wrapper.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].screenX;
                }, { passive: true });
                wrapper.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].screenX;
                    const diff = touchStartX - touchEndX;
                    if (Math.abs(diff) > 50) {
                        changeImage(diff > 0 ? 1 : -1);
                    }
                }, { passive: true });
            }
        });

        document.getElementById('lightboxOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });

        /* ============================================================
           QR CODE REFERRAL - TAMPIL OTOMATIS
           ============================================================ */
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('referralQrCanvas');
            if (!canvas) return; // hanya render kalau user adalah fundraiser (elemen ada di DOM)

            const url = canvas.dataset.url;

            QRCode.toCanvas(canvas, url, {
                width: 180,
                margin: 2,
                color: {
                    dark: '#1a1a1a',
                    light: '#ffffff'
                }
            }, function(error) {
                if (error) console.error('Gagal membuat QR code:', error);
            });
        });

        function downloadReferralQr() {
            const canvas = document.getElementById('referralQrCanvas');
            if (!canvas) return;

            const link = document.createElement('a');
            link.download = 'qr-referral-orangbaik.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    </script>

</body>

</html>