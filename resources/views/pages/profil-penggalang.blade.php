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
                        @php
                            $canCreateCampaign = auth()->user()->penggalangDana?->status === 'approved';
                        @endphp
                        @if ($canCreateCampaign)
                            <a href="{{ route('campaign.create') }}" class="dashboard-link">
                                Tambahkan Campaign
                            </a>
                        @endif
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
                        @forelse ($penggalang->campaign as $campaign)

                            @php
                                $terkumpul = $campaign->donasi->sum('nominal');
                                $persen = $campaign->target_donasi
                                    ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
                                    : 0;
                            @endphp

                            <div class="campaign-row">

                                <a href="{{ route('campaign.show', $campaign->slug) }}" class="campaign-row-image-link">
                                    <img src="{{ $campaign->thumbnail
                                        ? asset('storage/' . $campaign->thumbnail)
                                        : asset('assets/slide1.png') }}"
                                        alt="{{ $campaign->judul }}"
                                        class="campaign-row-image"
                                    />
                                </a>

                                <div class="campaign-row-body">

                                    <div class="campaign-row-header">

                                        <h3>{{ $campaign->judul }}</h3>

                                        @if ($isOwner)
                                            <div class="campaign-actions">

                                                {{-- ========================================= --}}
                                                {{-- TOMBOL UPDATE KABAR TERBARU               --}}
                                                {{-- ========================================= --}}
                                                <a href="{{ route('campaign.update.create', $campaign->slug) }}"
                                                    class="campaign-action update"
                                                    title="Buat Update Kabar Terbaru">
                                                    <i class="bi bi-megaphone-fill"></i>
                                                </a>

                                                {{-- TOMBOL EDIT --}}
                                                <a href="{{ route('campaign.edit', $campaign->id) }}"
                                                    class="campaign-action edit"
                                                    title="Edit Campaign">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>

                                                {{-- TOMBOL HAPUS --}}
                                                <form action="{{ route('campaign.destroy', $campaign->id) }}" method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus campaign ini?')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="campaign-action delete" title="Hapus Campaign">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>

                                                </form>

                                            </div>
                                        @endif

                                    </div>

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
                                        <div class="progress-fill" style="width: {{ $persen }}%;"></div>
                                    </div>

                                    <div class="campaign-meta">
                                        <span>
                                            Status:
                                            {{ ucfirst($campaign->status) }}
                                        </span>

                                        @if ($campaign->tanggal_berakhir)
                                            <span>
                                                {{ \Carbon\Carbon::parse($campaign->tanggal_berakhir)->format('d M Y') }}
                                            </span>
                                        @endif
                                    </div>

                                </div>

                            </div>

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