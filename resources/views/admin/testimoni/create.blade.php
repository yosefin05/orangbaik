@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">
        <div>
            <h2>Tambah Testimoni</h2>
            <span class="card-subtitle">
                Tambahkan testimoni baru yang akan ditampilkan pada website.
            </span>
        </div>

        <a href="{{ route('admin.testimoni.index') }}"
            class="btn-secondary">
            Kembali
        </a>
    </div>

    <form action="{{ route('admin.testimoni.store') }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf

        <div class="form-wrapper">

            <div class="form-group">
                <label>Foto Profil</label>

                <input type="file"
                    name="foto_profil"
                    accept="image/*"
                    required>

                @error('foto_profil')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label>Nama</label>

                <input type="text"
                    name="nama"
                    value="{{ old('nama') }}"
                    placeholder="Masukkan nama"
                    required>

                @error('nama')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label>Jabatan</label>

                <input type="text"
                    name="jabatan"
                    value="{{ old('jabatan') }}"
                    placeholder="Contoh: Donatur Tetap"
                    required>

                @error('jabatan')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label>Isi Testimoni</label>

                <textarea
                    name="isi_testimoni"
                    rows="5"
                    placeholder="Masukkan isi testimoni"
                    required>{{ old('isi_testimoni') }}</textarea>

                @error('isi_testimoni')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror
            </div>

        </div>

        <div class="form-footer">
            <button type="submit"
                class="btn-primary">
                Simpan Testimoni
            </button>
        </div>

    </form>

</div>

@endsection