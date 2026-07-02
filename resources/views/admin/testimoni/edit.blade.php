@extends('layouts.admin')

@section('page-title', 'Edit Testimoni')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Edit Testimoni</h2>
                <p class="card-subtitle">
                    Perbarui data testimoni yang ditampilkan pada website OrangBaik.id.
                </p>
            </div>

            <a href="{{ route('admin.testimoni.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <form
            action="{{ route('admin.testimoni.update', $testimoni) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="form-wrapper">

                <div class="form-group">
                    <label>Foto Saat Ini</label>

                    <div>
                        @if($testimoni->foto_profil)
                            <img
                                src="{{ asset('storage/' . $testimoni->foto_profil) }}"
                                alt="{{ $testimoni->nama }}"
                                class="current-photo">
                        @else
                            <div class="current-photo table-avatar-placeholder">
                                {{ strtoupper(substr($testimoni->nama, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label for="foto_profil">Ganti Foto Profil</label>

                    <input
                        type="file"
                        id="foto_profil"
                        name="foto_profil"
                        accept="image/*"
                        class="form-control">

                    @error('foto_profil')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nama">Nama</label>

                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="{{ old('nama', $testimoni->nama) }}"
                        class="form-control"
                        required>

                    @error('nama')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="jabatan">Jabatan</label>

                    <input
                        type="text"
                        id="jabatan"
                        name="jabatan"
                        value="{{ old('jabatan', $testimoni->jabatan) }}"
                        class="form-control"
                        required>

                    @error('jabatan')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="isi_testimoni">Isi Testimoni</label>

                    <textarea
                        id="isi_testimoni"
                        name="isi_testimoni"
                        rows="6"
                        class="form-control"
                        required>{{ old('isi_testimoni', $testimoni->isi_testimoni) }}</textarea>

                    @error('isi_testimoni')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

            </div>

            <div class="form-footer">
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save"></i>
                        <span>Simpan Perubahan</span>
                    </button>

                    <a href="{{ route('admin.testimoni.index') }}" class="btn-secondary">
                        Batal
                    </a>
                </div>
            </div>

        </form>

    </section>

@endsection