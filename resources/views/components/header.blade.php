<x-logout-modal />
<header class="site-header" id="siteHeader">
    {{-- DESKTOP HEADER --}}
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

            <form action="{{ route('search') }}" method="GET" class="desktop-search-form">
                <input type="text" name="q" placeholder="Cari campaign..." value="{{ request('q') }}">
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
                            @if(!empty(auth()->user()->foto_profil))
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
                        @if($penggalang && $penggalang->status === 'approved')
                            <a href="{{ route('profil.penggalang', $penggalang->id) }}">
                                <i class="bi bi-people-fill"></i>
                                <span>Profil Penggalang</span>
                            </a>
                            <a href="{{ route('campaign.create', $penggalang->id) }}">
                                <i class="bi bi-megaphone-fill"></i>
                                <span>Tambah Campaign</span>
                            </a>
                        @endif

                        @if(auth()->user()->role === 'admin')
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

    {{-- MOBILE HEADER --}}
    <div class="container mobile-header-inner">

        {{-- BURGER MENU DI KIRI --}}
        <button class="mobile-burger-btn" id="mobileBurgerBtn" aria-label="Menu Navigasi">
            <span class="burger-line"></span>
            <span class="burger-line"></span>
            <span class="burger-line"></span>
        </button>

        {{-- SEARCH FORM --}}
        <form action="{{ route('search') }}" method="GET" class="mobile-search-form">
            <button type="submit" aria-label="Cari">
                <i class="bi bi-search"></i>
            </button>
            <input type="text" name="q" placeholder="Cari Program Donasi" value="{{ request('q') }}">
        </form>

        {{-- PROFILE / LOGIN --}}
        @guest
            <a href="{{ route('login') }}" class="mobile-login-button">
                Masuk
            </a>
        @endguest

        @auth
            <a href="{{ route('profile.user') }}" class="mobile-profile-button" aria-label="Profil Saya">
                @if(!empty(auth()->user()->foto_profil))
                    <img src="{{ asset('storage/' . auth()->user()->foto_profil) }}" alt="{{ auth()->user()->name }}">
                @else
                    <span>
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                @endif
            </a>
        @endauth

    </div>

    {{-- MOBILE NAV MENU (SLIDE-IN) --}}
    <div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
    <nav class="mobile-nav-menu" id="mobileNavMenu">

        <div class="mobile-nav-header">
            <a href="{{ route('home') }}" class="mobile-nav-brand" aria-label="OrangBaik.id">
                <img src="{{ asset('assets/logo.png') }}" alt="OrangBaik.id">
                <span>orangbaik<b>.id</b></span>
            </a>

            <button type="button" id="mobileNavClose" class="mobile-nav-close" aria-label="Tutup menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        @auth
            <a href="{{ route('profile.user') }}" class="mobile-nav-profile-card">
                <span class="mobile-nav-profile-avatar">
                    @if(!empty(auth()->user()->foto_profil))
                        <img src="{{ asset('storage/' . auth()->user()->foto_profil) }}" alt="{{ auth()->user()->name }}">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </span>
                <span class="mobile-nav-profile-info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>Lihat profil saya <i class="bi bi-arrow-right"></i></small>
                </span>
            </a>
        @endauth

        <div class="mobile-nav-scroll">

            <div class="mobile-nav-section">
                <span class="mobile-nav-section-label">Menu</span>
                <div class="mobile-nav-links">
                    <a href="{{ route('home') }}" class="{{ request()->is('/') ? 'active' : '' }}">
                        <span class="mobile-nav-icon"><i class="bi bi-house-door-fill"></i></span>
                        <span>Beranda</span>
                    </a>

                    <a href="{{ url('donasi') }}" class="{{ request()->is('donasi*') ? 'active' : '' }}">
                        <span class="mobile-nav-icon"><i class="bi bi-heart-fill"></i></span>
                        <span>Donasi</span>
                    </a>

                    <a href="{{ url('kalkulator') }}" class="{{ request()->is('kalkulator*') ? 'active' : '' }}">
                        <span class="mobile-nav-icon"><i class="bi bi-calculator-fill"></i></span>
                        <span>Kalkulator</span>
                    </a>

                    <a href="{{ url('berita') }}" class="{{ request()->is('berita*') ? 'active' : '' }}">
                        <span class="mobile-nav-icon"><i class="bi bi-file-earmark-text-fill"></i></span>
                        <span>Berita</span>
                    </a>
                </div>
            </div>

            @auth
                @php
                    $mobilePenggalang = auth()->user()->penggalangDana;
                @endphp
                @if(($mobilePenggalang && $mobilePenggalang->status === 'approved') || auth()->user()->role === 'admin')
                    <div class="mobile-nav-section">
                        <span class="mobile-nav-section-label">Akun Saya</span>
                        <div class="mobile-nav-links">
                            @if($mobilePenggalang && $mobilePenggalang->status === 'approved')
                                <a href="{{ route('profil.penggalang', $mobilePenggalang->id) }}">
                                    <span class="mobile-nav-icon"><i class="bi bi-people-fill"></i></span>
                                    <span>Profil Penggalang</span>
                                </a>
                                <a href="{{ route('campaign.create', $mobilePenggalang->id) }}">
                                    <span class="mobile-nav-icon"><i class="bi bi-megaphone-fill"></i></span>
                                    <span>Tambah Campaign</span>
                                </a>
                            @endif

                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}">
                                    <span class="mobile-nav-icon"><i class="bi bi-speedometer2"></i></span>
                                    <span>Dashboard Admin</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @endauth

        </div>

        <div class="mobile-nav-footer">
            @guest
                <a href="{{ route('register') }}" class="mobile-nav-register">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Daftar Sekarang</span>
                </a>
                <a href="{{ route('login') }}" class="mobile-nav-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Masuk</span>
                </a>
            @endguest

            @auth
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="mobile-nav-logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            @endauth
        </div>
    </nav>
</header>

{{-- MOBILE BOTTOM NAV --}}
<nav class="mobile-bottom-nav">
    <a href="{{ route('home') }}" class="{{ request()->is('/') ? 'active' : '' }}">
        <i class="bi bi-house-door-fill"></i>
        <span>Beranda</span>
    </a>

    <a href="{{ url('donasi') }}" class="{{ request()->is('donasi*') ? 'active' : '' }}">
        <i class="bi bi-heart-fill"></i>
        <span>Donasi</span>
    </a>

    <a href="{{ url('kalkulator') }}" class="{{ request()->is('kalkulator*') ? 'active' : '' }}">
        <i class="bi bi-calculator-fill"></i>
        <span>Kalkulator</span>
    </a>

    <a href="{{ url('berita') }}" class="{{ request()->is('berita*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-text-fill"></i>
        <span>Berita</span>
    </a>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const burgerBtn = document.getElementById('mobileBurgerBtn');
        const closeBtn = document.getElementById('mobileNavClose');
        const navMenu = document.getElementById('mobileNavMenu');
        const navOverlay = document.getElementById('mobileNavOverlay');

        function openMenu() {
            navMenu.classList.add('open');
            navOverlay.classList.add('active');
            document.body.classList.add('mobile-nav-open');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            navMenu.classList.remove('open');
            navOverlay.classList.remove('active');
            document.body.classList.remove('mobile-nav-open');
            document.body.style.overflow = '';
        }

        if (burgerBtn) burgerBtn.addEventListener('click', openMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMenu);
        if (navOverlay) navOverlay.addEventListener('click', closeMenu);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMenu();
        });
    });
</script>