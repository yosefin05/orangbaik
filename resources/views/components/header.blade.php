<header class="site-header">
    <div class="container header-inner">

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
                Pencarian ⌕
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
</header>