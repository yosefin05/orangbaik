<header class="site-header" id="siteHeader">
    <div class="container header-inner desktop-header">

        <a href="{{ url('/') }}" class="brand">
            <div class="brand-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="OrangBaik.id">
            </div>
        </a>

        <nav class="nav-menu">
            <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
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
            <button class="search-btn" type="button">
                Pencarian <i class="bi bi-search"></i>
            </button>

            <span class="divider"></span>

            @guest
                <a href="{{ route('login') }}" class="login-link">Masuk</a>
                <a href="{{ route('register') }}" class="register-btn">Daftar</a>
            @endguest

            @auth
                <div class="header-user">
                    <button class="header-user-button" type="button">
                        <span class="header-user-avatar">
                            @if (!empty(auth()->user()->foto_profile))
                                <img 
                                    src="{{ asset('storage/' . auth()->user()->foto_profile) }}" 
                                    alt="{{ auth()->user()->name }}"
                                >
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </span>

                        <span class="header-user-name">
                            {{ auth()->user()->name }}
                        </span>
                    </button>

                    <div class="header-user-dropdown">
                        <a href="{{ route('profile.user') }}">Profil Saya</a>
                        <a href="{{ route('riwayat.donasi') }}">Riwayat Donasi</a>
                        <a href="{{ route('ubah.profile') }}">Ubah Profil</a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit">Keluar</button>
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

            <input 
                type="text" 
                name="q" 
                placeholder="Cari Program Unggulan Lainnya"
            >
        </form>

        @guest
            <a href="{{ route('login') }}" class="mobile-profile-button" aria-label="Login">
                <i class="bi bi-person-fill"></i>
            </a>
        @endguest

        @auth
            <a href="{{ route('profile.user') }}" class="mobile-profile-button" aria-label="Profil">
                @if (!empty(auth()->user()->foto_profile))
                    <img 
                        src="{{ asset('storage/' . auth()->user()->foto_profile) }}" 
                        alt="{{ auth()->user()->name }}"
                    >
                @else
                    <i class="bi bi-person-fill"></i>
                @endif
            </a>
        @endauth

    </div>
</header>

<script src="{{ asset('js/header.js') }}"></script>