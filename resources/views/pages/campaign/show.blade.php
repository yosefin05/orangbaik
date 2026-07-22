<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Campaign - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail-campaign.css') }}">
</head>

<body>

    @include('components.header')

    <main class="campaign-detail-page">

        {{-- Alert --}}
        @if (session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="container detail-container">

            <div class="detail-layout">

                {{-- ========================================================== --}}
                {{-- LEFT CONTENT                                               --}}
                {{-- ========================================================== --}}
                <div class="detail-main">

                    {{-- Thumbnail --}}
                    <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}"
                        class="campaign-hero-image">

                    {{-- Deskripsi --}}
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
                                        <i class="bi bi-plus-circle"></i>
                                        Tambah Update
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
                                        {{-- Gambar Thumbnail --}}
                                        <div class="update-thumbnail">
                                            <img src="{{ $thumbnailImage }}" alt="{{ $update->judul_update }}">
                                        </div>

                                        <div class="update-content-wrapper">
                                            {{-- Judul --}}
                                            <h3 class="update-title">{{ $update->judul_update }}</h3>

                                            {{-- Tanggal --}}
                                            <div class="update-meta">
                                                <span class="update-date">
                                                    <i class="bi bi-calendar3"></i>
                                                    {{ $update->created_at->translatedFormat('d F Y') }}
                                                </span>
                                            </div>

                                            {{-- Preview Isi --}}
                                            <div class="update-preview-text">
                                                {{ Str::limit($update->isi_update, 120) }}
                                            </div>

                                            {{-- Tombol Baca Selengkapnya --}}
                                            <button class="btn-read-more" onclick="openUpdateModal({{ $update->id }})">
                                                <span>Baca Selengkapnya</span>
                                                <i class="bi bi-chevron-down"></i>
                                            </button>

                                            {{-- Tombol Hapus (untuk owner) --}}
                                            @auth
                                                @if ($campaign->isOwner(Auth::id()))
                                                    <div class="update-footer">
                                                        <form action="{{ route('campaign.update.destroy', [$campaign->slug, $update->id]) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-delete-update" onclick="return confirm('Yakin ingin menghapus update ini?')">
                                                                <i class="bi bi-trash"></i>
                                                                Hapus
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

                    {{-- Fundraiser --}}
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
                                        <i class="bi bi-megaphone"></i>
                                        Gabung Jadi Fundraiser
                                    </button>
                                </form>
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
                                        <i class="bi bi-x-circle"></i>
                                        Berhenti
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

        {{-- ========================================================== --}}
        {{-- MODAL DETAIL UPDATE                                        --}}
        {{-- ========================================================== --}}
        <div class="update-modal" id="updateModal">
            <div class="update-modal-overlay" onclick="closeUpdateModal()"></div>
            <div class="update-modal-content">
                <button class="update-modal-close" onclick="closeUpdateModal()">
                    <i class="bi bi-x-lg"></i>
                </button>

                <div id="updateModalBody">
                    {{-- Konten akan diisi oleh JavaScript --}}
                </div>
            </div>
        </div>

    </main>

    @include('components.footer')

    @push('scripts')
    <script>
        // Data dari controller
        const updatesData = @json($updatesData);
        const isOwner = {{ auth()->check() && $campaign->isOwner(Auth::id()) ? 'true' : 'false' }};
        const slug = '{{ $campaign->slug }}';

        const modal = document.getElementById('updateModal');
        const modalBody = document.getElementById('updateModalBody');

        function openUpdateModal(id) {
            const update = updatesData.find(u => u.id === id);
            if (!update) return;

            modalBody.innerHTML = `
                <div class="update-detail">
                    <h2 class="update-detail-title">${update.judul}</h2>
                    <div class="update-detail-meta">
                        <span><i class="bi bi-calendar3"></i> ${update.tanggal}</span>
                    </div>
                    <div class="update-detail-body">
                        ${update.isi.replace(/\n/g, '<br>')}
                    </div>
                    ${update.gambar.length > 0 ? `
                        <div class="update-detail-gallery">
                            ${update.gambar.map(img => `
                                <div class="update-detail-gallery-item">
                                    <img src="${img}" alt="Gambar update">
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                    ${isOwner ? `
                        <div class="update-detail-footer">
                            <form action="/campaign/${slug}/update/${update.id}" method="POST" onsubmit="return confirm('Yakin ingin menghapus update ini?')">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn-delete-update">
                                    <i class="bi bi-trash"></i>
                                    Hapus Update
                                </button>
                            </form>
                        </div>
                    ` : ''}
                </div>
            `;

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeUpdateModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUpdateModal();
            }
        });
    </script>
    @endpush

</body>

</html>