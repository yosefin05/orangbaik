@extends('layouts.admin')

@section('page-title', 'Detail Penggalang Dana')

@section('content')

    {{-- Profil Penggalang Dana --}}
    <section class="ob-card ob-card-lg profile-card">

        @if($penggalangDana->foto_profil)
            <img
                src="{{ asset('storage/' . $penggalangDana->foto_profil) }}"
                alt="{{ $penggalangDana->nama_penggalang }}"
                class="profile-photo">
        @else
            <div class="profile-photo table-avatar-placeholder">
                {{ strtoupper(substr($penggalangDana->nama_penggalang, 0, 1)) }}
            </div>
        @endif

        <div class="profile-info">
            <h2>{{ $penggalangDana->nama_penggalang }}</h2>

            <p class="profile-type">
                {{ ucfirst($penggalangDana->jenis_penggalang) }}
            </p>

            @if($penggalangDana->status === 'pending')
                <span class="badge badge-yellow">Pending</span>
            @elseif($penggalangDana->status === 'approved')
                <span class="badge badge-green">Approved</span>
            @else
                <span class="badge badge-red">Rejected</span>
            @endif
        </div>

    </section>

    {{-- Informasi Utama --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Informasi Penggalang Dana</h2>
                <p class="card-subtitle">
                    Detail data utama penggalang dana.
                </p>
            </div>

            <a href="{{ route('admin.penggalang_dana.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <table class="data-table data-table-kv">
            <tbody>
                <tr>
                    <th>User</th>
                    <td>{{ $penggalangDana->user->name ?? 'User tidak ditemukan' }}</td>
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

    </section>

    {{-- Deskripsi --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Deskripsi</h2>
                <p class="card-subtitle">
                    Penjelasan singkat tentang penggalang dana.
                </p>
            </div>
        </div>

        <p class="card-text">
            {{ $penggalangDana->deskripsi }}
        </p>

    </section>

    {{-- Visi & Misi --}}
    <div class="info-grid">

        <section class="ob-card ob-card-lg">
            <div class="card-topbar">
                <div>
                    <h2>Visi</h2>
                    <p class="card-subtitle">
                        Tujuan utama penggalang dana.
                    </p>
                </div>
            </div>

            <p class="card-text">
                {{ $penggalangDana->visi }}
            </p>
        </section>

        <section class="ob-card ob-card-lg">
            <div class="card-topbar">
                <div>
                    <h2>Misi</h2>
                    <p class="card-subtitle">
                        Langkah dan rencana penggalang dana.
                    </p>
                </div>
            </div>

            <p class="card-text">
                {{ $penggalangDana->misi }}
            </p>
        </section>

    </div>

    {{-- Media Sosial --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Media Sosial</h2>
                <p class="card-subtitle">
                    Akun media sosial penggalang dana.
                </p>
            </div>
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

    </section>

    {{-- Dokumen Verifikasi --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Dokumen Verifikasi</h2>
                <p class="card-subtitle">
                    Dokumen pendukung untuk proses verifikasi.
                </p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Nama Dokumen</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($penggalangDana->penggalangDanaDokumen as $dokumen)

                        <tr>
                            <td>
                                <p class="cell-title">
                                    {{ $dokumen->nama_dokumen }}
                                </p>
                            </td>

                            <td class="text-center">
                                <a
                                    href="{{ asset('storage/' . $dokumen->file_dokumen) }}"
                                    target="_blank"
                                    class="action-link link-blue">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Lihat Dokumen</span>
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

    </section>

    {{-- Riwayat Verifikasi --}}
    @if($penggalangDana->verified_by || $penggalangDana->verified_at)

        <section class="ob-card ob-card-lg">

            <div class="card-topbar">
                <div>
                    <h2>Riwayat Verifikasi</h2>
                    <p class="card-subtitle">
                        Informasi admin yang melakukan verifikasi.
                    </p>
                </div>
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

        </section>

    @endif

    {{-- Aksi Verifikasi --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Aksi Verifikasi</h2>
                <p class="card-subtitle">
                    Setujui atau tolak pengajuan penggalang dana.
                </p>
            </div>
        </div>

        <div class="form-actions">

            @if($penggalangDana->status !== 'approved')
                <form action="{{ route('admin.penggalang_dana.approve', $penggalangDana) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle"></i>
                        <span>Approve</span>
                    </button>
                </form>
            @endif

            @if($penggalangDana->status !== 'rejected')
                <form action="{{ route('admin.penggalang_dana.reject', $penggalangDana) }}" method="POST">
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