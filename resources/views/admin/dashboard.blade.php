@extends('layouts.admin')

@section('content')

<div class="welcome-banner">
    <div>
        <h2>Selamat datang, {{ auth()->user()->name }} 👋</h2>
        <p>Berikut ringkasan aktivitas platform OrangBaik.id hari ini.</p>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-logout">Logout</button>
    </form>
</div>

<!-- Statistik -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon icon-blue">📂</div>
        <div>
            <p class="stat-label">Total Campaign</p>
            <h3 class="stat-value text-blue">{{ $totalCampaign }}</h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-green">🤝</div>
        <div>
            <p class="stat-label">Total Penggalang Dana</p>
            <h3 class="stat-value text-green">{{ $totalPenggalangDana }}</h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-purple">👤</div>
        <div>
            <p class="stat-label">Total User</p>
            <h3 class="stat-value text-purple">{{ $totalUser }}</h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-red">🛡️</div>
        <div>
            <p class="stat-label">Total Admin</p>
            <h3 class="stat-value text-red">{{ $totalAdmin }}</h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-teal">💼</div>
        <div>
            <p class="stat-label">Total Fundraiser</p>
            <h3 class="stat-value text-teal">{{ $totalFundraiser }}</h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-orange">📰</div>
        <div>
            <p class="stat-label">Total Berita</p>
            <h3 class="stat-value text-orange">{{ $totalBerita }}</h3>
        </div>
    </div>

</div>

<!-- Menu Manajemen -->
<div class="card">

    <div class="card-header">
        <h2>Manajemen Data</h2>
        <span class="card-subtitle">Akses cepat ke modul admin</span>
    </div>

    <div class="menu-grid">

        <a href="#" class="menu-item">
            <span class="menu-icon">📂</span>
            <span>Filter</span>
        </a>

        <a href="#" class="menu-item">
            <span class="menu-icon">📢</span>
            <span>Campaign</span>
        </a>

        <a href="#" class="menu-item">
            <span class="menu-icon">👤</span>
            <span>Penggalang Dana</span>
        </a>

        <a href="#" class="menu-item">
            <span class="menu-icon">📰</span>
            <span>Berita</span>
        </a>

    </div>

</div>

@endsection