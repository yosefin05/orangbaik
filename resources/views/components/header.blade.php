<header class="site-header">
    <div class="container header-inner">

        <a href="{{ url('/') }}" class="brand">
            <div class="brand-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="OrangBaik.id">
            </div>
        </a>

        <nav class="nav-menu">
            <a href="{{ url('/') }}" class="active">Beranda</a>
            <a href="{{ url('donasi') }}">Donasi</a>
            <a href="{{ url('kalkulator') }}">Kalkulator</a>
            <a href="{{ url('berita') }}">Berita</a>
        </nav>

        <div class="header-actions">
            <button class="search-btn">Pencarian ⌕</button>

            <span class="divider"></span>

            <a href="{{ route('login') }}" class="login-link">Masuk</a>
            <a href="{{ route('register') }}" class="register-btn">Daftar</a>
        </div>

    </div>
</header>