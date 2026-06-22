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

    <form
        action="{{ route('admin.berita.update', $berita) }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="judul">Judul</label>

            <input
                type="text"
                id="judul"
                name="judul"
                value="{{ old('judul', $berita->judul) }}"
                class="form-control"
            >
        </div>

        <div class="form-group">
            <label>Thumbnail Saat Ini</label>

            <img
                src="{{ asset('storage/' . $berita->thumbnail) }}"
                alt="{{ $berita->judul }}"
                class="current-thumbnail"
            >
        </div>

        <div class="form-group">
            <label for="thumbnail">
                Ganti Thumbnail (Opsional)
            </label>

            <input
                type="file"
                id="thumbnail"
                name="thumbnail"
                class="form-control"
                accept="image/*"
            >
        </div>

        <div class="form-group">
            <label>
                Galeri Saat Ini
            </label>

            @if($berita->gambar->count())

                <div class="gallery-grid">

                    @foreach($berita->gambar as $gambar)

                        <div class="gallery-item">

                            <img
                                src="{{ asset('storage/' . $gambar->gambar) }}"
                                alt="Galeri"
                            >

                            <small>
                                {{ basename($gambar->gambar) }}
                            </small>

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
            <label for="gambar">
                Tambah Gambar Galeri
            </label>

            <input
                type="file"
                id="gambar"
                name="gambar[]"
                class="form-control"
                accept="image/*"
                multiple
            >

            <small>
                Upload gambar tambahan untuk galeri berita.
            </small>
        </div>

        <div class="form-group">
            <label for="isi">
                Isi Berita
            </label>

            <textarea
                id="isi"
                name="isi"
                rows="8"
                class="form-control"
            >{{ old('isi', $berita->isi) }}</textarea>
        </div>

        <div class="form-actions">

            <button
                type="submit"
                class="btn-primary"
            >
                Simpan Perubahan
            </button>

            <a
                href="{{ route('admin.berita.show', $berita) }}"
                class="btn-secondary"
            >
                Detail
            </a>

            <a
                href="{{ route('admin.berita.index') }}"
                class="btn-secondary"
            >
                Kembali
            </a>

        </div>

    </form>

</div>

@endsection