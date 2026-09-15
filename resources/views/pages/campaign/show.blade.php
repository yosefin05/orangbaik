<!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $campaign->judul }} - OrangBaik.id</title>

        <link rel="stylesheet" href="{{ asset('css/global.css') }}">
        <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
        <link rel="stylesheet" href="{{ asset('css/detail-campaign.css') }}">
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
                    {{-- LEFT CONTENT --}}
                    {{-- ========================================================== --}}
                    <div class="detail-main">
                        @php
                            $hariIni = \Carbon\Carbon::today();
                            $mulai = \Carbon\Carbon::parse($campaign->tanggal_mulai)->startOfDay();
                            $akhir = $campaign->tanggal_berakhir
                                ? \Carbon\Carbon::parse($campaign->tanggal_berakhir)->endOfDay()
                                : null;

                            if ($hariIni->lt($mulai)) {
                                $statusHari = 'Mulai dalam ' . $hariIni->diffInDays($mulai) . ' hari';
                                $statusHariClass = 'is-upcoming';
                            } elseif ($akhir === null) {
                                $statusHari = 'Tanpa batas waktu';
                                $statusHariClass = 'is-ongoing';
                            } elseif ($hariIni->gt($akhir)) {
                                $statusHari = 'Campaign berakhir';
                                $statusHariClass = 'is-ended';
                            } else {
                                $sisaHari = (int) $hariIni->diffInDays($akhir);
                                $statusHari = $sisaHari == 0 ? 'Hari terakhir' : $sisaHari . ' Hari lagi';
                                $statusHariClass = $sisaHari <= 3 ? 'is-urgent' : 'is-ongoing';
                            }
                        @endphp

                        <div class="hero-wrapper">
                            <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}"
                                class="campaign-hero-image">
                            <span class="hero-status-badge {{ $statusHariClass }}">
                                <i class="bi bi-clock-fill"></i> {{ $statusHari }}
                            </span>
                        </div>
                        <section class="description-section">
                            <h1>{{ $campaign->judul }}</h1>
                            <div class="rich-text-output">{!! $campaign->deskripsi !!}</div>
                        </section>

                        {{-- ========================================================== --}}
                        {{-- KABAR TERBARU - UPDATE CAMPAIGN --}}
                        {{-- ========================================================== --}}
                        <section class="latest-news-section">

                            <div class="section-header">
                                <h2>
                                    <i class="bi bi-newspaper" style="color: var(--primary); margin-right: 7px;"></i> Kabar Terbaru
                                </h2>

                                <div class="section-actions">
                                    <button class="btn-icon-action" onclick="shareCampaign()" title="Bagikan campaign">
                                        <i class="bi bi-share-fill"></i>
                                    </button>

                                    @auth
                                        @if ($campaign->isOwner(Auth::id()))
                                            <div class="owner-menu">
                                                <button class="btn-icon-action" onclick="toggleOwnerMenu(event)" title="Kelola campaign">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <div class="owner-menu-dropdown" id="ownerMenuDropdown">
                                                    <a href="{{ route('campaign.update.create', $campaign->slug) }}">
                                                        <i class="bi bi-megaphone-fill"></i> Buat Update
                                                    </a>
                                                    <a href="{{ route('campaign.edit', $campaign->id) }}">
                                                        <i class="bi bi-pen"></i> Edit Campaign
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    @endauth
                                </div>
                            </div>

                            @if ($campaign->campaignUpdates->count() > 0)
                                <div class="updates-list">
                                    @foreach ($campaign->campaignUpdates as $update)
                                        <div class="update-card" id="update-{{ $update->id }}">
                                            <div class="update-icon-badge">
                                                <i class="bi bi-megaphone-fill"></i>
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
                                                    {{ Str::limit(strip_tags($update->isi_update), 120) }}
                                                </div>

                                                <button class="btn-read-more" onclick="toggleUpdateDetail({{ $update->id }})">
                                                    <span id="toggleText-{{ $update->id }}">Baca Selengkapnya</span>
                                                    <i class="bi bi-chevron-down" id="toggleIcon-{{ $update->id }}"></i>
                                                </button>

                                                <div class="update-detail-wrapper" id="detailContent-{{ $update->id }}">
                                                    <div class="update-detail-content">
                                                        <div class="update-full-body">
                                                            {!! $update->isi_update !!}
                                                        </div>
                                                    </div>
                                                </div>

                                                @auth
                                                    @if ($campaign->isOwner(Auth::id()))
                                                        <div class="update-footer">
                                                            <a href="{{ route('campaign.update.edit', [$campaign->slug, $update->id]) }}"
                                                                class="btn-edit-update">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </a>
                                                            <form
                                                                action="{{ route('campaign.update.destroy', [$campaign->slug, $update->id]) }}"
                                                                method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn-delete-update"
                                                                    onclick="return confirm('Yakin ingin menghapus update ini?')">
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
                                    <img src="{{ asset('assets/kabar-terbaru.png') }}" alt="Kabar Terbaru" class="icon-section">
                                    <p>Belum ada update dari campaign ini.</p>
                                    @auth
                                        @if ($campaign->isOwner(Auth::id()))
                                            <a href="{{ route('campaign.update.create', $campaign->slug) }}" class="btn-outline">
                                                Buat Update Pertama
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            @endif
                        </section>
                        {{-- ========================================================== --}}
                        {{-- 💬 PESAN DOA DARI DONATUR --}}
                        {{-- ========================================================== --}}
                        <section class="pesan-doa-section">
                            <div class="section-header">
                                <h2>
                                    <i class="bi bi-chat-heart-fill" style="color: var(--primary); margin-right: 7px;"></i> 
                                    Pesan & Doa Donatur
                                </h2>
                                <span class="badge-doa-count">
                                    <i class="bi bi-heart-fill" style="font-size: 10px;"></i>
                                    {{ $pesanDoa->count() }} pesan
                                </span>
                            </div>

                            @if ($pesanDoa->count() > 0)
                                <div class="pesan-doa-grid">
                                    @foreach ($pesanDoa as $doa)
                                        <div class="pesan-doa-card">
                                            <div class="pesan-doa-header">
                                                <div class="pesan-doa-avatar">
                                                    @if (!$doa->is_anonim && $doa->user && $doa->user->foto_profil)
                                                        <img src="{{ asset('storage/' . $doa->user->foto_profil) }}" 
                                                            alt="{{ $doa->nama_donatur }}">
                                                    @else
                                                        <span class="avatar-placeholder">
                                                            {{ $doa->is_anonim ? '🙏' : strtoupper(substr($doa->nama_donatur, 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="pesan-doa-info">
                                                    <strong>{{ $doa->is_anonim ? 'Hamba Allah' : $doa->nama_donatur }}</strong>
                                                    <div>
                                                        <span class="pesan-doa-nominal">
                                                            <i class="bi bi-heart-fill" style="color: #ef4444; font-size: 10px;"></i>
                                                            Rp {{ number_format($doa->nominal, 0, ',', '.') }}
                                                        </span>
                                                        <span class="pesan-doa-time">
                                                            <i class="bi bi-clock"></i>
                                                            {{ $doa->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pesan-doa-body">
                                                <p>"{{ $doa->pesan_doa }}"</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if ($pesanDoa->count() >= 10)
                                    <div class="pesan-doa-footer">
                                        <a href="{{ route('campaign.pesan-doa', $campaign->slug) }}" class="btn-lihat-semua">
                                            <i class="bi bi-chat-dots"></i> Lihat Semua Pesan Doa
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="empty-pesan-doa">
                                <img src="{{ asset('assets/doa.png') }}" alt="Doa" class="icon-section">
                                    <p>Belum ada pesan doa dari donatur.</p>
                                    <small>💡 Jadilah donatur pertama yang meninggalkan pesan doa!</small>
                                </div>
                            @endif
                        </section>

                    </div>

                    {{-- ========================================================== --}}
                    {{-- RIGHT SIDEBAR --}}
                    {{-- ========================================================== --}}
                    <aside class="detail-sidebar">

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
                                <span>{{ $statusHari }}</span>
                            </div>
                            <div class="donate-button-wrapper">
                                <a href="{{ route('donasi.create', $campaign->getRouteSlug()) }}" class="donate-button">Donasi
                                    Sekarang</a>

                            </div>
                        </div>

                        <div class="fundraiser-info-card">
                            <h3>Informasi Penggalang Dana</h3>

                            <a href="{{ route('profil.penggalang', $campaign->penggalangDana->id) }}"
                                class="fundraiser-profile">
                                <img src="{{ asset('storage/' . ($campaign->penggalangDana->foto_profil ?? 'assets/logo-icon.png')) }}"
                                    alt="{{ $campaign->penggalangDana->nama_penggalang }}">
                                <div>
                                    <h4>{{ $campaign->penggalangDana->nama_penggalang ?? 'Orang Baik' }}</h4>
                                </div>
                            </a>
                        </div>

                        {{-- ========================================================== --}}
                        {{-- DONASI TERBARU - HANYA SETTLEMENT --}}
                        {{-- ========================================================== --}}
                        <div class="side-list-card">
                            <h3>Donasi Terbaru</h3>

                            @php
                                // Ambil hanya donasi dengan status settlement
                                $donasiSettlement = $campaign->donasi()
                                    ->whereHas('pembayaran', function ($q) {
                                        $q->where('transaction_status', 'settlement');
                                    })
                                    ->latest()
                                    ->take(5)
                                    ->get();
                            @endphp

                            @if ($donasiSettlement->count() > 0)
                                @foreach ($donasiSettlement as $donasi)
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
                                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                                <h4 style="margin:0;">
                                                    {{ $donasi->is_anonymous ? 'Anonim' : ($donasi->nama_donatur ?? $donasi->user->name ?? 'Donatur') }}
                                                </h4>
                                            </div>
                                            <p>Berdonasi sebesar <b>Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</b></p>
                                            <span>{{ $donasi->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <img src="{{ asset('assets/donasi.png') }}" alt="Donasi Terbaru" class="icon-section">
                                <p class="text-muted">Belum ada donasi yang berhasil.</p>
                            @endif
                        </div>

                        {{-- ========================================================== --}}
                        {{-- FUNDRAISER SECTION --}}
                        {{-- ========================================================== --}}
                        @auth
                            @php
                                $isFundraiser = $campaign->isFundraiser(Auth::id());
                                $routeSlug = $campaign->custom_slug ?? $campaign->slug;
                            @endphp

                            @if (!$isFundraiser)
                                <div class="fundraiser-join-card">
                                    <h3>Jadi Fundraiser</h3>
                                    <img src="{{ asset('assets/fundraiser.png') }}" alt="Fundraiser" class="icon-section">
                                    <p>Bantu sebarkan campaign ini dan dapatkan reward!</p>
                                    <form action="{{ route('fundraiser.store', $routeSlug) }}" method="POST" id="fundraiserForm">
                                        @csrf
                                        <button type="submit" class="btn-fundraiser-join" id="btnJoinFundraiser">
                                            <i class="bi bi-megaphone"></i> Gabung Jadi Fundraiser
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @if ($isFundraiser)
                                @php
                                    $fundraiser = $campaign->getFundraiserByUser(Auth::id());
                                @endphp

                                <div class="fundraiser-referral-card" id="fundraiserReferralCard">
                                    <h4>🔗 Link Referral Anda</h4>
                                    <p>Bagikan link ini untuk mengajak donasi:</p>
                                    <div class="referral-link-box">
                                        <input type="text" id="referralLink" value="{{ $fundraiser->referral_url }}" readonly>
                                        <button onclick="copyReferralLink()" class="btn-copy-link">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>

                                    <div style="display: none;">
                                        <span id="sidebarReferralCode">{{ $fundraiser->referral_code }}</span>
                                    </div>

                                    <small>Setiap donasi melalui link ini akan tercatat atas nama Anda.</small>
                                    <div class="fundraiser-total-donasi">
                                        Terkumpul melalui Anda:
                                        <strong>Rp {{ number_format($fundraiser->total_donasi_settlement, 0, ',', '.') }}</strong>
                                    </div>

                                    <div class="referral-qr-section">
                                        @php
                                            $qrSvg = SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                                                ->size(180)->margin(1)
                                                ->generate($fundraiser->referral_url);
                                        @endphp
                                        <div class="qr-canvas-wrapper" onclick="openFundraiserModalFromSidebar()"
                                            style="cursor: pointer;">
                                            {!! $qrSvg !!}
                                        </div>
                                        <span class="text-hint-light">👆 Klik QR Code untuk lihat detail</span>
                                        <a class="btn-download-qr" id="btnDownloadQr" href="#"
                                            download="qr-referral-{{ $fundraiser->referral_code }}.svg">
                                            <i class="bi bi-download"></i> Download QR Code
                                        </a>
                                    </div>
                                </div>

                                <div class="fundraiser-join-card joined">
                                    <h3>✅ Anda Fundraiser</h3>
                                    <p>Terima kasih telah menjadi fundraiser untuk campaign ini!</p>
                                    <form action="{{ route('fundraiser.destroy', $routeSlug) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-fundraiser-leave"
                                            onclick="return confirm('Yakin ingin berhenti menjadi fundraiser?')">
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

            {{-- Mobile Sticky Donation CTA Bar --}}
            <div class="mobile-sticky-cta">
                <div class="mobile-cta-info">
                    <span class="mobile-cta-label">Terkumpul</span>
                    <strong class="mobile-cta-val">Rp {{ number_format($totalTerkumpul ?? 0, 0, ',', '.') }}</strong>
                </div>
                <a href="{{ route('donasi.create', $campaign->getRouteSlug()) }}" class="btn-mobile-donate">
                    <span>Donasi Sekarang</span>
                    <i class="bi bi-heart-fill"></i>
                </a>

            </div>

        </main>

        {{-- Lightbox --}}
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
        {{-- MODAL POP-UP FUNDRAISER --}}
        {{-- ========================================================== --}}
        <div class="modal-overlay" id="fundraiserModal">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="modal-close" onclick="closeFundraiserModal()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <div class="modal-logo">
                        <img src="{{ asset('favicon.ico') }}" alt="OrangBaik.id">
                        <span>OrangBaik.id</span>
                    </div>
                    <h2>🎉 Selamat!</h2>
                    <p class="subtitle">Anda berhasil menjadi Fundraiser</p>
                </div>
                <div class="modal-body">
                    <div class="success-badge">
                        <i class="bi bi-check-circle-fill"></i>
                        Fundraiser Activated
                    </div>

                    <div class="fundraiser-info-modal">
                        @auth
                            <img src="{{ asset('storage/' . (Auth::user()->foto_profil ?? 'assets/logo-icon.png')) }}"
                                alt="Profile" class="fundraiser-avatar-modal">
                            <div class="fundraiser-name-modal">
                                {{ Auth::user()->name }}
                                <small>Fundraiser untuk campaign ini</small>
                            </div>
                        @endauth
                    </div>

                    <hr class="modal-divider">

                    <div class="referral-section-title">
                        <i class="bi bi-link-45deg"></i> Link Referral Anda
                    </div>
                    <div class="referral-link-box-modal">
                        <input type="text" id="modalReferralLink" value="" readonly>
                        <button onclick="copyModalReferralLink()" class="btn-copy-link-modal">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <small style="display:block; margin-bottom: 16px; color: #64748b; font-size: 12px;">
                        <i class="bi bi-info-circle"></i> Bagikan link ini untuk mengajak donasi
                    </small>

                    <div class="referral-section-title">
                        <i class="bi bi-tag"></i> Kode Referral Anda
                    </div>
                    <div class="referral-code-box">
                        <span class="code-label">Kode</span>
                        <span class="code-value" id="modalReferralCode">-</span>
                        <button class="btn-copy-code" onclick="copyModalReferral()">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>

                    <div class="referral-section-title">
                        <i class="bi bi-qr-code"></i> QR Code Referral
                    </div>
                    <div class="modal-qr-wrapper" onclick="openQRFullscreen()" style="cursor: pointer;">
                        <div id="modalQrContainer">
                            <!-- QR Code akan diisi oleh JavaScript -->
                        </div>
                        <span class="text-hint-light">👆 Klik QR Code untuk lihat detail</span>
                    </div>

                    <div class="modal-actions">
                        <button class="btn-modal-secondary" onclick="closeFundraiserModal()">
                            <i class="bi bi-x-circle"></i> Tutup
                        </button>
                        <button class="btn-modal-primary" onclick="shareReferralLink()">
                            <i class="bi bi-share-fill"></i> Bagikan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- MODAL QR CODE FULLSCREEN --}}
        {{-- ========================================================== --}}
        <div class="modal-qr-overlay" id="qrFullscreenOverlay">
            <div class="modal-qr-fullscreen">
                <button class="modal-qr-close" onclick="closeQRFullscreen()">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="modal-qr-fullscreen-content">
                    <div id="qrFullscreenContainer"></div>
                    <h3>QR Code Referral Anda</h3>
                    <p>Minta orang lain scan QR ini untuk berdonasi lewat link referral Anda.</p>
                    <div class="modal-qr-actions">
                        <button class="btn-download-qr-modal" id="btnDownloadQrFullscreen">
                            <i class="bi bi-download"></i> Download
                        </button>
                        <button class="btn-share-qr-modal" onclick="shareReferralLink()">
                            <i class="bi bi-share-fill"></i> Bagikan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast Notification --}}
        <div class="toast-notification" id="toastNotification">
            <i class="bi bi-check-circle-fill"></i>
            <span id="toastMessage">Berhasil disalin!</span>
        </div>

        {{-- ========================================================== --}}
        {{-- SCRIPTS --}}
        {{-- ========================================================== --}}
        <script>
            // ============================================================
            // SHARE CAMPAIGN
            // ============================================================
            function shareCampaign() {
                if (navigator.share) {
                    navigator.share({
                        title: '{{ $campaign->judul }}',
                        text: 'Dukung campaign ini di OrangBaik.id',
                        url: window.location.href
                    }).catch(() => { });
                } else {
                    const url = window.location.href;
                    navigator.clipboard.writeText(url).then(() => {
                        alert('Link campaign berhasil disalin!');
                    }).catch(() => {
                        prompt('Salin link ini:', url);
                    });
                }
            }

            // ============================================================
            // COPY REFERRAL LINK (dari sidebar)
            // ============================================================
            function copyReferralLink() {
                const input = document.getElementById('referralLink');
                if (!input) return;
                input.select();
                document.execCommand('copy');
                showToast('Link referral berhasil disalin!');
            }

            // ============================================================
            // OWNER "KELOLA" DROPDOWN MENU
            // ============================================================
            function toggleOwnerMenu(e) {
                e.stopPropagation();
                const dropdown = document.getElementById('ownerMenuDropdown');
                if (!dropdown) return;
                dropdown.classList.toggle('open');
            }

            document.addEventListener('click', function (e) {
                const dropdown = document.getElementById('ownerMenuDropdown');
                if (!dropdown) return;
                const menu = dropdown.closest('.owner-menu');
                if (menu && !menu.contains(e.target)) {
                    dropdown.classList.remove('open');
                }
            });

            // ============================================================
            // TOGGLE UPDATE DETAIL
            // ============================================================
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

            // ============================================================
            // LIGHTBOX
            // ============================================================
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
                    dot.onclick = function (e) {
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
            document.addEventListener('DOMContentLoaded', function () {
                const wrapper = document.querySelector('.lightbox-image-wrapper');
                if (wrapper) {
                    wrapper.addEventListener('touchstart', function (e) {
                        touchStartX = e.changedTouches[0].screenX;
                    }, { passive: true });
                    wrapper.addEventListener('touchend', function (e) {
                        touchEndX = e.changedTouches[0].screenX;
                        const diff = touchStartX - touchEndX;
                        if (Math.abs(diff) > 50) {
                            changeImage(diff > 0 ? 1 : -1);
                        }
                    }, { passive: true });
                }
            });

            document.getElementById('lightboxOverlay').addEventListener('click', function (e) {
                if (e.target === this) closeLightbox();
            });

            // ============================================================
            // AUTO-WIRE LIGHTBOX UNTUK GAMBAR DI KONTEN
            // (deskripsi campaign & isi update masing-masing jadi galeri sendiri)
            // ============================================================
            document.addEventListener('DOMContentLoaded', function () {
                function wireLightboxImages(containerSelector) {
                    document.querySelectorAll(containerSelector).forEach(function (container) {
                        const imgs = Array.from(container.querySelectorAll('img'));
                        if (!imgs.length) return;
                        const srcs = imgs.map(img => img.src);
                        imgs.forEach(function (img) {
                            img.addEventListener('click', function () {
                                openLightbox(img.src, srcs);
                            });
                        });
                    });
                }

                wireLightboxImages('.rich-text-output');
                wireLightboxImages('.update-full-body');
            });

            // ============================================================
            // TOAST NOTIFICATION
            // ============================================================
            function showToast(message) {
                const toast = document.getElementById('toastNotification');
                const toastMsg = document.getElementById('toastMessage');
                toastMsg.textContent = message;
                toast.classList.add('show');
                clearTimeout(toast._timeout);
                toast._timeout = setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }

            // ============================================================
            // MODAL FUNDRAISER - CORE FUNCTIONS
            // ============================================================
            let modalReferralCode = '';
            let modalReferralUrl = '';

            function openFundraiserModal(referralCode, referralUrl, qrSvg) {
                modalReferralCode = referralCode || '';
                modalReferralUrl = referralUrl || '';

                const referralLinkInput = document.getElementById('modalReferralLink');
                if (referralLinkInput) {
                    referralLinkInput.value = referralUrl || '';
                }

                const referralCodeElement = document.getElementById('modalReferralCode');
                if (referralCodeElement) {
                    referralCodeElement.textContent = referralCode || '-';
                }

                const qrContainer = document.getElementById('modalQrContainer');
                if (qrContainer && qrSvg) {
                    qrContainer.innerHTML = qrSvg;
                }

                const modal = document.getElementById('fundraiserModal');
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeFundraiserModal() {
                const modal = document.getElementById('fundraiserModal');
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }

            // ============================================================
            // OPEN MODAL FROM SIDEBAR QR
            // ============================================================
            function openFundraiserModalFromSidebar() {
                const referralLinkInput = document.getElementById('referralLink');
                if (!referralLinkInput) {
                    showToast('Data referral tidak ditemukan');
                    return;
                }

                const referralUrl = referralLinkInput.value;

                let referralCode = '';
                const codeElement = document.getElementById('sidebarReferralCode');
                if (codeElement) {
                    referralCode = codeElement.textContent;
                } else {
                    try {
                        const urlParams = new URLSearchParams(new URL(referralUrl).search);
                        referralCode = urlParams.get('ref') || 'REF-' + Math.random().toString(36).substring(2, 10).toUpperCase();
                    } catch (e) {
                        referralCode = 'REF-' + Math.random().toString(36).substring(2, 10).toUpperCase();
                    }
                }

                const qrWrapper = document.querySelector('.qr-canvas-wrapper');
                if (!qrWrapper) {
                    showToast('QR Code tidak ditemukan');
                    return;
                }

                const qrSvg = qrWrapper.innerHTML;
                if (!qrSvg || qrSvg.trim() === '') {
                    showToast('QR Code tidak ditemukan');
                    return;
                }

                openFundraiserModal(referralCode, referralUrl, qrSvg);
            }

            // ============================================================
            // QR CODE FULLSCREEN (dari dalam modal fundraiser)
            // ============================================================
            function openQRFullscreen() {
                const qrContainer = document.getElementById('modalQrContainer');
                const overlay = document.getElementById('qrFullscreenOverlay');
                const fullscreenContainer = document.getElementById('qrFullscreenContainer');
                if (!qrContainer || !overlay || !fullscreenContainer) return;

                const qrSvg = qrContainer.innerHTML;
                if (!qrSvg || qrSvg.trim() === '') {
                    showToast('QR Code belum tersedia');
                    return;
                }

                fullscreenContainer.innerHTML = qrSvg;
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';

                const btnDownload = document.getElementById('btnDownloadQrFullscreen');
                if (btnDownload) {
                    btnDownload.onclick = function () {
                        const svg = fullscreenContainer.querySelector('svg');
                        if (!svg) return;
                        const blob = new Blob([svg.outerHTML], { type: 'image/svg+xml' });
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = 'qr-referral-' + (modalReferralCode || 'orangbaik') + '.svg';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    };
                }
            }

            function closeQRFullscreen() {
                const overlay = document.getElementById('qrFullscreenOverlay');
                if (overlay) {
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }

            // ============================================================
            // COPY FUNCTIONS
            // ============================================================
            function copyModalReferralLink() {
                const input = document.getElementById('modalReferralLink');
                input.select();
                document.execCommand('copy');
                showToast('Link referral berhasil disalin!');
            }

            function copyModalReferral() {
                const code = document.getElementById('modalReferralCode').textContent;
                navigator.clipboard.writeText(code).then(() => {
                    showToast('Kode referral berhasil disalin!');
                }).catch(() => {
                    const input = document.createElement('input');
                    input.value = code;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    document.body.removeChild(input);
                    showToast('Kode referral berhasil disalin!');
                });
            }

            // ============================================================
            // SHARE REFERRAL LINK
            // ============================================================
            function shareReferralLink() {
                if (navigator.share) {
                    navigator.share({
                        title: 'Donasi Campaign di OrangBaik.id',
                        text: 'Yuk donasi! Gunakan referral code saya: ' + modalReferralCode,
                        url: modalReferralUrl
                    }).catch(() => { });
                } else {
                    navigator.clipboard.writeText(modalReferralUrl).then(() => {
                        showToast('Link referral berhasil disalin!');
                    }).catch(() => {
                        prompt('Salin link ini:', modalReferralUrl);
                    });
                }
            }

            // ============================================================
            // KEYBOARD SHORTCUTS
            // ============================================================
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    const qrOverlay = document.getElementById('qrFullscreenOverlay');
                    const fundraiserModal = document.getElementById('fundraiserModal');
                    const lightboxOverlay = document.getElementById('lightboxOverlay');

                    if (qrOverlay && qrOverlay.classList.contains('active')) {
                        closeQRFullscreen();
                    } else if (fundraiserModal && fundraiserModal.classList.contains('active')) {
                        closeFundraiserModal();
                    } else if (lightboxOverlay && lightboxOverlay.classList.contains('active')) {
                        closeLightbox();
                    }
                }
            });

            // Click outside to close
            document.addEventListener('DOMContentLoaded', function () {
                const fundraiserModal = document.getElementById('fundraiserModal');
                if (fundraiserModal) {
                    fundraiserModal.addEventListener('click', function (e) {
                        if (e.target === this) {
                            closeFundraiserModal();
                        }
                    });
                }

                const qrOverlay = document.getElementById('qrFullscreenOverlay');
                if (qrOverlay) {
                    qrOverlay.addEventListener('click', function (e) {
                        if (e.target === this) {
                            closeQRFullscreen();
                        }
                    });
                }
            });

            // ============================================================
            // DOWNLOAD QR CODE (dari sidebar)
            // ============================================================
            document.addEventListener('DOMContentLoaded', function () {
                const btn = document.getElementById('btnDownloadQr');
                if (!btn) return;
                const svg = btn.closest('.referral-qr-section').querySelector('svg');
                if (!svg) return;
                const blob = new Blob([svg.outerHTML], { type: 'image/svg+xml' });
                btn.href = URL.createObjectURL(blob);
            });

            // ============================================================
            // TAMPILKAN MODAL DARI SERVER
            // ============================================================
            document.addEventListener('DOMContentLoaded', function () {
                @if (session('show_fundraiser_modal') && session('referral_code'))
                    try {
                        const qrContainer = document.getElementById('modalQrContainer');
                        if (!qrContainer) return;

                        const qrSvg = `{!! SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->margin(1)->generate(session('referral_url')) !!}`;
                        qrContainer.innerHTML = qrSvg;

                        setTimeout(function () {
                            openFundraiserModal(
                                '{{ session('referral_code') }}',
                                '{{ session('referral_url') }}',
                                qrSvg
                            );
                        }, 500);

                    } catch (error) {
                        console.error('Error showing modal:', error);
                    }
                @endif
            });
        </script>

    </body>

    </html>