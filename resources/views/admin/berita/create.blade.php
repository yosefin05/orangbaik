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

                {{-- THUMBNAIL --}}
                <div class="form-group">
                    <label for="thumbnail">Thumbnail <span class="text-danger">*</span></label>

                    <div class="upload-dropzone" id="thumbnailDropzone">

                        {{-- Placeholder --}}
                        <div class="upload-placeholder" id="thumbnailPlaceholder">
                            <i class="bi bi-image"></i>
                            <p>Klik atau seret gambar untuk thumbnail</p>
                            <span class="text-muted">Format: JPG, PNG, JPEG • Maks: 2MB</span>
                        </div>

                        {{-- PREVIEW - PAKAI thumbnail-preview --}}
                        <div class="upload-preview thumbnail-preview" id="thumbnailPreview" style="display: none;">
                            <div class="thumbnail-wrapper">
                                <img id="thumbnailPreviewImg" src="#" alt="Thumbnail Preview" />
                            </div>
                            <button type="button" class="btn-remove-thumbnail" id="thumbnailRemoveBtn"
                                title="Hapus thumbnail">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        {{-- Tombol Upload --}}
                        <div class="upload-btn-wrapper" id="thumbnailUploadWrapper">
                            <button type="button" class="upload-btn" onclick="document.getElementById('thumbnail').click()">
                                <i class="bi bi-cloud-upload"></i>
                                <span>Pilih Thumbnail</span>
                            </button>
                            <span class="upload-hint">atau seret gambar ke sini</span>
                        </div>

                        <input type="file" id="thumbnail" name="thumbnail" class="upload-input-hidden" accept="image/*"
                            required />
                    </div>

                    @error('thumbnail')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ====================================================== --}}
                {{-- ISI BERITA --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="isi">Isi Berita <span class="text-danger">*</span></label>
                    <x-rich-text-editor name="isi" id="isi" :value="old('isi')" />
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