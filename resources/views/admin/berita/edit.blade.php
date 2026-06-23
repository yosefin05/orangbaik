@extends('layouts.admin')

@section('content')

    <div class="page-header">
        <div>
            <h2>Edit Berita</h2>
            <p>Perbarui artikel "{{ $berita->judul }}"</p>
        </div>
    </div>

    <div class="card form-card">

        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.berita.update', $berita) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="judul">Judul</label>

                <input type="text" id="judul" name="judul" value="{{ old('judul', $berita->judul) }}" class="form-control">
            </div>

            <div class="form-group">

                <label>Thumbnail Saat Ini</label>

                <img src="{{ asset('storage/' . $berita->thumbnail) }}" alt="{{ $berita->judul }}"
                    class="current-thumbnail">
            </div>

            <div class="form-group">
                <label for="thumbnail">
                    Ganti Thumbnail (Opsional)
                </label>

                <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
            </div>

            <div class="form-group">

                <label>
                    Galeri Saat Ini
                </label>

                <p class="text-muted">
                    {{ $berita->gambar->count() }}/3 gambar digunakan
                </p>

                <div class="gallery-grid">

                    @foreach($berita->gambar as $gambar)

                        <div class="gallery-item">

                            <img src="{{ asset('storage/' . $gambar->gambar) }}" alt="Galeri">

                            <button type="button" class="delete-image-btn" onclick="hapusGambar({{ $gambar->id }})">
                                ×
                            </button>

                        </div>

                    @endforeach

                </div>

            </div>

            <div class="form-group">

                <label for="gambar">
                    Tambah Gambar Galeri
                </label>

                <input type="file" id="gambar" name="gambar[]" class="form-control" multiple accept="image/*">

                <small>
                    Maksimal 3 gambar dan ukuran tiap gambar maksimal 2 MB.
                </small>

            </div>

            <div class="form-group">
                <label for="isi">
                    Isi Berita
                </label>

                <textarea id="isi" name="isi" rows="8" class="form-control">{{ old('isi', $berita->isi) }}</textarea>
            </div>

            <div class="form-actions">

                <button type="submit" class="btn-primary">
                    Simpan Perubahan
                </button>

                <a href="{{ route('admin.berita.show', $berita) }}" class="btn-secondary">
                    Detail
                </a>

                <a href="{{ route('admin.berita.index') }}" class="btn-secondary">
                    Kembali
                </a>

            </div>

        </form>

        @foreach($berita->gambar as $gambar)

            <form id="delete-image-{{ $gambar->id }}" action="{{ route('admin.berita-gambar.destroy', $gambar) }}" method="POST"
                style="display:none;">
                @csrf
                @method('DELETE')
            </form>

        @endforeach

        <script>
            function hapusGambar(id) {
                if (confirm('Hapus gambar ini?')) {
                    document
                        .getElementById('delete-image-' + id)
                        .submit();
                }
            }
        </script>

        <script>
            document.getElementById('gambar')
                .addEventListener('change', function () {

                    const files = this.files;

                    if (files.length > 3) {

                        alert(
                            'Maksimal hanya boleh upload 3 gambar.'
                        );

                        this.value = '';

                        return;
                    }

                    for (let i = 0; i < files.length; i++) {

                        if (files[i].size > 2 * 1024 * 1024) {

                            alert(
                                'Ukuran gambar maksimal 2 MB.'
                            );

                            this.value = '';

                            return;
                        }
                    }
                });
        </script>

@endsection