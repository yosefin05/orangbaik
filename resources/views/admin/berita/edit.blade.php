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

        <form action="{{ route('admin.berita.update', $berita) }}" method="POST" enctype="multipart/form-data"
            id="beritaForm">

            @csrf
            @method('PUT')

            <div class="form-wrapper">

                {{-- ====================================================== --}}
                {{-- JUDUL --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="judul">Judul <span class="text-danger">*</span></label>
                    <input type="text" id="judul" name="judul" value="{{ old('judul', $berita->judul) }}"
                        placeholder="Masukkan judul berita" class="form-control" required />
                    @error('judul')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="custom_slug">Link Berita <small>(Opsional)</small></label>
                    <input type="text" id="custom_slug" name="custom_slug"
                        value="{{ old('custom_slug', $berita->custom_slug) }}" placeholder="contoh: berita-banjir-jakarta"
                        class="form-control" />
                    <small class="text-muted">Kosongkan untuk menggunakan slug otomatis dari judul.</small>
                    @error('custom_slug')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ====================================================== --}}
                {{-- THUMBNAIL SAAT INI --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label>Thumbnail Saat Ini</label>

                    <div class="current-photo-wrapper">
                        @if ($berita->thumbnail)
                            <img src="{{ asset('storage/' . $berita->thumbnail) }}" alt="{{ $berita->judul }}"
                                class="current-thumbnail" />
                        @else
                            <div class="current-thumbnail table-avatar-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        @endif
                    </div>
                    <small class="text-muted">Thumbnail saat ini</small>
                </div>

                {{-- ====================================================== --}}
                {{-- GANTI THUMBNAIL - Dengan Preview --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="thumbnail">Ganti Thumbnail</label>

                    <div class="upload-dropzone" id="thumbnailDropzone">

                        {{-- Placeholder --}}
                        <div class="upload-placeholder" id="thumbnailPlaceholder">
                            <i class="bi bi-image"></i>
                            <p>Klik atau seret gambar untuk thumbnail baru</p>
                            <span class="text-muted">Format: JPG, PNG, JPEG • Maks: 2MB</span>
                        </div>

                        {{-- Preview --}}
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
                                <span>Pilih Thumbnail Baru</span>
                            </button>
                            <span class="upload-hint">atau seret gambar ke sini</span>
                        </div>

                        <input type="file" id="thumbnail" name="thumbnail" class="upload-input-hidden" accept="image/*" />
                    </div>

                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Kosongkan jika tidak ingin mengganti thumbnail.
                    </small>

                    @error('thumbnail')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ====================================================== --}}
                {{-- ISI BERITA --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="isi">Isi Berita <span class="text-danger">*</span></label>
                    <x-rich-text-editor name="isi" id="isi" :value="old('isi', $berita->isi)" />
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
                        <span>Simpan Perubahan</span>
                    </button>

                    <a href="{{ route('admin.berita.show', $berita) }}" class="btn-secondary">
                        <i class="bi bi-eye"></i>
                        <span>Detail</span>
                    </a>

                    <a href="{{ route('admin.berita.index') }}" class="btn-secondary">
                        <i class="bi bi-x-circle"></i>
                        Batal
                    </a>

                </div>
            </div>

        </form>

    </section>

@endsection