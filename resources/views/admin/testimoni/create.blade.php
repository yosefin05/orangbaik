@extends('layouts.admin')

@section('page-title', 'Tambah Testimoni')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Tambah Testimoni</h2>
                <p class="card-subtitle">
                    Tambahkan testimoni baru yang akan ditampilkan pada website OrangBaik.id.
                </p>
            </div>

            <a href="{{ route('admin.testimoni.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <form
            action="{{ route('admin.testimoni.store') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <div class="form-wrapper">

                <div class="form-group">
                    <label for="foto_profil">Foto Profil</label>

                    <input
                        type="file"
                        id="foto_profil"
                        name="foto_profil"
                        accept="image/*"
                        class="form-control"
                        required>

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
                        value="{{ old('nama') }}"
                        placeholder="Masukkan nama"
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
                        value="{{ old('jabatan') }}"
                        placeholder="Contoh: Donatur Tetap"
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
                        rows="5"
                        placeholder="Masukkan isi testimoni"
                        class="form-control"
                        required>{{ old('isi_testimoni') }}</textarea>

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
                        <span>Simpan Testimoni</span>
                    </button>

                    <a href="{{ route('admin.testimoni.index') }}" class="btn-secondary">
                        Batal
                    </a>
                </div>
            </div>

        </form>

    </section>

@endsection