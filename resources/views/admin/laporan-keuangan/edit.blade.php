@extends('layouts.admin')

@section('page-title', 'Edit Laporan Keuangan')

@section('content')
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Edit Laporan Keuangan</h2>
                <p class="card-subtitle">Perbarui data atau unggah ulang file laporan tahun {{ $report->tahun }}.</p>
            </div>
            <a href="{{ route('admin.laporan-keuangan.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <form action="{{ route('admin.laporan-keuangan.update', $report) }}" method="POST" enctype="multipart/form-data" class="admin-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="tahun">Tahun Laporan <span class="text-danger">*</span></label>
                <input type="number" name="tahun" id="tahun" class="form-control" value="{{ old('tahun', $report->tahun) }}" required>
            </div>

            <div class="form-group">
                <label for="judul">Judul Laporan <span class="text-danger">*</span></label>
                <input type="text" name="judul" id="judul" class="form-control" value="{{ old('judul', $report->judul) }}" required>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi / Catatan Singkat</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $report->deskripsi) }}</textarea>
            </div>

            <div class="form-group">
                <label for="file">Ganti File Laporan (Kosongkan jika tidak diganti)</label>
                <input type="file" name="file" id="file" class="form-control" accept=".pdf,image/*">
                <small class="text-muted">File saat ini: <a href="{{ asset('storage/' . $report->file_path) }}" target="_blank">Lihat File Laporan</a></small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="urutan">Urutan</label>
                    <input type="number" name="urutan" id="urutan" class="form-control" value="{{ old('urutan', $report->urutan) }}">
                </div>

                <div class="form-group col-md-6">
                    <label>Status Aktif</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" {{ old('is_active', $report->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Tampilkan di halaman publik</label>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-save"></i> Perbarui Laporan
                </button>
            </div>
        </form>
    </section>
@endsection
