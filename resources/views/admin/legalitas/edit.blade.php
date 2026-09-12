@extends('layouts.admin')

@section('page-title', 'Edit Legalitas')

@section('content')
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Edit Data Legalitas</h2>
                <p class="card-subtitle">Ubah informasi atau perbarui file dokumen legalitas.</p>
            </div>
            <a href="{{ route('admin.legalitas.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form action="{{ route('admin.legalitas.update', $legalitas) }}" method="POST" enctype="multipart/form-data" class="admin-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="judul">Judul Legalitas <span class="text-danger">*</span></label>
                <input type="text" name="judul" id="judul" class="form-control" value="{{ old('judul', $legalitas->judul) }}" required>
            </div>

            <div class="form-group">
                <label for="nomor_legalitas">Nomor Legalitas / SK</label>
                <input type="text" name="nomor_legalitas" id="nomor_legalitas" class="form-control" value="{{ old('nomor_legalitas', $legalitas->nomor_legalitas) }}">
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi Singkat</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $legalitas->deskripsi) }}</textarea>
            </div>

            <div class="form-group">
                <label for="file">Ganti File Dokumen (Kosongkan jika tidak diganti)</label>
                <input type="file" name="file" id="file" class="form-control" accept="image/*,.pdf">
                <small class="text-muted">File saat ini: <a href="{{ asset('storage/' . $legalitas->file_path) }}" target="_blank">Lihat Dokumen</a></small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="urutan">Urutan Tampil</label>
                    <input type="number" name="urutan" id="urutan" class="form-control" value="{{ old('urutan', $legalitas->urutan) }}">
                </div>

                <div class="form-group col-md-6">
                    <label>Status Aktif</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" {{ old('is_active', $legalitas->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Tampilkan di halaman publik</label>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-save"></i> Perbarui Legalitas
                </button>
            </div>
        </form>
    </section>
@endsection
