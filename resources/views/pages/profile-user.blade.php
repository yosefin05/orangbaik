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

<body>

    @include('components.header')

    @php

        $user = auth()->user();

    @endphp

    <main class="profile-page">

        <section class="profile-hero">

            <div class="profile-container">
                <img src="{{ asset('assets/profile-banner.png') }}" class="profile-banner" alt="Banner OrangBaik.id">
            </div>

        </section>

        <section class="profile-content">

            <div class="profile-container">

                {{-- PROFILE SUMMARY --}}
                <div class="profile-summary">

                    <div class="profile-user">

                        @if($user->foto_profil)

                            <img src="{{ asset('storage/' . $user->foto_profil) }}" class="profile-avatar"
                                alt="{{ $user->name }}">

                        @else

                            <img src="{{ asset('assets/avatar-user.png') }}" class="profile-avatar" alt="{{ $user->name }}">

                        @endif

                        <div>

                            <h1>{{ $user->name }}</h1>

                            <p>

                                @if($user->role == 'admin')

                                    Administrator

                                @elseif($user->penggalangDana)

                                    Penggalang Dana

                                @else

                                    Verified User

                                @endif

                            </p>

                        </div>

                    </div>

                    <div class="profile-stats">

                        <div>

                            <strong>

                                Rp{{ number_format($totalDonasi ?? 0, 0, ',', '.') }}

                            </strong>

                            <span>Nominal Donasi</span>

                        </div>

                        <div>

                            <strong>

                                {{ $jumlahDonasi ?? 0 }}

                            </strong>

                            <span>Jumlah Donasi</span>

                        </div>

                    </div>

                </div>
                {{-- INFORMASI PRIBADI --}}
                <section class="profile-card">

                    <h2>Informasi Pribadi</h2>

                    <div class="profile-menu-list">

                        {{-- Dashboard Admin --}}
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

                        {{-- Ubah Profil --}}
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

                        {{-- Bahasa --}}
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

                        {{-- Riwayat Donasi --}}
                        <a href="{{ route('riwayat.donasi') }}" class="profile-menu-item">

                            <span class="menu-icon">
                                @include('components.profile-icon', ['type' => 'donation'])
                            </span>

                            <span class="menu-text">
                                <strong>Riwayat Donasi</strong>
                                <small>Lihat seluruh riwayat donasi Anda</small>
                            </span>

                            <span class="menu-arrow">›</span>

                        </a>

                        {{-- Penggalang Dana --}}

                        @if(!$user->penggalangDana)

                            {{-- Belum daftar --}}
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

                        @elseif($user->penggalangDana->status == 'pending')

                            {{-- Masih diverifikasi --}}
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

                        @elseif($user->penggalangDana->status == 'rejected')

                            {{-- Ditolak --}}
                            <a href="{{ route('profil.penggalang') }}" class="profile-menu-item">

                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => 'handshake'])
                                </span>

                                <span class="menu-text">
                                    <strong>Pengajuan Ditolak</strong>
                                    <small>Lihat alasan dan ajukan kembali</small>
                                </span>

                                <span class="menu-arrow">›</span>

                            </a>

                        @elseif($user->penggalangDana->status == 'approved')

                            {{-- Sudah aktif --}}
                            <a href="{{ route('profil.penggalang') }}" class="profile-menu-item" id="openPenggalangModal">

                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => 'handshake'])
                                </span>

                                <span class="menu-text">
                                    <strong>Profil Penggalang Dana</strong>
                                    <small>Kelola akun penggalang dana Anda</small>
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

                        {{-- Tentang Kami --}}
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

                        {{-- Syarat --}}
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

                        {{-- Bantuan --}}
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

                        {{-- Logout --}}
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf

                            <button type="submit" class="profile-menu-item logout-button">

                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => 'logout'])
                                </span>

                                <span class="menu-text">
                                    <strong>Logout</strong>
                                    <small>Keluar dari akun Anda</small>
                                </span>

                                <span class="menu-arrow">›</span>

                            </button>
                        </form>

                    </div>
                </section>
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
            </div>
        </section>

    </main>

    @include('components.footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const openBtn = document.getElementById('openPenggalangModal');
            const closeBtn = document.getElementById('closePenggalangModal');
            const modal = document.getElementById('penggalangModal');

            if (openBtn) {
                openBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    modal.classList.add('active');
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    modal.classList.remove('active');
                });
            }

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });

        });
    </script>