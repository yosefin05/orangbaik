@php
    // Judul kategori (opsional). Dikirim dari halaman donasi saat sebuah
    // kategori dipilih; bila terisi, header mobile menampilkan nama
    // kategori menggantikan search bar.
    $headerTitle = $headerTitle ?? null;

    // Ikon kategori (opsional), dikirim bersama $headerTitle.
    $headerIcon = $headerIcon ?? 'assets/lainnya.svg';

    // Deteksi halaman asal
    $isLandingPage = request()->is('/');
    $isDonationPage = request()->is('donasi', 'donasi/*');
    $isNewsPage = request()->is('berita', 'berita/*');
    $isCalculatorPage = request()->is('kalkulator', 'kalkulator/*');
    $isProfilePage = request()->routeIs('profile.user');


    // Header search pada halaman utama
    $showMobileSearch = $isLandingPage
        || $isDonationPage
        || $isNewsPage
        || $isCalculatorPage
        || $isProfilePage;

    // Jenis pencarian berdasarkan halaman asal
    $searchType = $isNewsPage ? 'berita' : 'campaign';

    $mobileSearchType = $searchType;

    $mobileSearchPlaceholder = $isNewsPage
        ? 'Cari berita...'
        : 'Cari program donasi...';

    // Halaman utama (setara): Beranda, Donasi, Kalkulator, Berita, Profil.
    // Semuanya memakai header + bottom navigation dan TANPA tombol back.
    $showBottomNav = request()->is(
        '/',
        'donasi',
        'donasi/*',
        'berita',
        'berita/*',
        'kalkulator',
        'kalkulator/*',
        'profil',
        'profil/*'
    )
        || request()->routeIs('profile.user');

    // Tombol back hanya untuk halaman di luar halaman utama (detail, form, dll.)
    $showMobileBack = !$showBottomNav;
@endphp

<x-logout-modal />

