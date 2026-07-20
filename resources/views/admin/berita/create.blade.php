@extends('layouts.admin')

@section('page-title', 'Tambah Berita')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/berita/create.css') }}">
@endpush

@section('content')

    {{-- ========================================================== --}}
    {{-- HEADER CARD --}}
    {{-- ========================================================== --}}
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

        {{-- Alert Error --}}
        <x-alert-error />

        {{-- ========================================================== --}}
        {{-- FORM --}}
        {{-- ========================================================== --}}
        <form action="{{ route('admin.berita.store') }}" method="POST" enctype="multipart/form-data" id="beritaForm">

            @csrf

            <div class="form-wrapper">

                {{-- Judul --}}
                <div class="form-group">
                    <label for="judul">Judul <span class="text-danger">*</span></label>
                    <input type="text" id="judul" name="judul" value="{{ old('judul') }}" class="form-control"
                        placeholder="Masukkan judul berita" required />
                    @error('judul')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ====================================================== --}}
                {{-- THUMBNAIL --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="thumbnail">Thumbnail <span class="text-danger">*</span></label>

                    {{-- Dropzone Area --}}
                    <div class="upload-dropzone" id="thumbnailDropzone">
                        <div class="upload-placeholder" id="thumbnailPlaceholder">
                            <i class="bi bi-image"></i>
                            <p>Klik atau seret gambar untuk thumbnail</p>
                            <span class="text-muted">Format: JPG, PNG, JPEG • Maks: 2MB</span>
                        </div>
                        <div class="upload-preview" id="thumbnailPreview" style="display: none;">
                            <img id="thumbnailPreviewImg" src="#" alt="Thumbnail Preview" />
                            <button type="button" class="btn-remove-image" id="thumbnailRemoveBtn" title="Hapus thumbnail">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <input type="file" id="thumbnail" name="thumbnail" class="upload-input" accept="image/*" required />
                    </div>

                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Gunakan gambar utama untuk thumbnail berita. Ukuran optimal: 1200 x 630px.
                    </small>
                    @error('thumbnail')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ====================================================== --}}
                {{-- GALERI GAMBAR --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="gambar">Galeri Gambar</label>

                    {{-- Dropzone Area --}}
                    <div class="upload-dropzone" id="galleryDropzone">
                        <div class="upload-placeholder" id="galleryPlaceholder">
                            <i class="bi bi-images"></i>
                            <p>Klik atau seret gambar untuk galeri</p>
                            <span class="text-muted">Maksimal 3 gambar • Masing-masing maks 2MB</span>
                        </div>
                        <div class="gallery-preview-grid" id="galleryPreviewGrid">
                            {{-- Gallery previews akan di-render di sini --}}
                        </div>
                        <input type="file" id="gambar" name="gambar[]" class="upload-input" accept="image/*" multiple />
                    </div>

                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Maksimal 3 gambar dan ukuran tiap gambar maksimal 2 MB.
                    </small>
                    @error('gambar.*')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ====================================================== --}}
                {{-- ISI BERITA --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="isi">Isi Berita <span class="text-danger">*</span></label>
                    <textarea id="isi" name="isi" rows="10" class="form-control" placeholder="Tulis isi berita di sini..."
                        required>{{ old('isi') }}</textarea>
                    @error('isi')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- ========================================================== --}}
            {{-- FORM ACTIONS --}}
            {{-- ========================================================== --}}
            <div class="form-footer">
                <div class="form-actions">

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save"></i>
                        <span>Simpan Berita</span>
                    </button>

                    <a href="{{ route('admin.berita.index') }}" class="btn-secondary">
                        <i class="bi bi-x-circle"></i>
                        Batal
                    </a>

                </div>
            </div>

        </form>

    </section>

    @push('scripts')
        <script src="{{ asset('js/admin/berita-create.js') }}"></script>
    @endpush

@endsection