@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <h2>Tambah Berita</h2>
        <p>Buat artikel atau berita baru</p>
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
        action="{{ route('admin.berita.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="form-group">
            <label for="judul">Judul</label>
            <input
                type="text"
                id="judul"
                name="judul"
                value="{{ old('judul') }}"
                class="form-control"
                placeholder="Judul berita"
                required
            >
        </div>

        <div class="form-group">
            <label for="thumbnail">Thumbnail</label>
            <input
                type="file"
                id="thumbnail"
                name="thumbnail"
                class="form-control"
                accept="image/*"
                required
            >
        </div>

        <div class="form-group">
            <label for="gambar">
                Galeri Gambar
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
                Bisa upload lebih dari satu gambar.
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
                required
            >{{ old('isi') }}</textarea>
        </div>

        <div class="form-actions">

            <button
                type="submit"
                class="btn-primary"
            >
                Simpan
            </button>

            <a
                href="{{ route('admin.berita.index') }}"
                class="btn-secondary"
            >
                Batal
            </a>

        </div>

    </form>

</div>

@endsection