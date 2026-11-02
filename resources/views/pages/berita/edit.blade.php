<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berita - OrangBaik.id</title>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/campaign-update.css') }}">
</head>
<body>
    @include('components.header')

    <main class="campaign-update-page">
        <div class="container">

            <x-alert-error />

            <div class="update-top-bar">
                <a href="{{ route('berita.index') }}" class="back-link">
                    <i class="bi bi-arrow-left"></i> Kembali ke Berita
                </a>
                <div class="header-titles">
                    <h1>Edit Berita</h1>
                    <p>Perbarui informasi berita: <strong>{{ $berita->judul }}</strong></p>
                </div>
            </div>

            <form action="{{ route('penggalang.berita.update', $berita) }}" method="POST" enctype="multipart/form-data" class="update-grid-layout" novalidate>
                @csrf
                @method('PUT')

                {{-- LEFT COLUMN: FORM --}}
                <div class="update-main-content">
                    <div class="custom-card editor-card">
                        <div class="card-header-styled">
                            <div class="icon-badge"><i class="bi bi-pencil-square"></i></div>
                            <div>
                                <h2>Form Edit Berita</h2>
                                <p>Perbarui informasi berita</p>
                            </div>
                        </div>

                        <div class="card-body-styled">
                            {{-- Judul --}}
                            <div class="form-group-custom">
                                <label for="judul">Judul Berita <span class="req">*</span></label>
                                <input type="text" id="judul" name="judul" value="{{ old('judul', $berita->judul) }}"
                                    placeholder="Judul berita" required>
                                @error('judul')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Custom Slug --}}
                            <div class="form-group-custom">
                                <label for="custom_slug">Link Berita <small>(Opsional)</small></label>
                                <input type="text" id="custom_slug" name="custom_slug"
                                    value="{{ old('custom_slug', $berita->custom_slug) }}"
                                    placeholder="contoh: judul-berita-saya">
                                <small style="color: var(--muted); font-size: 12px;">Kosongkan untuk menggunakan slug dari judul.</small>
                                @error('custom_slug')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Thumbnail --}}
                            <div class="form-group-custom">
                                <label>Thumbnail</label>
                                @if ($berita->thumbnail)
                                    <div style="margin-bottom: 12px; border-radius: 10px; overflow: hidden; max-width: 300px;">
                                        <img src="{{ asset('storage/' . $berita->thumbnail) }}"
                                            alt="Thumbnail Saat Ini" style="width: 100%; border-radius: 10px; display:block;">
                                        <small style="color: var(--muted); font-size: 12px; margin-top: 6px; display:block;">Thumbnail saat ini. Upload gambar baru untuk menggantinya.</small>
                                    </div>
                                @endif
                                <div class="upload-dropzone" id="thumbnailDropzone">
                                    <div class="upload-placeholder" id="thumbnailPlaceholder">
                                        <i class="bi bi-image"></i>
                                        <p>Klik untuk upload thumbnail baru</p>
                                        <span class="text-muted">Format: JPG, PNG, WebP • Maks: 2MB</span>
                                    </div>
                                    <div class="upload-preview thumbnail-preview" id="thumbnailPreview" style="display: none;">
                                        <div class="thumbnail-wrapper">
                                            <img id="thumbnailPreviewImg" src="#" alt="Preview" />
                                        </div>
                                        <button type="button" class="btn-remove-thumbnail" id="thumbnailRemoveBtn" title="Hapus">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <div class="upload-btn-wrapper" id="thumbnailUploadWrapper">
                                        <button type="button" class="upload-btn" onclick="document.getElementById('thumbnail').click()">
                                            <i class="bi bi-cloud-upload"></i>
                                            <span>Pilih Gambar Baru</span>
                                        </button>
                                        <span class="upload-hint">atau seret gambar ke sini</span>
                                    </div>
                                    <input type="file" id="thumbnail" name="thumbnail" class="upload-input-hidden" accept="image/*">
                                </div>
                                @error('thumbnail')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Isi Berita --}}
                            <div class="form-group-custom">
                                <label for="isi">Isi Berita <span class="req">*</span></label>
                                <x-rich-text-editor name="isi" id="isi" :value="old('isi', $berita->isi)" />
                                @error('isi')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: SIDEBAR --}}
                <aside class="update-sidebar-content">
                    <div class="custom-card info-card">
                        <div class="info-row">
                            <span class="info-label">Dipublikasikan</span>
                            <span class="info-value">{{ $berita->created_at->translatedFormat('d F Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Penulis</span>
                            <span class="info-value" style="font-weight: 600; color: var(--primary);">{{ $berita->user->name ?? 'Unknown' }}</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary-action">
                        <i class="bi bi-save-fill"></i> Simpan Perubahan
                    </button>

                    <a href="{{ route('berita.show', $berita->custom_slug ?? $berita->slug) }}"
                        style="display:flex; align-items:center; justify-content:center; gap:6px; padding: 12px; border-radius: 12px; background: #eef2f7; color: var(--text); text-align:center; margin-top: 8px; font-weight:500; transition:.2s ease;">
                        <i class="bi bi-eye"></i> Lihat Berita
                    </a>

                    {{-- Hapus --}}
                    @if (Auth::id() === $berita->user_id || Auth::user()->isAdmin())
                        <form action="{{ route('penggalang.berita.destroy', $berita) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus berita ini?')"
                            style="margin-top: 8px;">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                style="width:100%; display:flex; align-items:center; justify-content:center; gap:6px; padding: 12px; border-radius: 12px; background: var(--danger-light); color: var(--danger); border:none; cursor:pointer; font-weight:500; transition:.2s ease;">
                                <i class="bi bi-trash"></i> Hapus Berita
                            </button>
                        </form>
                    @endif
                </aside>
            </form>

        </div>
    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script>
        const thumbnailInput = document.getElementById('thumbnail');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        const thumbnailPlaceholder = document.getElementById('thumbnailPlaceholder');
        const thumbnailPreviewImg = document.getElementById('thumbnailPreviewImg');
        const thumbnailRemoveBtn = document.getElementById('thumbnailRemoveBtn');
        const thumbnailDropzone = document.getElementById('thumbnailDropzone');

        thumbnailInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    thumbnailPreviewImg.src = e.target.result;
                    thumbnailPreview.style.display = 'flex';
                    thumbnailPlaceholder.style.display = 'none';
                    thumbnailDropzone.classList.add('has-preview');
                };
                reader.readAsDataURL(file);
            }
        });

        thumbnailRemoveBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            thumbnailInput.value = '';
            thumbnailPreview.style.display = 'none';
            thumbnailPlaceholder.style.display = 'flex';
            thumbnailDropzone.classList.remove('has-preview');
        });

        thumbnailDropzone.addEventListener('click', function () {
            if (!thumbnailDropzone.classList.contains('has-preview')) {
                thumbnailInput.click();
            }
        });

        thumbnailDropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        thumbnailDropzone.addEventListener('dragleave', function () {
            this.classList.remove('dragover');
        });

        thumbnailDropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                thumbnailInput.files = e.dataTransfer.files;
                thumbnailInput.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>
