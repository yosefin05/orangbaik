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
            enctype="multipart/form-data"
            id="beritaForm">

            @csrf
            @method('PUT')

            <div class="form-wrapper">

                {{-- ====================================================== --}}
                {{-- JUDUL --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="judul">Judul <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="judul"
                        name="judul"
                        value="{{ old('judul', $berita->judul) }}"
                        placeholder="Masukkan judul berita"
                        class="form-control"
                        required
                    />
                    @error('judul')
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
                            <img
                                src="{{ asset('storage/' . $berita->thumbnail) }}"
                                alt="{{ $berita->judul }}"
                                class="current-thumbnail"
                            />
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
                            <button type="button" class="btn-remove-thumbnail" id="thumbnailRemoveBtn" title="Hapus thumbnail">
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

                        <input
                            type="file"
                            id="thumbnail"
                            name="thumbnail"
                            class="upload-input-hidden"
                            accept="image/*"
                        />
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
                {{-- GALERI SAAT INI --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label>Galeri Saat Ini</label>

                    <p class="text-muted">
                        {{ $berita->gambar->count() }}/3 gambar digunakan.
                    </p>

                    @if ($berita->gambar->count() > 0)
                        <div class="gallery-grid">

                            @foreach ($berita->gambar as $gambar)
                                <div class="gallery-item">
                                    <img
                                        src="{{ asset('storage/' . $gambar->gambar) }}"
                                        alt="Galeri {{ $loop->iteration }}"
                                    />
                                    <button
                                        type="button"
                                        class="delete-image-btn"
                                        onclick="hapusGambar({{ $gambar->id }})"
                                        title="Hapus gambar"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <span class="gallery-index">{{ $loop->iteration }}</span>
                                </div>
                            @endforeach

                        </div>
                    @else
                        <p class="text-muted">
                            Belum ada gambar galeri.
                        </p>
                    @endif
                </div>

                {{-- ====================================================== --}}
                {{-- TAMBAH GAMBAR GALERI - Dengan Preview --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="gambar">Tambah Gambar Galeri</label>

                    <div class="upload-dropzone" id="galleryDropzone">
                        
                        {{-- Placeholder --}}
                        <div class="upload-placeholder" id="galleryPlaceholder">
                            <i class="bi bi-images"></i>
                            <p>Klik atau seret gambar untuk galeri</p>
                            <span class="text-muted">Maksimal total 3 gambar • Masing-masing maks 2MB</span>
                        </div>

                        {{-- Gallery Grid --}}
                        <div class="gallery-preview-grid" id="galleryPreviewGrid">
                            {{-- Gallery previews akan di-render di sini --}}
                        </div>

                        {{-- Tombol Upload --}}
                        <div class="upload-btn-wrapper" id="galleryUploadWrapper">
                            <button type="button" class="upload-btn" onclick="document.getElementById('gambar').click()">
                                <i class="bi bi-cloud-upload"></i>
                                <span>Tambah Gambar</span>
                            </button>
                            <span class="upload-hint">atau seret gambar ke sini</span>
                        </div>

                        <input
                            type="file"
                            id="gambar"
                            name="gambar[]"
                            class="upload-input-hidden"
                            accept="image/*"
                            multiple
                            data-current-count="{{ $berita->gambar->count() }}"
                        />
                    </div>

                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Maksimal total 3 gambar. Ukuran tiap gambar maksimal 2 MB.
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
                    <textarea
                        id="isi"
                        name="isi"
                        rows="8"
                        placeholder="Masukkan isi berita"
                        class="form-control"
                        required
                    >{{ old('isi', $berita->isi) }}</textarea>
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

        {{-- ========================================================== --}}
        {{-- FORM HAPUS GAMBAR (tersembunyi) --}}
        {{-- ========================================================== --}}
        @foreach ($berita->gambar as $gambar)
            <form
                id="delete-image-{{ $gambar->id }}"
                action="{{ route('admin.berita-gambar.destroy', $gambar) }}"
                method="POST"
                style="display: none;"
            >
                @csrf
                @method('DELETE')
            </form>
        @endforeach

    </section>

@endsection

{{-- ========================================================== --}}
{{-- SCRIPT - Hanya untuk hapus gambar existing                 --}}
{{-- ========================================================== --}}
@push('scripts')
    <script>
        function hapusGambar(id) {
            if (confirm('Yakin ingin menghapus gambar ini?')) {
                document.getElementById('delete-image-' + id).submit();
            }
        }

        // ==========================================================
        // VALIDASI GAMBAR GALERI (tambah)
        // ==========================================================
        const inputGambar = document.getElementById('gambar');

        if (inputGambar) {
            inputGambar.addEventListener('change', function() {
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
@endpush