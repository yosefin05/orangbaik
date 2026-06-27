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

@php
    $userName = auth()->user()->name ?? 'Yosefin Kurniawati Tanto';

    $personalMenus = [
        [
            'title' => 'Ubah Profile',
            'desc' => 'Atur identitas dan foto profile kamu',
            'icon' => 'user',
            'url' => route('profile.edit'),
        ],
        [
            'title' => 'Bahasa',
            'desc' => 'Pilih bahasa yang ingin digunakan',
            'icon' => 'language',
            'url' => '#',
        ],
        [
            'title' => 'Riwayat Donasi',
            'desc' => 'Lihat detail dan status donasi Anda.',
            'icon' => 'donation',
            'url' => '#',
        ],
        [
            'title' => 'Daftar sebagai Penggalang Dana',
            'desc' => 'Daftar sekarang dan mulai menggalang dukungan melalui campaign donasi.',
            'icon' => 'handshake',
            'url' => '#',
        ],
    ];

    $otherMenus = [
        [
            'title' => 'Tentang Kami',
            'desc' => 'Informasi umum tentang orangbaik.id',
            'icon' => 'info',
            'url' => '#',
        ],
        [
            'title' => 'Syarat & Ketentuan',
            'desc' => 'Aturan penggunaan Aplikasi',
            'icon' => 'terms',
            'url' => '#',
        ],
        [
            'title' => 'Pusat Bantuan',
            'desc' => 'Tempat mendapatkan panduan dan bantuan',
            'icon' => 'help',
            'url' => '#',
        ],
        [
            'title' => 'Logout',
            'desc' => 'Ini akan membuatmu keluar dari semua ekosistem aplikasi artiera.',
            'icon' => 'logout',
            'url' => route('logout'),
            'logout' => true,
        ],
    ];
@endphp

<main class="profile-page">

    <section class="profile-hero">
        <div class="profile-container">

            <button class="profile-back" type="button" onclick="history.back()">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M15 18L9 12L15 6" />
                </svg>
                <span>Kembali</span>
            </button>

            <img 
                src="{{ asset('assets/profile-banner.png') }}" 
                alt="Banner OrangBaik.id"
                class="profile-banner"
            >

        </div>
    </section>

    <section class="profile-content">
        <div class="profile-container">

            <div class="profile-summary">
                <div class="profile-user">
                    <img 
                        src="{{ asset('assets/avatar-user.png') }}" 
                        alt="{{ $userName }}"
                        class="profile-avatar"
                    >

                    <div>
                        <h1>{{ $userName }}</h1>
                        <p>Verified User</p>
                    </div>
                </div>

                <div class="profile-stats">
                    <div>
                        <strong>Rp150.000</strong>
                        <span>Nominal Donasi</span>
                    </div>

                    <div>
                        <strong>5</strong>
                        <span>Jumlah Donasi</span>
                    </div>
                </div>
            </div>

            <section class="profile-card">
                <h2>Informasi Pribadi</h2>

                <div class="profile-menu-list">
                    @foreach ($personalMenus as $menu)
                        <a href="{{ $menu['url'] }}" class="profile-menu-item">
                            <span class="menu-icon">
                                @include('components.profile-icon', ['type' => $menu['icon']])
                            </span>

                            <span class="menu-text">
                                <strong>{{ $menu['title'] }}</strong>
                                <small>{{ $menu['desc'] }}</small>
                            </span>

                            <span class="menu-arrow">›</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="profile-card">
                <h2>Lainnya</h2>

                <div class="profile-menu-list">
                    @foreach ($otherMenus as $menu)
                        @if (!empty($menu['logout']))
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf

                                <button type="submit" class="profile-menu-item logout-button">
                                    <span class="menu-icon">
                                        @include('components.profile-icon', ['type' => $menu['icon']])
                                    </span>

                                    <span class="menu-text">
                                        <strong>{{ $menu['title'] }}</strong>
                                        <small>{{ $menu['desc'] }}</small>
                                    </span>

                                    <span class="menu-arrow">›</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ $menu['url'] }}" class="profile-menu-item">
                                <span class="menu-icon">
                                    @include('components.profile-icon', ['type' => $menu['icon']])
                                </span>

                                <span class="menu-text">
                                    <strong>{{ $menu['title'] }}</strong>
                                    <small>{{ $menu['desc'] }}</small>
                                </span>

                                <span class="menu-arrow">›</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>

        </div>
    </section>

</main>

@include('components.footer')

</body>
</html>