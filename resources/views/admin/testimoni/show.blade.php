@extends('layouts.admin')

@section('page-title', 'Detail Testimoni')

@section('content')

    <section class="ob-card ob-card-lg detail-card">

        <div class="card-topbar">
            <div>
                <h2>Detail Testimoni</h2>
                <p class="card-subtitle">
                    Informasi lengkap testimoni yang ditampilkan pada website.
                </p>
            </div>

            <a href="{{ route('admin.testimoni.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <div class="detail-profile-preview">

            @if($testimoni->foto_profil)
                <img
                    src="{{ asset('storage/' . $testimoni->foto_profil) }}"
                    alt="{{ $testimoni->nama }}"
                    class="detail-avatar">
            @else
                <div class="detail-avatar detail-avatar-placeholder">
                    {{ strtoupper(substr($testimoni->nama, 0, 1)) }}
                </div>
            @endif

            <div>
                <h3>{{ $testimoni->nama }}</h3>
                <p>{{ $testimoni->jabatan }}</p>
            </div>

        </div>

        <div class="form-wrapper">

            <div class="form-group">
                <label>Nama</label>
                <input
                    type="text"
                    class="form-control"
                    value="{{ $testimoni->nama }}"
                    readonly>
            </div>

            <div class="form-group">
                <label>Jabatan</label>
                <input
                    type="text"
                    class="form-control"
                    value="{{ $testimoni->jabatan }}"
                    readonly>
            </div>

            <div class="form-group">
                <label>Uploader</label>
                <input
                    type="text"
                    class="form-control"
                    value="{{ $testimoni->user->name ?? 'User tidak ditemukan' }}"
                    readonly>
            </div>

            <div class="form-group">
                <label>Isi Testimoni</label>
                <textarea
                    rows="6"
                    class="form-control"
                    readonly>{{ $testimoni->isi_testimoni }}</textarea>
            </div>

        </div>

    </section>

@endsection