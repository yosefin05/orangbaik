<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Penggalang Dana - Orang Baik</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profil-penggalang.css') }}">
</head>

<body>
    {{-- TOPBAR (mobile saja): jadi solid setelah banner di-scroll --}}
    <div class="profile-topbar" id="profileTopbar">
        <button class="profile-topbar-back" type="button" aria-label="Kembali" onclick="window.history.back()">
            <i class="bi bi-chevron-left"></i>
        </button>
        <p class="profile-topbar-title">{{ $penggalang->nama_penggalang }}</p>
    </div>

    <main class="fundraiser-profile-page">

        {{-- BANNER --}}
        <section class="fundraiser-hero">
            <div class="fundraiser-container">
                <div class="banner-wrapper">
                    <button class="fundraiser-back" type="button" aria-label="Kembali" onclick="window.history.back()">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M15 18L9 12L15 6" />
                        </svg>
                    </button>

                    <img
                        src="{{ $penggalang->thumbnail
                            ? asset('storage/' . $penggalang->thumbnail)
                            : asset('assets/profile-banner.png') }}"
                        alt="{{ $penggalang->nama_penggalang }}"
                        class="penggalang-banner"
                        width="1200"
                        height="250"
                        loading="eager"
                        decoding="async">
                </div>
            </div>
        </section>

        <section class="fundraiser-content">
            <div class="fundraiser-container">

                {{-- KARTU PROFIL --}}
                @php
                    $canCreateCampaign = auth()->user()?->penggalangDana?->status === 'approved';
                @endphp

                <div class="penggalang-summary">
                    <div class="penggalang-left">
                        <div class="logo-wrapper">
                            <img
                                src="{{ $penggalang->foto_profil
                                    ? asset('storage/' . $penggalang->foto_profil)
                                    : asset('assets/logo-icon.png') }}"
                                alt="{{ $penggalang->nama_penggalang }}"
                                class="penggalang-logo"
                                width="72"
                                height="72"
                                loading="eager"
                                decoding="async">
                        </div>

                        <div class="penggalang-title">
                            <h1>{{ $penggalang->nama_penggalang }}</h1>

                            <div class="penggalang-meta">
                                @if($penggalang->status !== 'approved')
                                    <span class="status-badge {{ $penggalang->status ?? 'pending' }}">
                                        {{ ucfirst($penggalang->status ?? 'Pending') }}
                                    </span>
                                @endif

                                @if($penggalang->jenis_penggalang == 'organisasi' && $penggalang->verified)
                                    <span class="jenis-badge">
                                        <i class="bi bi-patch-check-fill"></i> Verified Organization
                                    </span>
                                @endif

                                <span class="meta-item">
                                    <i class="bi bi-person-badge"></i>
                                    {{ ucfirst($penggalang->jenis_penggalang) }}
                                </span>

                                <span class="meta-item">
                                    <i class="bi bi-megaphone"></i>
                                    {{ $penggalang->campaign->count() }} campaign
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="penggalang-actions">
                        @if($canCreateCampaign)
                            <a href="{{ route('campaign.create') }}" class="profile-btn profile-btn--primary">
                                <i class="bi bi-plus-lg"></i> Tambahkan Campaign
                            </a>

                            <a href="#" class="profile-btn">
                                <i class="bi bi-bar-chart-line"></i> Dashboard
                            </a>
                        @endif

                        <a href="{{ route('penggalang_dana.edit', $penggalang->id) }}" class="profile-btn">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                    </div>
                </div>

                <div class="profile-layout">

                    {{-- ================= KOLOM UTAMA ================= --}}
                    <div class="profile-main">

                        {{-- CAMPAIGN --}}
                        <section class="fundraiser-campaign-section">
                            <div class="section-head">
                                <h2>Penggalangan Dana</h2>
                                <span class="section-count">{{ $penggalang->campaign->count() }}</span>
                            </div>

                            <div class="campaign-list">
                                @forelse ($penggalang->campaign as $campaign)
                                    @php
                                        $terkumpul = $campaign->donasi->sum('nominal');
                                        $persen = $campaign->target_donasi
                                            ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                            : 0;
                                    @endphp

                                    <article class="campaign-row">
                                        <a href="{{ route('campaign.show', $campaign->slug) }}" class="campaign-row-image-link" tabindex="-1" aria-hidden="true">
                                            <img
                                                src="{{ $campaign->thumbnail
                                                    ? asset('storage/' . $campaign->thumbnail)
                                                    : asset('assets/slide1.png') }}"
                                                alt=""
                                                class="campaign-row-image"
                                                width="520"
                                                height="293"
                                                loading="lazy"
                                                decoding="async">
                                        </a>

                                        <div class="campaign-row-body">
                                            <div class="campaign-row-header">
                                                <h3>
                                                    <a href="{{ route('campaign.show', $campaign->slug) }}">{{ $campaign->judul }}</a>
                                                </h3>
                                            </div>

                                            <div>
                                                <div class="campaign-progress" role="progressbar"
                                                    aria-valuenow="{{ round($persen) }}" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-fill" style="width: {{ $persen }}%;"></div>
                                                </div>
                                            </div>

                                            <div class="campaign-row-amount">
                                                <strong>Rp{{ number_format($terkumpul, 0, ',', '.') }}</strong>
                                                <span>dari Rp{{ number_format($campaign->target_donasi, 0, ',', '.') }} · {{ round($persen) }}%</span>
                                            </div>

                                            <div class="campaign-footer">
                                                <div class="campaign-meta">
                                                    <span class="campaign-status">{{ ucfirst($campaign->status) }}</span>

                                                    @if ($campaign->tanggal_berakhir)
                                                        <span>{{ \Carbon\Carbon::parse($campaign->tanggal_berakhir)->format('d M Y') }}</span>
                                                    @endif
                                                </div>

                                                @if ($isOwner)
                                                    <div class="campaign-actions">
                                                        <a href="{{ route('campaign.update.create', $campaign->slug) }}"
                                                            class="campaign-action update"
                                                            title="Buat Update Kabar Terbaru"
                                                            aria-label="Buat Update">
                                                            <i class="bi bi-megaphone-fill"></i>
                                                        </a>

                                                        <a href="{{ route('campaign.edit', $campaign->id) }}"
                                                            class="campaign-action edit"
                                                            title="Edit Campaign"
                                                            aria-label="Edit Campaign">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>

                                                        <form action="{{ route('campaign.destroy', $campaign->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Yakin ingin menghapus campaign ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="campaign-action delete"
                                                                title="Hapus Campaign"
                                                                aria-label="Hapus Campaign">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @empty
                                    <div class="campaign-empty">
                                        <p>Belum ada campaign.</p>
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        {{-- TENTANG --}}
                        <details class="info-card" open>
                            <summary>
                                <span>Tentang Penggalang</span>
                                <b><i class="bi bi-chevron-down"></i></b>
                            </summary>
                            <div class="paragraph-content">
                                <p>{!! nl2br(e($penggalang->deskripsi)) !!}</p>
                            </div>
                        </details>

                        {{-- VISI MISI --}}
                        <details class="info-card" open>
                            <summary>
                                <span>Visi Misi</span>
                                <b><i class="bi bi-chevron-down"></i></b>
                            </summary>
                            <div class="paragraph-content">
                                <h3>Visi</h3>
                                <p class="visi-text">{{ $penggalang->visi }}</p>

                                <h3>Misi</h3>
                                @php
                                    $misiRaw = trim((string) $penggalang->misi);
                                    $misiItems = [];

                                    foreach (preg_split('/\R+/', $misiRaw) as $line) {
                                        // Pisah hanya pada "-" atau "•" di awal baris / setelah spasi DAN diikuti spasi.
                                        // Hyphen dalam kata ("tri-dharma") tidak ikut terpecah.
                                        foreach (preg_split('/(?:^|\s)[-•]\s+/u', $line, -1, PREG_SPLIT_NO_EMPTY) as $item) {
                                            $item = trim($item);
                                            if ($item !== '') {
                                                $misiItems[] = $item;
                                            }
                                        }
                                    }

                                    $misiAsList = count($misiItems) > 1 || preg_match('/^[-•]\s/u', $misiRaw);
                                @endphp

                                @if($misiAsList)
                                    <ul class="misi-list">
                                        @foreach($misiItems as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @elseif($misiRaw !== '')
                                    <p>{!! nl2br(e($penggalang->misi)) !!}</p>
                                @endif
                            </div>
                        </details>
                    </div>

                    {{-- ================= SIDEBAR ================= --}}
                    <aside class="profile-side">

                        <details class="info-card" open>
                            <summary>
                                <span>Informasi Penggalang</span>
                                <b><i class="bi bi-chevron-down"></i></b>
                            </summary>
                            <div class="info-table">
                                <div class="info-row">
                                    <strong class="info-label">Nama Penggalang</strong>
                                    <p class="info-value">{{ $penggalang->nama_penggalang }}</p>
                                </div>
                                <div class="info-row">
                                    <strong class="info-label">Jenis</strong>
                                    <p class="info-value">{{ ucfirst($penggalang->jenis_penggalang) }}</p>
                                </div>
                                @if($penggalang->tahun_berdiri)
                                    <div class="info-row">
                                        <strong class="info-label">Tahun Berdiri</strong>
                                        <p class="info-value">{{ $penggalang->tahun_berdiri }}</p>
                                    </div>
                                @endif
                                <div class="info-row">
                                    <strong class="info-label">Lokasi</strong>
                                    <p class="info-value">{{ $penggalang->alamat }}</p>
                                </div>
                            </div>
                        </details>

                        @php
                            $docsToShow = $legalitasDokumen ?? $penggalang->penggalangDanaDokumen;
                        @endphp
                        @if(($penggalang->jenis_penggalang === 'organisasi' && $docsToShow->isNotEmpty()) || $isOwner)
                            <details class="info-card" open>
                                <summary>
                                    <span>Informasi Legalitas {{ $penggalang->jenis_penggalang === 'organisasi' ? 'Organisasi' : '(Privat Pemilik)' }}</span>
                                    <b><i class="bi bi-chevron-down"></i></b>
                                </summary>
                                @if($docsToShow->isNotEmpty())
                                    <div class="info-table">
                                        @foreach($docsToShow as $dokumen)
                                            <div class="info-row">
                                                <strong class="info-label">{{ $dokumen->nama_dokumen }}</strong>
                                                <p class="info-value">
                                                    <a href="{{ route('dokumen.show', $dokumen->id) }}" target="_blank" rel="noopener noreferrer">
                                                        <i class="bi bi-file-earmark-text"></i> Lihat Dokumen
                                                    </a>
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="info-empty">Belum ada dokumen legalitas.</p>
                                @endif
                            </details>
                        @endif

                        <details class="info-card" open>
                            <summary>
                                <span>Kontak & Sosial Media</span>
                                <b><i class="bi bi-chevron-down"></i></b>
                            </summary>
                            <div class="info-table">
                                <div class="info-row">
                                    <strong class="info-label">Email</strong>
                                    <p class="info-value">
                                        <a href="mailto:{{ $penggalang->email }}">{{ $penggalang->email }}</a>
                                    </p>
                                </div>
                                <div class="info-row">
                                    <strong class="info-label">Hotline</strong>
                                    <p class="info-value">{{ $penggalang->no_telepon }}</p>
                                </div>
                                @if($penggalang->instagram)
                                    <div class="info-row">
                                        <strong class="info-label">Instagram</strong>
                                        <p class="info-value">{{ '@' . $penggalang->instagram }}</p>
                                    </div>
                                @endif
                                @if($penggalang->facebook)
                                    <div class="info-row">
                                        <strong class="info-label">Facebook</strong>
                                        <p class="info-value">{{ $penggalang->facebook }}</p>
                                    </div>
                                @endif
                                @if($penggalang->youtube)
                                    <div class="info-row">
                                        <strong class="info-label">YouTube</strong>
                                        <p class="info-value">{{ $penggalang->youtube }}</p>
                                    </div>
                                @endif
                                @if($penggalang->tiktok)
                                    <div class="info-row">
                                        <strong class="info-label">TikTok</strong>
                                        <p class="info-value">{{ '@' . $penggalang->tiktok }}</p>
                                    </div>
                                @endif
                            </div>
                        </details>
                    </aside>
                </div>
            </div>
        </section>
    </main>

    @include('components.footer')

    <script>
        (function () {
            var bar = document.getElementById('profileTopbar');
            var banner = document.querySelector('.banner-wrapper');
            if (!bar || !banner) return;

            function update() {
                var limit = Math.max(banner.offsetHeight - 64, 40);
                bar.classList.toggle('is-solid', window.scrollY > limit);
            }

            update();
            window.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update);
        })();
    </script>
</body>

</html>