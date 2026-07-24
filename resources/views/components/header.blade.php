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

            <form action="{{ url('donasi') }}" method="GET" class="desktop-search-form">
                <input type="text" name="q" placeholder="Cari campaign...">

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
                                <i class="bi bi-megaphone-fill"></i>
                                <span>Profil Penggalang</span>
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

        <form action="{{ url('donasi') }}" method="GET" class="mobile-search-form">
            <button type="submit" aria-label="Cari">
                <i class="bi bi-search"></i>
            </button>

            <input type="text" name="q" placeholder="Cari Program Donasi">
        </form>

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