<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Penggalang Dana - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profil-penggalang.css') }}">
</head>

<body>
    <main class="fundraiser-profile-page">

        {{-- HERO --}}
        <section class="fundraiser-hero">
            <div class="fundraiser-container">

                <button class="fundraiser-back" type="button" onclick="history.back()">
                    <svg viewBox="0 0 24 24">
                        <path d="M15 18L9 12L15 6" />
                    </svg>
                    <span>Kembali</span>
                </button>

                <div class="banner-wrapper">
                    <img src="{{ $penggalang->thumbnail
    ? asset('storage/' . $penggalang->thumbnail)
    : asset('assets/profile-banner.png') }}" alt="{{ $penggalang->nama_penggalang }}" class="penggalang-banner">
                </div>

            </div>
        </section>

        <section class="fundraiser-content">
            <div class="fundraiser-container">

                {{-- PROFILE SUMMARY --}}
                <div class="penggalang-summary">

                    <div class="penggalang-left">

                        <div class="logo-wrapper">

                            <img src="{{ $penggalang->foto_profil
    ? asset('storage/' . $penggalang->foto_profil)
    : asset('assets/logo-icon.png') }}" alt="{{ $penggalang->nama_penggalang }}" class="penggalang-logo">

                        </div>

                        <div class="penggalang-title">

                            <h1>{{ $penggalang->nama_penggalang }}</h1>

                            <div class="verified-row">

                                {{-- Badge kecil --}}
                                @if($penggalang->jenis_penggalang == 'organisasi' && $penggalang->verified)

                                    <span class="jenis-badge">
                                        ✓ .org
                                    </span>
                                    <span>Verified Organization</span>

                                @endif
                            </div>

                        </div>

                    </div>

                    <div class="penggalang-actions">

                        <a href="#" class="dashboard-link">
                            Dashboard
                        </a>

                        <a href="{{ route('penggalang_dana.edit', $penggalang->id) }}" class="edit-button">
                            Edit
                        </a>

                    </div>

                </div>
                {{-- DETAIL INFO --}}
                <section class="accordion-list">

                    <details class="info-card" open>
                        <summary>
                            <span>Informasi Penggalang</span>
                            <b>⌄</b>
                        </summary>

                        <div class="info-table">

                            <div>
                                <strong>• Nama Penggalang</strong>
                                <p>{{ $penggalang->nama_penggalang }}</p>
                            </div>

                            <div>
                                <strong>• Jenis</strong>
                                <p>{{ ucfirst($penggalang->jenis_penggalang) }}</p>
                            </div>

                            @if($penggalang->tahun_berdiri)
                                <div>
                                    <strong>• Tahun Berdiri</strong>
                                    <p>{{ $penggalang->tahun_berdiri }}</p>
                                </div>
                            @endif

                            <div>
                                <strong>• Lokasi</strong>
                                <p>{{ $penggalang->alamat }}</p>
                            </div>

                        </div>
                    </details>

                    <details class="info-card" open>
                        <summary>
                            <span>Tentang Penggalang</span>
                            <b>⌄</b>
                        </summary>

                        <div class="paragraph-content">
                            {!! nl2br(e($penggalang->deskripsi)) !!}
                        </div>
                    </details>

                    <details class="info-card" open>
                        <summary>
                            <span>Visi Misi</span>
                            <b>⌄</b>
                        </summary>

                        <div class="paragraph-content">

                            <h3>Visi</h3>
                            <p>{{ $penggalang->visi }}</p>

                            <h3>Misi</h3>
                            <p>{!! nl2br(e($penggalang->misi)) !!}</p>

                        </div>
                    </details>

                    <details class="info-card" open>
                        <summary>
                            <span>Informasi Legalitas</span>
                            <b>⌄</b>
                        </summary>

                        <div class="info-table">

                            @forelse($penggalang->penggalangDanaDokumen as $dokumen)

                                <div>
                                    <strong>• {{ $dokumen->nama_dokumen }}</strong>
                                    <p>
                                        <a href="{{ $dokumen->file_dokumen }}" target="_blank">
                                            Lihat Dokumen
                                        </a>
                                    </p>
                                </div>

                            @empty
                                <p>Belum ada dokumen legalitas.</p>
                            @endforelse

                        </div>
                    </details>

                    <details class="info-card" open>
                        <summary>
                            <span>Kontak & Sosial Media</span>
                            <b>⌄</b>
                        </summary>

                        <div class="info-table">

                            <div>
                                <strong>• Email</strong>
                                <p>{{ $penggalang->email }}</p>
                            </div>

                            <div>
                                <strong>• Hotline</strong>
                                <p>{{ $penggalang->no_telepon }}</p>
                            </div>

                            @if($penggalang->instagram)
                                <div>
                                    <strong>• Instagram</strong>
                                    <p>{{ '@' . $penggalang->instagram }}</p>
                                </div>
                            @endif

                            @if($penggalang->facebook)
                                <div>
                                    <strong>• Facebook</strong>
                                    <p>{{ $penggalang->facebook }}</p>
                                </div>
                            @endif

                            @if($penggalang->youtube)
                                <div>
                                    <strong>• Youtube</strong>
                                    <p>{{ $penggalang->youtube }}</p>
                                </div>
                            @endif

                            @if($penggalang->tiktok)
                                <div>
                                    <strong>• TikTok</strong>
                                    <p>{{ '@' . $penggalang->tiktok }}</p>
                                </div>
                            @endif

                        </div>
                    </details>

                </section>

                {{-- CAMPAIGN LIST --}}
                <section class="fundraiser-campaign-section">

                    <h2>Penggalangan Dana</h2>

                    <div class="campaign-list">

                        @forelse($penggalang->campaign as $campaign)

                                            <article class="campaign-row">

                                                <img src="{{ $campaign->thumbnail
                            ? asset('storage/' . $campaign->thumbnail)
                            : asset('assets/slide1.png') }}" alt="{{ $campaign->judul }}" class="campaign-row-image">

                                                <div class="campaign-row-body">

                                                    <h3>{{ $campaign->judul }}</h3>

                                                    <p>
                                                        {{ $penggalang->nama_penggalang }}
                                                        <span>●</span>
                                                    </p>

                                                    <div class="campaign-row-amount">
                                                        <strong>
                                                            Rp{{ number_format($campaign->target_donasi, 0, ',', '.') }}
                                                        </strong>
                                                        <span>Target</span>
                                                    </div>

                                                    <div class="campaign-progress">
                                                        <div></div>
                                                    </div>

                                                    <div class="campaign-meta">
                                                        <span>Status: {{ $campaign->status }}</span>

                                                        @if($campaign->tanggal_berakhir)
                                                            <span>
                                                                {{ \Carbon\Carbon::parse($campaign->tanggal_berakhir)->format('d M Y') }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                </div>

                                            </article>

                        @empty

                            <p>Belum ada campaign.</p>

                        @endforelse

                    </div>

                </section>

            </div>
        </section>

    </main>

    @include('components.footer')

</body>

</html>