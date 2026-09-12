@extends('layouts.admin')

@section('page-title', 'Tambah Laporan Keuangan')

@section('content')
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Tambah Laporan Keuangan</h2>
                <p class="card-subtitle">Unggah dokumen laporan keuangan tahunan dalam bentuk PDF/gambar.</p>
            </div>
            <a href="{{ route('admin.laporan-keuangan.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form action="{{ route('admin.laporan-keuangan.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
            @csrf

            <div class="form-group">
                <label for="tahun">Tahun Laporan <span class="text-danger">*</span></label>
                <input type="number" name="tahun" id="tahun" class="form-control" value="{{ old('tahun', date('Y')) }}" required placeholder="Contoh: 2025">
                <small class="text-muted">Setiap tahun hanya diperbolehkan satu laporan keuangan.</small>
            </div>

            <div class="form-group">
                <label for="judul">Judul Laporan <span class="text-danger">*</span></label>
                <input type="text" name="judul" id="judul" class="form-control" value="{{ old('judul') }}" required placeholder="Contoh: Laporan Keuangan Tahunan 2025 Audit KAP">
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi / Catatan Singkat</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Ringkasan atau keterangan audit">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="form-group">
                <label for="file">File Laporan (PDF atau Gambar) <span class="text-danger">*</span></label>
                <input type="file" name="file" id="file" class="form-control" required accept=".pdf,image/*">
                <small class="text-muted">Format PDF sangat disarankan. Maksimal 15MB.</small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="urutan">Urutan</label>
                    <input type="number" name="urutan" id="urutan" class="form-control" value="{{ old('urutan', 0) }}">
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
                    <i class="bi bi-save"></i> Simpan Laporan
                </button>
            </div>
        </form>
    </section>
@endsection
