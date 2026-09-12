@extends('layouts.admin')

@section('page-title', 'Tambah Legalitas')

@section('content')
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Tambah Data Legalitas</h2>
                <p class="card-subtitle">Unggah dokumen izin atau legalitas baru untuk ditampilkan pada halaman Tentang Kami.</p>
            </div>
            <a href="{{ route('admin.legalitas.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form action="{{ route('admin.legalitas.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
            @csrf

            <div class="form-group">
                <label for="judul">Judul Legalitas <span class="text-danger">*</span></label>
                <input type="text" name="judul" id="judul" class="form-control" value="{{ old('judul') }}" required placeholder="Contoh: Izin Operasional LAZ">
            </div>

            <div class="form-group">
                <label for="nomor_legalitas">Nomor Legalitas / SK</label>
                <input type="text" name="nomor_legalitas" id="nomor_legalitas" class="form-control" value="{{ old('nomor_legalitas') }}" placeholder="Contoh: AHU-0012345.AH.01.04">
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi Singkat</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Keterangan singkat seputar dokumen ini">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="form-group">
                <label for="file">File Dokumen (Gambar JPG/PNG/WEBP atau PDF) <span class="text-danger">*</span></label>
                <input type="file" name="file" id="file" class="form-control" required accept="image/*,.pdf">
                <small class="text-muted">Maksimal 10MB.</small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="urutan">Urutan Tampil</label>
                    <input type="number" name="urutan" id="urutan" class="form-control" value="{{ old('urutan', 1) }}">
                </div>

                <div class="form-group col-md-6">
                    <label>Status Aktif</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Tampilkan di halaman publik</label>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-save"></i> Simpan Legalitas
                </button>
            </div>
        </form>
    </section>
@endsection
