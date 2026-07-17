@extends('layouts.admin')
@section('page-title', 'Tambah Berita')
@section('content')
    <section class="ob-card ob-card-lg form-card">

        <div class="card-topbar">
            <div>
                <h2>Tambah Berita</h2>
                <p class="card-subtitle">
                    Buat artikel atau berita baru yang akan ditampilkan pada website OrangBaik.id.
                </p>
            </div>

            <a href="{{ route('admin.berita.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
        <x-alert-error />
        <form
            action="{{ route('admin.berita.store') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <div class="form-wrapper">

                <div class="form-group">
                    <label for="judul">Judul</label>

                    <input
                        type="text"
                        id="judul"
                        name="judul"
                        value="{{ old('judul') }}"
                        class="form-control"
                        placeholder="Masukkan judul berita"
                        required>
                </div>

                <div class="form-group">
                    <label for="thumbnail">Thumbnail</label>

                    <input
                        type="file"
                        id="thumbnail"
                        name="thumbnail"
                        class="form-control"
                        accept="image/*"
                        required>

                    <small class="text-muted">
                        Gunakan gambar utama untuk thumbnail berita.
                    </small>
                </div>

                <div class="form-group">
                    <label for="gambar">Galeri Gambar</label>

                    <input
                        type="file"
                        id="gambar"
                        name="gambar[]"
                        class="form-control"
                        accept="image/*"
                        multiple>

                    <small class="text-muted">
                        Maksimal 3 gambar dan ukuran tiap gambar maksimal 2 MB.
                    </small>
                </div>

                <div class="form-group">
                    <label for="isi">Isi Berita</label>

                    <textarea
                        id="isi"
                        name="isi"
                        rows="8"
                        class="form-control"
                        placeholder="Tulis isi berita di sini..."
                        required>{{ old('isi') }}</textarea>
                </div>

            </div>

            <div class="form-footer">
                <div class="form-actions">

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save"></i>
                        <span>Simpan Berita</span>
                    </button>

                    <a href="{{ route('admin.berita.index') }}" class="btn-secondary">
                        Batal
                    </a>

                </div>
            </div>

        </form>

    </section>

    <script>
        const inputGambar = document.getElementById('gambar');

        if (inputGambar) {
            inputGambar.addEventListener('change', function () {
                const files = this.files;
                const maxFiles = 3;
                const maxFileSize = 2 * 1024 * 1024;

                if (files.length > maxFiles) {
                    alert('Maksimal hanya boleh upload 3 gambar.');
                    this.value = '';
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    if (files[i].size > maxFileSize) {
                        alert('Ukuran tiap gambar maksimal 2 MB.');
                        this.value = '';
                        return;
                    }
                }
            });
        }
    </script>

@endsection