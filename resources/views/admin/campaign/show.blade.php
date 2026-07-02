@extends('layouts.admin')

@section('page-title', 'Detail Campaign')

@section('content')

    {{-- Header Campaign --}}
    <section class="ob-card ob-card-lg profile-card">

        @if($campaign->thumbnail)
            <img
                src="{{ asset('storage/' . $campaign->thumbnail) }}"
                alt="{{ $campaign->judul }}"
                class="current-thumbnail">
        @else
            <div class="current-thumbnail table-avatar-placeholder">
                <i class="bi bi-image"></i>
            </div>
        @endif

        <div class="profile-info">
            <h2>{{ $campaign->judul }}</h2>

            <p class="profile-type">
                {{ $campaign->kategori->nama_kategori ?? 'Kategori tidak ditemukan' }}
            </p>

            @if($campaign->status === 'pending')
                <span class="badge badge-yellow">Pending</span>
            @elseif($campaign->status === 'approved')
                <span class="badge badge-green">Approved</span>
            @else
                <span class="badge badge-red">Rejected</span>
            @endif
        </div>

    </section>

    {{-- Informasi Campaign --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Informasi Campaign</h2>
                <p class="card-subtitle">
                    Detail utama campaign donasi.
                </p>
            </div>

            <a href="{{ route('admin.campaign.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <table class="data-table data-table-kv">
            <tbody>
                <tr>
                    <th>Slug</th>
                    <td>
                        <span class="badge badge-blue">
                            {{ $campaign->slug }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Penggalang Dana</th>
                    <td>{{ $campaign->penggalangDana->nama_penggalang ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Target Donasi</th>
                    <td>
                        <span class="text-muted-strong">
                            Rp {{ number_format($campaign->target_donasi, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Tanggal Mulai</th>
                    <td>
                        {{ $campaign->tanggal_mulai ? \Carbon\Carbon::parse($campaign->tanggal_mulai)->format('d M Y') : '-' }}
                    </td>
                </tr>

                <tr>
                    <th>Tanggal Berakhir</th>
                    <td>
                        {{ $campaign->tanggal_berakhir ? \Carbon\Carbon::parse($campaign->tanggal_berakhir)->format('d M Y') : '-' }}
                    </td>
                </tr>
            </tbody>
        </table>

    </section>

    {{-- Deskripsi --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Deskripsi Campaign</h2>
                <p class="card-subtitle">
                    Penjelasan lengkap tentang campaign.
                </p>
            </div>
        </div>

        <div class="detail-content">
            {!! nl2br(e($campaign->deskripsi)) !!}
        </div>

    </section>

    {{-- Galeri --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Galeri Campaign</h2>
                <p class="card-subtitle">
                    Kumpulan gambar pendukung campaign.
                </p>
            </div>
        </div>

        @if($campaign->campaignGambar->count())
            <div class="gallery-grid">
                @foreach($campaign->campaignGambar as $gambar)
                    <div class="gallery-item">
                        <img
                            src="{{ asset('storage/' . $gambar->foto) }}"
                            alt="{{ $campaign->judul }}">
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted">
                Tidak ada galeri.
            </p>
        @endif

    </section>

    {{-- Filter --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Filter Campaign</h2>
                <p class="card-subtitle">
                    Filter atau kategori tambahan yang terhubung dengan campaign.
                </p>
            </div>
        </div>

        @if($campaign->campaignFilter->count())
            <div class="badge-group">
                @foreach($campaign->campaignFilter as $filter)
                    <span class="badge badge-blue">
                        {{ $filter->filter->nama_filter ?? '-' }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-muted">
                Tidak ada filter.
            </p>
        @endif

    </section>

    {{-- Update Campaign --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Update Campaign</h2>
                <p class="card-subtitle">
                    Riwayat update yang dibuat untuk campaign ini.
                </p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($campaign->campaignUpdates as $update)
                        <tr>
                            <td>
                                <p class="cell-title">
                                    {{ $update->judul_update }}
                                </p>
                            </td>

                            <td>
                                {{ $update->user->name ?? '-' }}
                            </td>

                            <td class="text-muted-strong">
                                {{ $update->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">
                                Belum ada update.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </section>

    {{-- Fundraiser Pendukung --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Fundraiser Pendukung</h2>
                <p class="card-subtitle">
                    Daftar user yang ikut menjadi fundraiser pendukung.
                </p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Nama User</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($campaign->campaignFundraisers as $fundraiser)
                        <tr>
                            <td>
                                <p class="cell-title">
                                    {{ $fundraiser->user->name ?? 'User tidak ditemukan' }}
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty-state">
                                Tidak ada fundraiser pendukung.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </section>

    {{-- Riwayat Verifikasi --}}
    @if($campaign->verified_by || $campaign->verified_at)

        <section class="ob-card ob-card-lg">

            <div class="card-topbar">
                <div>
                    <h2>Riwayat Verifikasi</h2>
                    <p class="card-subtitle">
                        Informasi admin yang melakukan verifikasi campaign.
                    </p>
                </div>
            </div>

            <table class="data-table data-table-kv">
                <tbody>
                    <tr>
                        <th>Diverifikasi Oleh</th>
                        <td>{{ optional($campaign->verifier)->name ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Tanggal Verifikasi</th>
                        <td>{{ optional($campaign->verified_at)->format('d M Y H:i') ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

        </section>

    @endif

    {{-- Aksi Verifikasi --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Aksi Verifikasi</h2>
                <p class="card-subtitle">
                    Setujui atau tolak campaign ini.
                </p>
            </div>
        </div>

        <div class="form-actions">

            @if($campaign->status !== 'approved')
                <form action="{{ route('admin.campaign.approve', $campaign) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle"></i>
                        <span>Approve</span>
                    </button>
                </form>
            @endif

            @if($campaign->status !== 'rejected')
                <form action="{{ route('admin.campaign.reject', $campaign) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn-danger">
                        <i class="bi bi-x-circle"></i>
                        <span>Reject</span>
                    </button>
                </form>
            @endif

        </div>

    </section>

@endsection