@extends('layouts.admin')

@section('page-title', 'Tambah Syarat & Ketentuan')

@section('content')

    <section class="ob-card ob-card-lg form-card">

        <div class="card-topbar">
            <div>
                <h2>Tambah Bagian</h2>
                <p class="card-subtitle">
                    Tambahkan bagian baru pada halaman syarat dan ketentuan.
                </p>
            </div>

            <a href="{{ route('admin.syarat-ketentuan.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.syarat-ketentuan.store') }}" method="POST">
            @csrf

            <div class="form-wrapper">
                <div class="form-group">
                    <label for="judul">Judul Bagian</label>
                    <input
                        type="text"
                        id="judul"
                        name="judul"
                        value="{{ old('judul') }}"
                        placeholder="Contoh: Ketentuan Donasi"
                        class="form-control"
                        required>
                </div>

                <div class="form-group">
                    <label for="isi">Isi</label>
                    <textarea
                        id="isi"
                        name="isi"
                        rows="8"
                        placeholder="Satu paragraf per baris."
                        class="form-control"
                        required>{{ old('isi') }}</textarea>
                    <small class="text-muted">Setiap baris baru akan ditampilkan sebagai paragraf terpisah di halaman publik.</small>
                </div>

                <div class="form-group">
                    <label for="urutan">Urutan</label>
                    <input
                        type="number"
                        id="urutan"
                        name="urutan"
                        min="0"
                        value="{{ old('urutan') }}"
                        placeholder="Kosongkan untuk otomatis"
                        class="form-control">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        Tampilkan di halaman publik
                    </label>
                </div>
            </div>

            <div class="form-footer">
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save"></i>
                        <span>Simpan</span>
                    </button>
                    <a href="{{ route('admin.syarat-ketentuan.index') }}" class="btn-secondary">Batal</a>
                </div>
            </div>
        </form>

    </section>

@endsection
