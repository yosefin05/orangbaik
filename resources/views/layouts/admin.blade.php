<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrangBaik Admin</title>


    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    <div class="admin-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">

            <div class="sidebar-brand">
                <h1>OrangBaik<span>.id</span></h1>
                <p>Admin Panel</p>
            </div>

            <nav class="sidebar-nav">

                <a href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-person"></i>
                    <span>User</span>
                </a>

                <a href="{{ route('admin.penggalang_dana.index') }}"
                    class="{{ request()->routeIs('admin.penggalang_dana.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Penggalang Dana</span>
                </a>

                <a href="{{ route('admin.berita.index') }}"
                    class="{{ request()->routeIs('admin.berita.*') ? 'active' : '' }}">
                    <i class="bi bi-newspaper"></i>
                    <span>Berita</span>
                </a>

                <a href="{{ route('admin.filter.index') }}"
                    class="{{ request()->routeIs('admin.filter.*') ? 'active' : '' }}">
                    <i class="bi bi-funnel"></i>
                    <span>Filter</span>
                </a>

                <a href="{{ route('admin.campaign.index') }}"
                    class="{{ request()->routeIs('admin.campaign.*') ? 'active' : '' }}">
                    <i class="bi bi-megaphone"></i>
                    <span>Campaign</span>
                </a>

                <a href="{{ route('admin.testimoni.index') }}"
                    class="{{ request()->routeIs('admin.testimoni.*') ? 'active' : '' }}">
                    <i class="bi bi-chat-heart"></i>
                    <span>Testimoni</span>
                </a>

                <a href="{{ route('admin.komentar.index') }}"
                    class="{{ request()->routeIs('admin.komentar.*') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots"></i>
                    <span>Komentar</span>
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

                        <i class="bi bi-chevron-down user-dropdown-icon"></i>

                    </button>

                    <div class="topbar-user-dropdown">
                        <a href="{{ route('profile.user') }}">
                            <i class="bi bi-person-circle"></i>
                            <span>Profil Saya</span>
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Keluar</span>
                            </button>
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