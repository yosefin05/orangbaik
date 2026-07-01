<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrangBaik Admin</title>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
</head>

<body>

    <div class="admin-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">

            <a href="{{ route('home') }}" class="sidebar-brand">
                <h1>OrangBaik<span>.id</span></h1>
                <p>Admin Panel</p>
            </a>

            <nav class="sidebar-nav">

                <a href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    🏠 Dashboard
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                    👤 User
                </a>

                <a href="{{ route('admin.penggalang_dana.index') }}"
                    class="{{ request()->routeIs('admin.penggalang_dana.index') ? 'active' : '' }}">
                    👥 Penggalang Dana
                </a>

                <a href="{{ route('admin.berita.index') }}"
                    class="{{ request()->routeIs('admin.berita.index') ? 'active' : '' }}">
                    📰 Berita
                </a>

                <a href="{{ route('admin.filter.index') }}"
                    class="{{ request()->routeIs('admin.filter.index') ? 'active' : '' }}">
                    📂 Filter
                </a>

                <a href="{{ route('admin.campaign.index') }}"
                    class="{{ request()->routeIs('admin.campaign.index') ? 'active' : '' }}">
                    📢 Campaign
                </a>

                <a href="{{ route('admin.testimoni.index') }}"
                    class="{{ request()->routeIs('admin.testimoni.index') ? 'active' : ''}}">
                    🤳 Testimoni
                </a>

                <a href="{{ route('admin.komentar.index') }}"
                    class="{{ request()->routeIs('admin.komentar.index') ? 'active' : ''}}">
                    💬 Komentar
                </a>
            </nav>

            <div class="sidebar-footer">
                &copy; {{ date('Y') }} OrangBaik.id
            </div>

        </aside>

        <!-- CONTENT -->
        <div class="main-content">

            <!-- HEADER -->
            <header class="topbar">
                <h1>@yield('page-title', 'Dashboard Control Panel')</h1>

                <div class="topbar-user">
                    <button class="topbar-user-button" type="button">
                        <div class="user-info">
                            <p class="user-name">{{ auth()->user()->name }}</p>
                            <p class="user-role">Administrator</p>
                        </div>
                        <div class="user-avatar">

                            @if(auth()->user()->foto_profil)
                                <img src="{{ asset('storage/' . auth()->user()->foto_profil) }}"
                                    alt="{{ auth()->user()->name }}">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </div>
                    </button>
                    <div class="topbar-user-dropdown">
                        <a href="{{ route('profile.user') }}">Profil Saya</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit">Keluar</button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- CONTENT AREA -->
            <main class="content-area">
                @yield('content')
            </main>

        </div>

    </div>

</body>

</html>