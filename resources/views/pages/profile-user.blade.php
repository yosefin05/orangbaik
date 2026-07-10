<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile-user.css') }}">
</head>
<x-logout-modal/>

<body>

    @include('components.header')

    @php
        $user = auth()->user()->load('penggalangDana');
        $penggalang = $user->penggalangDana;
        $penggalangStatus = $penggalang?->status;
        $avatarInitial = strtoupper(substr($user->name ?? 'U', 0, 1));
    @endphp

    <main class="profile-page">

        {{-- HERO --}}
        <section class="profile-hero">
            <div class="profile-container">
                <img src="{{ asset('assets/profile-banner.png') }}" class="profile-banner" alt="Profil OrangBaik.id">
            </div>
        </section>

        {{-- CONTENT --}}
        <section class="profile-content">
            <div class="profile-container">

                {{-- PROFILE SUMMARY --}}
                <section class="profile-summary">

                    <div class="profile-user">
                        @if(!empty($user->foto_profil))
                            <img src="{{ asset('storage/' . $user->foto_profil) }}" class="profile-avatar"
                                alt="{{ $user->name }}">
                        @else
                            <div class="profile-avatar profile-avatar-placeholder">
                                {{ $avatarInitial }}
                            </div>
                        @endif

                        <div class="profile-user-text">
                            <h1>{{ $user->name }}</h1>
                            <p>{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="profile-stats">
                        <div class="profile-stat-item">
                            <strong>Rp{{ number_format($totalDonasi ?? 0, 0, ',', '.') }}</strong>
                            <span>Nominal Donasi</span>
                        </div>

                        <div class="profile-stat-item">
                            <strong>{{ $jumlahDonasi ?? 0 }}</strong>
                            <span>Jumlah Donasi</span>
                        </div>
                    </div>

                </section>

                {{-- INFORMASI PRIBADI --}}
                <section class="profile-card">
                    <h2>Informasi Pribadi</h2>

                    <div class="profile-menu-list">

                        @if($user->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="profile-menu-item">
                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => 'dashboard'])
                                </span>

                                <span class="menu-text">
                                    <strong>Dashboard Admin</strong>
                                    <small>Masuk ke halaman administrator</small>
                                </span>

                                <span class="menu-arrow">›</span>
                            </a>
                        @endif

                        <a href="{{ route('profile.edit') }}" class="profile-menu-item">
                            <span class="menu-icon">
                                @include('components.profile-icon', ['type' => 'user'])
                            </span>

                            <span class="menu-text">
                                <strong>Ubah Profil</strong>
                                <small>Atur identitas dan foto profil kamu</small>
                            </span>

                            <span class="menu-arrow">›</span>
                        </a>

                        <a href="#" class="profile-menu-item">
                            <span class="menu-icon">
                                @include('components.profile-icon', ['type' => 'language'])
                            </span>

                            <span class="menu-text">
                                <strong>Bahasa</strong>
                                <small>Pilih bahasa yang ingin digunakan</small>
                            </span>

                            <span class="menu-arrow">›</span>
                        </a>

                        <a href="{{ route('riwayat.donasi') }}" class="profile-menu-item">
                            <span class="menu-icon">
                                @include('components.profile-icon', ['type' => 'donation'])
                            </span>

                            <span class="menu-text">
                                <strong>Riwayat Donasi</strong>
                                <small>Lihat seluruh riwayat donasi kamu</small>
                            </span>

                            <span class="menu-arrow">›</span>
                        </a>

                        @php
                            $penggalangStatus = $penggalang?->status;
                        @endphp

                        @if(!$penggalang)

                            <a href="#" class="profile-menu-item" id="openPenggalangModal">

                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => 'handshake'])
                                </span>

                                <span class="menu-text">
                                    <strong>Daftar sebagai Penggalang Dana</strong>
                                    <small>Mulai membuat campaign donasi</small>
                                </span>

                                <span class="menu-arrow">›</span>

                            </a>

                        @elseif($penggalangStatus === 'pending')
                            <a href="{{ route('profil.penggalang') }}" class="profile-menu-item">
                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => 'handshake'])
                                </span>
                                <span class="menu-text">
                                    <strong>Status Pengajuan</strong>
                                    <small>Pengajuan sedang diverifikasi admin</small>
                                </span>
                                <span class="menu-arrow">›</span>
                            </a>
                        @elseif($penggalangStatus === 'rejected')
                            <a href="{{ route('penggalang_dana.rejected') }}" class="profile-menu-item">
                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => 'handshake'])
                                    @if(!$penggalang->status_read)
                                        <span class="notif-dot"></span>
                                    @endif
                                </span>

                                <span class="menu-text">
                                    <strong>Pengajuan Ditolak</strong>
                                    <small>Lihat alasan penolakan</small>
                                </span>
                                <span class="menu-arrow">›</span>
                            </a>
                        @elseif($penggalangStatus === 'approved')
                            <a href="{{ route('profil.penggalang') }}" class="profile-menu-item">
                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => 'handshake'])
                                    @if(!$penggalang->status_read)
                                        <span class="notif-dot"></span>
                                    @endif
                                </span>
                                <span class="menu-text">
                                    <strong>Profil Penggalang Dana</strong>
                                    <small>Kelola akun penggalang dana kamu</small>
                                </span>
                                <span class="menu-arrow">›</span>
                            </a>
                        @endif
                    </div>
                </section>

                {{-- LAINNYA --}}
                <section class="profile-card">
                    <h2>Lainnya</h2>
                    <div class="profile-menu-list">
                        <a href="{{ route('tentang') }}" class="profile-menu-item">
                            <span class="menu-icon">
                                @include('components.profile-icon', ['type' => 'info'])
                            </span>
                            <span class="menu-text">
                                <strong>Tentang Kami</strong>
                                <small>Informasi umum tentang OrangBaik.id</small>
                            </span>
                            <span class="menu-arrow">›</span>
                        </a>
                        <a href="{{ route('syarat.ketentuan') }}" class="profile-menu-item">
                            <span class="menu-icon">
                                @include('components.profile-icon', ['type' => 'terms'])
                            </span>
                            <span class="menu-text">
                                <strong>Syarat & Ketentuan</strong>
                                <small>Aturan penggunaan OrangBaik.id</small>
                            </span>
                            <span class="menu-arrow">›</span>
                        </a>
                        <a href="{{ route('pusat.bantuan') }}" class="profile-menu-item">
                            <span class="menu-icon">
                                @include('components.profile-icon', ['type' => 'help'])
                            </span>
                            <span class="menu-text">
                                <strong>Pusat Bantuan</strong>
                                <small>Tempat mendapatkan panduan dan bantuan</small>
                            </span>
                            <span class="menu-arrow">›</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="profile-menu-item logout-button logout-trigger" >
                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => 'logout'])
                                </span>
                                <span class="menu-text">
                                    <strong>Logout</strong>
                                    <small>Keluar dari akun kamu</small>
                                </span>
                                <span class="menu-arrow">›</span>
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        </section>

        {{-- MODAL --}}
        <div class="modal-overlay" id="penggalangModal">
            <div class="penggalang-modal">

                <h2>Daftar Penggalang Dana</h2>

                <p>
                    Silakan pilih jenis akun penggalang dana yang ingin didaftarkan.
                </p>

                <div class="modal-buttons">
                    <a href="{{ route('verifikasi.penggalang') }}" class="btn-individu">
                        Individu
                    </a>

                    <a href="{{ route('penggalang_dana.organisasi.create') }}" class="btn-organisasi">
                        Organisasi
                    </a>
                </div>

                <button type="button" class="btn-close" id="closePenggalangModal">
                    Batal
                </button>

            </div>
        </div>

    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/profile-user.js') }}"></script>
</body>

</html>