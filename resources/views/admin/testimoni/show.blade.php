@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">
        <div>
            <h2>Detail Testimoni</h2>
            <span class="card-subtitle">
                Informasi lengkap testimoni.
            </span>
        </div>

        <a href="{{ route('admin.testimoni.index') }}"
            class="btn-secondary">
            Kembali
        </a>
    </div>

    <div class="form-wrapper">

        <div style="text-align:center;">
            <img
                src="{{ asset('storage/' . $testimoni->foto_profil) }}"
                alt="{{ $testimoni->nama }}"
                width="150"
                height="150"
                style="
                    border-radius:50%;
                    object-fit:cover;
                ">
        </div>

        <div class="form-group">
            <label>Nama</label>
            <input
                type="text"
                value="{{ $testimoni->nama }}"
                readonly>
        </div>

        <div class="form-group">
            <label>Jabatan</label>
            <input
                type="text"
                value="{{ $testimoni->jabatan }}"
                readonly>
        </div>

        <div class="form-group">
            <label>Uploader</label>
            <input
                type="text"
                value="{{ $testimoni->user->name }}"
                readonly>
        </div>

        <div class="form-group">
            <label>Isi Testimoni</label>

            <textarea rows="6" readonly>{{ $testimoni->isi_testimoni }}</textarea>
        </div>

    </div>

</div>

@endsection