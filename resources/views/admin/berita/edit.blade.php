@extends('layouts.admin')

@section('page-title', 'Edit Berita')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/berita/edit.css') }}">
@endpush

@section('content')

    <section class="ob-card ob-card-lg form-card">

        <div class="card-topbar">
            <div>
                <h2>Edit Berita</h2>
                <p class="card-subtitle">
                    Perbarui artikel "{{ $berita->judul }}" yang ditampilkan pada website.
                </p>
            </div>

            <a href="{{ route('admin.berita.index') }}" class="btn-secondary">
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

        <form
            action="{{ route('admin.berita.update', $berita) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="form-wrapper">

                <div class="form-group">
                    <label for="judul">Judul</label>

                    <input
                        type="text"
                        id="judul"
                        name="judul"
                        value="{{ old('judul', $berita->judul) }}"
                        placeholder="Masukkan judul berita"
                        class="form-control"
                        required>
                </div>

                <div class="form-group">
                    <label>Thumbnail Saat Ini</label>

                    @if($berita->thumbnail)
                        <img
                            src="{{ asset('storage/' . $berita->thumbnail) }}"
                            alt="{{ $berita->judul }}"
                            class="current-thumbnail">
                    @else
                        <p class="text-muted">
                            Belum ada thumbnail.
                        </p>
                    @endif
                </div>

                <div class="form-group">
                    <label for="thumbnail">Ganti Thumbnail</label>

                    <input
                        type="file"
                        id="thumbnail"
                        name="thumbnail"
                        class="form-control"
                        accept="image/*">

                    <small class="text-muted">
                        Kosongkan jika tidak ingin mengganti thumbnail.
                    </small>
                </div>

                <div class="form-group">
                    <label>Galeri Saat Ini</label>

                    <p class="text-muted">
                        {{ $berita->gambar->count() }}/3 gambar digunakan.
                    </p>

                    @if($berita->gambar->count() > 0)

                        <div class="gallery-grid">

                            @foreach($berita->gambar as $gambar)

                                <div class="gallery-item">

                                    <img
                                        src="{{ asset('storage/' . $gambar->gambar) }}"
                                        alt="Galeri {{ $loop->iteration }}">

                                    <button
                                        type="button"
                                        class="delete-image-btn"
                                        onclick="hapusGambar({{ $gambar->id }})">
                                        <i class="bi bi-x"></i>
                                    </button>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <p class="text-muted">
                            Belum ada gambar galeri.
                        </p>

                    @endif
                </div>

                <div class="form-group">
                    <label for="gambar">Tambah Gambar Galeri</label>

                    <input
                        type="file"
                        id="gambar"
                        name="gambar[]"
                        class="form-control"
                        multiple
                        accept="image/*"
                        data-current-count="{{ $berita->gambar->count() }}">

                    <small class="text-muted">
                        Maksimal total 3 gambar. Ukuran tiap gambar maksimal 2 MB.
                    </small>
                </div>

                <div class="form-group">
                    <label for="isi">Isi Berita</label>

                    <textarea
                        id="isi"
                        name="isi"
                        rows="8"
                        placeholder="Masukkan isi berita"
                        class="form-control"
                        required>{{ old('isi', $berita->isi) }}</textarea>
                </div>

            </div>

            <div class="form-footer">
                <div class="form-actions">

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save"></i>
                        <span>Simpan Perubahan</span>
                    </button>

                    <a href="{{ route('admin.berita.show', $berita) }}" class="btn-secondary">
                        <i class="bi bi-eye"></i>
                        <span>Detail</span>
                    </a>

                    <a href="{{ route('admin.berita.index') }}" class="btn-secondary">
                        Batal
                    </a>

                </div>
            </div>

        </form>

        @foreach($berita->gambar as $gambar)

            <form
                id="delete-image-{{ $gambar->id }}"
                action="{{ route('admin.berita-gambar.destroy', $gambar) }}"
                method="POST"
                style="display: none;">

                @csrf
                @method('DELETE')

            </form>

        @endforeach

    </section>

    <script>
        function hapusGambar(id) {
            if (confirm('Yakin ingin menghapus gambar ini?')) {
                document
                    .getElementById('delete-image-' + id)
                    .submit();
            }
        }

        const inputGambar = document.getElementById('gambar');

        if (inputGambar) {
            inputGambar.addEventListener('change', function () {
                const files = this.files;
                const currentCount = Number(this.dataset.currentCount || 0);
                const maxTotal = 3;
                const maxFileSize = 2 * 1024 * 1024;
                const remainingSlot = maxTotal - currentCount;

                if (files.length > remainingSlot) {
                    alert('Sisa slot galeri hanya ' + remainingSlot + ' gambar.');
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