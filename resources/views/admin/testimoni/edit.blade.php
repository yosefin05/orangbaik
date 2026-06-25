@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">
        <div>
            <h2>Edit Testimoni</h2>
            <span class="card-subtitle">
                Perbarui data testimoni yang ditampilkan pada website.
            </span>
        </div>

        <a href="{{ route('admin.testimoni.index') }}"
            class="btn-secondary">
            Kembali
        </a>
    </div>

    <form action="{{ route('admin.testimoni.update', $testimoni) }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="form-wrapper">

            <div class="form-group">
                <label>Foto Saat Ini</label>

                <div>
                    <img
                        src="{{ asset('storage/' . $testimoni->foto_profil) }}"
                        width="120"
                        height="120"
                        style="
                            border-radius:50%;
                            object-fit:cover;
                        ">
                </div>
            </div>

            <div class="form-group">
                <label>Ganti Foto Profil</label>

                <input
                    type="file"
                    name="foto_profil"
                    accept="image/*">

                @error('foto_profil')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label>Nama</label>

                <input
                    type="text"
                    name="nama"
                    value="{{ old('nama', $testimoni->nama) }}"
                    required>

                @error('nama')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <div class="form-group">
                <label>Jabatan</label>

                <input
                    type="text"
                    name="jabatan"
                    value="{{ old('jabatan', $testimoni->jabatan) }}"
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
                    rows="6"
                    required>{{ old('isi_testimoni', $testimoni->isi_testimoni) }}</textarea>

                @error('isi_testimoni')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                @enderror
            </div>

        </div>

        <div class="form-footer">
            <button
                type="submit"
                class="btn-primary">
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>

@endsection