<header class="site-header {{ $showMobileSearch ? 'has-mobile-search' : '' }}" id="siteHeader">

    {{-- ==========================================
    DESKTOP HEADER
    =========================================== --}}
    <div class="container header-inner desktop-header">

        <a href="{{ route('home') }}" class="brand" aria-label="OrangBaik.id">
            <div class="brand-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="OrangBaik.id">
            </div>
        </a>

        <nav class="nav-menu">
            <a href="{{ route('home') }}" class="{{ request()->is('/') ? 'active' : '' }}">
                Beranda
            </a>

            <a href="{{ url('donasi') }}" class="{{ request()->is('donasi*') ? 'active' : '' }}">
                Donasi
            </a>

            <a href="{{ url('kalkulator') }}" class="{{ request()->is('kalkulator*') ? 'active' : '' }}">
                Kalkulator
            </a>

            <a href="{{ url('berita') }}" class="{{ request()->is('berita*') ? 'active' : '' }}">
                Berita
            </a>
        </nav>

        <div class="header-actions">

            {{-- Desktop search --}}
            <form action="{{ route('search') }}" method="GET" class="desktop-search-form">
                <input type="text" name="q" placeholder="{{ $isNewsPage ? 'Cari berita...' : 'Cari campaign...' }}"
                    value="{{ request('q') }}" aria-label="Cari">

                <input type="hidden" name="type" value="{{ $searchType }}">

                <button type="submit" aria-label="Cari">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <span class="divider"></span>

            @guest
                <a href="{{ route('login') }}" class="login-link">
                    Masuk
                </a>

                <a href="{{ route('register') }}" class="register-btn">
                    Daftar
                </a>
            @endguest

            @auth
                <div class="header-user">

                    <button class="header-user-button" type="button">
                        <span class="header-user-avatar">
                            @if (!empty(auth()->user()->foto_profil))
                                <img src="{{ asset('storage/' . auth()->user()->foto_profil) }}"
                                    alt="{{ auth()->user()->name }}">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </span>

                        <span class="header-user-name">
                            {{ auth()->user()->name }}
                        </span>

                        <i class="bi bi-chevron-down header-user-icon"></i>
                    </button>

                    <div class="header-user-dropdown">

                        <a href="{{ route('profile.user') }}">
                            <i class="bi bi-person-circle"></i>
                            <span>Profil Saya</span>
                        </a>

                        @php
                            $penggalang = auth()->user()->penggalangDana;
                        @endphp

                        @if ($penggalang && $penggalang->status === 'approved')
                            <a href="{{ route('profil.penggalang', $penggalang->id) }}">
                                <i class="bi bi-people"></i>
                                <span>Profil Penggalang</span>
                            </a>

                            <a href="{{ route('campaign.create', $penggalang->id) }}">
                                <i class="bi bi-megaphone"></i>
                                <span>Tambah Campaign</span>
                            </a>
                        @endif

                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i>
                                <span>Dashboard Admin</span>
                            </a>
                        @endif

                        <form id="logoutForm" action="{{ route('logout') }}" method="POST">
                            @csrf

                            <button type="button" id="logoutButton" class="logout-trigger">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </button>
                        </form>

                    </div>
                </div>
            @endauth

        </div>
    </div>

    {{-- ==========================================
    MOBILE HEADER
    - Default : search bar
    - Kategori dipilih : [← back] [Kategori / Nama] [ikon]
    =========================================== --}}
    @if ($showMobileSearch)
        <div class="container mobile-header-inner {{ $headerTitle ? 'is-category' : '' }}">

            @if ($headerTitle)

                <a href="{{ route('donasi') }}" class="mobile-header-back" aria-label="Kembali ke semua kategori">
                    <i class="bi bi-arrow-left"></i>
                </a>

                <div class="mobile-header-heading">
                    <span class="mobile-header-eyebrow">Kategori</span>
                    <h1 class="mobile-header-title">{{ $headerTitle }}</h1>
                </div>

                <span class="mobile-header-badge" aria-hidden="true">
                    <img src="{{ asset($headerIcon) }}" alt="">
                </span>

            @else

                <form action="{{ route('search') }}" method="GET" class="mobile-search-form">
                    <button type="submit" aria-label="Cari">
                        <i class="bi bi-search"></i>
                    </button>

                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $mobileSearchPlaceholder }}"
                        aria-label="{{ $mobileSearchPlaceholder }}">

                    <input type="hidden" name="type" value="{{ $mobileSearchType }}">
                </form>

            @endif

        </div>
    @endif

</header>


{{-- ==========================================
MOBILE BACK BUTTON
Hanya untuk halaman di luar halaman utama.
=========================================== --}}
@if ($showMobileBack)
    <a href="{{ route('home') }}" class="mobile-page-back" data-mobile-back aria-label="Kembali">
        <i class="bi bi-arrow-left"></i>
    </a>
@endif


{{-- ==========================================
MOBILE BOTTOM NAVIGATION
=========================================== --}}
@if ($showBottomNav)
    <nav class="mobile-bottom-nav" aria-label="Navigasi utama">

        <a href="{{ route('home') }}" class="{{ request()->is('/') ? 'active' : '' }}" aria-label="Beranda">
            <i class="bi bi-house"></i>
            <span>Beranda</span>
        </a>

        <a href="{{ url('donasi') }}" class="{{ request()->is('donasi*') ? 'active' : '' }}" aria-label="Donasi">
            <i class="bi bi-heart"></i>
            <span>Donasi</span>
        </a>

        <a href="{{ url('kalkulator') }}" class="{{ request()->is('kalkulator*') ? 'active' : '' }}"
            aria-label="Kalkulator">
            <i class="bi bi-calculator"></i>
            <span>Kalkulator</span>
        </a>

        <a href="{{ url('berita') }}" class="{{ request()->is('berita*') ? 'active' : '' }}" aria-label="Berita">
            <i class="bi bi-newspaper"></i>
            <span>Berita</span>
        </a>

        <a href="{{ auth()->check() ? route('profile.user') : route('login') }}"
            class="{{ request()->routeIs('profile.user') ? 'active' : '' }}" aria-label="Profil">
            <i class="bi bi-person"></i>
            <span>Profil</span>
        </a>

    </nav>
@endif