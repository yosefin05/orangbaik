<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OrangBaik Admin</title>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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
                🏠 Dashboard
            </a>

            <a href="{{ route('admin.users.index') }}"
                class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                👤 User
            </a>

            <a href="{{ route('admin.berita.index') }}"
                class="{{ request()->routeIs('admin.berita.index') ? 'active' : '' }}">
                📂 Berita
            </a>

            <a href="{{ route('admin.filter.index') }}"
                class="{{ request()->routeIs('admin.filter.index') ? 'active' : '' }}">
                📂 Filter
            </a>

            <a href="{{ route('admin.penggalang_dana.index') }}"
                class="{{ request()->routeIs('admin.penggalang_dana.index') ? 'active' : '' }}">
                👥 Penggalang Dana
            </a>

            <a href="{{ route('admin.campaign.index') }}"
                class="{{ request()->routeIs('admin.campaign.index') ? 'active' : '' }}">
                📢 Campaign
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
                <div class="user-info">
                    <p class="user-name">{{ auth()->user()->name }}</p>
                    <p class="user-role">Administrator</p>
                </div>
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
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