@extends('layouts.admin')

@section('page-title', 'Dashboard')

{{-- ============================================================ --}}
{{-- PAGE-SPECIFIC CSS                                             --}}
{{-- ============================================================ --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

@section('content')
    {{-- ========================================================== --}}
    {{-- WELCOME BANNER                                             --}}
    {{-- ========================================================== --}}
    <section class="welcome-banner">
        <div>
            <h2>
                Selamat datang, {{ auth()->user()->name }}
                <i class="bi bi-stars"></i>
            </h2>
            <p>Berikut ringkasan aktivitas platform OrangBaik.id hari ini.</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout logout-trigger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </section>

    {{-- ========================================================== --}}
    {{-- STATISTIK                                                  --}}
    {{-- ========================================================== --}}
    <section class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon icon-purple">
                <i class="bi bi-person"></i>
            </div>

            <div>
                <p class="stat-label">Total User</p>
                <h3 class="stat-value text-purple">{{ $totalUser }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-red">
                <i class="bi bi-shield-check"></i>
            </div>

            <div>
                <p class="stat-label">Total Admin</p>
                <h3 class="stat-value text-red">{{ $totalAdmin }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-green">
                <i class="bi bi-people"></i>
            </div>

            <div>
                <p class="stat-label">Total Penggalang Dana</p>
                <h3 class="stat-value text-green">{{ $totalPenggalangDana }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-blue">
                <i class="bi bi-megaphone"></i>
            </div>

            <div>
                <p class="stat-label">Total Campaign</p>
                <h3 class="stat-value text-blue">{{ $totalCampaign }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-orange">
                <i class="bi bi-newspaper"></i>
            </div>

            <div>
                <p class="stat-label">Total Berita</p>
                <h3 class="stat-value text-orange">{{ $totalBerita }}</h3>
            </div>
        </div>

    </section>

    {{-- ========================================================== --}}
    {{-- MENU MANAJEMEN                                             --}}
    {{-- ========================================================== --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Manajemen Data</h2>
                <p class="card-subtitle">Akses cepat ke modul admin.</p>
            </div>
        </div>

        <div class="menu-grid">

            <a href="{{ route('admin.users.index') }}" class="menu-item">
                <i class="bi bi-person menu-icon"></i>
                <span>User</span>
            </a>

            <a href="{{ route('admin.penggalang_dana.index') }}" class="menu-item">
                <i class="bi bi-people menu-icon"></i>
                <span>Penggalang Dana</span>
            </a>

            <a href="{{ route('admin.berita.index') }}" class="menu-item">
                <i class="bi bi-newspaper menu-icon"></i>
                <span>Berita</span>
            </a>

            <a href="{{ route('admin.filter.index') }}" class="menu-item">
                <i class="bi bi-funnel menu-icon"></i>
                <span>Filter</span>
            </a>

            <a href="{{ route('admin.campaign.index') }}" class="menu-item">
                <i class="bi bi-megaphone menu-icon"></i>
                <span>Campaign</span>
            </a>

            <a href="{{ route('admin.testimoni.index') }}" class="menu-item">
                <i class="bi bi-chat-heart menu-icon"></i>
                <span>Testimoni</span>
            </a>

            <a href="{{ route('admin.komentar.index') }}" class="menu-item">
                <i class="bi bi-chat-dots menu-icon"></i>
                <span>Komentar</span>
            </a>

        </div>

    </section>

@endsection