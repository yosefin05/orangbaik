@extends('layouts.admin')

@section('page-title', 'Detail Penggalang Dana')

@section('content')

<div class="page-header">
    <div>
        <h2>Detail Penggalang Dana</h2>
        <p>{{ $penggalangDana->nama_penggalang }}</p>
    </div>

    <a href="{{ route('admin.penggalang_dana.index') }}" class="btn-secondary">
        ← Kembali
    </a>
</div>

<!-- Profil Header -->
<div class="card profile-card">

    <img
        src="{{ asset('storage/' . $penggalangDana->foto_profil) }}"
        alt="{{ $penggalangDana->nama_penggalang }}"
        class="profile-photo"
    >

    <div class="profile-info">

        <h2>{{ $penggalangDana->nama_penggalang }}</h2>
        <p class="profile-type">{{ ucfirst($penggalangDana->jenis_penggalang) }}</p>

        @if($penggalangDana->status == 'pending')
            <span class="badge badge-yellow">Pending</span>
        @elseif($penggalangDana->status == 'approved')
            <span class="badge badge-green">Approved</span>
        @else
            <span class="badge badge-red">Rejected</span>
        @endif

    </div>

</div>

<!-- Informasi Utama -->
<div class="card">

    <div class="card-header">
        <h3>Informasi Penggalang Dana</h3>
    </div>

    <table class="data-table data-table-kv">
        <tbody>
            <tr>
                <th>User</th>
                <td>{{ $penggalangDana->user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $penggalangDana->email }}</td>
            </tr>
            <tr>
                <th>Nomor Telepon</th>
                <td>{{ $penggalangDana->no_telepon }}</td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td>{{ $penggalangDana->alamat }}</td>
            </tr>
            <tr>
                <th>Tanggal Daftar</th>
                <td>{{ $penggalangDana->created_at->format('d M Y H:i') }}</td>
            </tr>
        </tbody>
    </table>

</div>

<!-- Deskripsi, Visi, Misi -->
<div class="card">
    <div class="card-header">
        <h3>Deskripsi</h3>
    </div>
    <p class="card-text">{{ $penggalangDana->deskripsi }}</p>
</div>

<div class="info-grid">

    <div class="card">
        <div class="card-header">
            <h3>Visi</h3>
        </div>
        <p class="card-text">{{ $penggalangDana->visi }}</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Misi</h3>
        </div>
        <p class="card-text">{{ $penggalangDana->misi }}</p>
    </div>

</div>

<!-- Media Sosial -->
<div class="card">

    <div class="card-header">
        <h3>Media Sosial</h3>
    </div>

    <table class="data-table data-table-kv">
        <tbody>
            <tr>
                <th>Instagram</th>
                <td>{{ $penggalangDana->instagram ?: '-' }}</td>
            </tr>
            <tr>
                <th>Facebook</th>
                <td>{{ $penggalangDana->facebook ?: '-' }}</td>
            </tr>
            <tr>
                <th>Youtube</th>
                <td>{{ $penggalangDana->youtube ?: '-' }}</td>
            </tr>
            <tr>
                <th>Tiktok</th>
                <td>{{ $penggalangDana->tiktok ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

</div>

<!-- Dokumen Verifikasi (tetap list biasa, BUKAN key-value) -->
<div class="card">

    <div class="card-header">
        <h3>Dokumen Verifikasi</h3>
    </div>

    <div class="table-wrapper">
        <table class="data-table">

            <thead>
                <tr>
                    <th>Nama Dokumen</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse($penggalangDana->penggalangDanaDokumen as $dokumen)

                    <tr>
                        <td>{{ $dokumen->nama_dokumen }}</td>
                        <td>
                            <a
                                href="{{ asset('storage/' . $dokumen->file_dokumen) }}"
                                target="_blank"
                                class="action-link link-blue"
                            >
                                Lihat Dokumen
                            </a>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="2" class="empty-state">
                            Tidak ada dokumen.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
    </div>

</div>

<!-- Riwayat Verifikasi -->
@if($penggalangDana->verified_by || $penggalangDana->verified_at)

<div class="card">

    <div class="card-header">
        <h3>Riwayat Verifikasi</h3>
    </div>

    <table class="data-table data-table-kv">
        <tbody>
            <tr>
                <th>Diverifikasi Oleh</th>
                <td>{{ optional($penggalangDana->verifier)->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Tanggal Verifikasi</th>
                <td>{{ optional($penggalangDana->verified_at)->format('d M Y H:i') ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

</div>

@endif

<!-- Aksi Verifikasi -->
<div class="card">

    <div class="card-header">
        <h3>Aksi Verifikasi</h3>
    </div>

    <div class="form-actions">

        @if($penggalangDana->status != 'approved')

            <form action="{{ route('admin.penggalang_dana.approve', $penggalangDana) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-primary">
                    Approve
                </button>
            </form>

        @endif

        @if($penggalangDana->status != 'rejected')

            <form action="{{ route('admin.penggalang_dana.reject', $penggalangDana) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-danger">
                    Reject
                </button>
            </form>

        @endif

    </div>

</div>

@endsection