<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Berita - OrangBaik.id</title>
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
                    <h1>Tambah Berita</h1>
                    <p>Buat berita atau informasi terbaru untuk platform OrangBaik.id</p>
                </div>
            </div>

            <form action="{{ route('penggalang.berita.store') }}" method="POST" enctype="multipart/form-data" class="update-grid-layout" novalidate>
                @csrf

                {{-- LEFT COLUMN: FORM --}}
                <div class="update-main-content">
                    <div class="custom-card editor-card">
                        <div class="card-header-styled">
                            <div class="icon-badge"><i class="bi bi-newspaper"></i></div>
                            <div>
                                <h2>Form Berita</h2>
                                <p>Isi informasi berita yang ingin dipublikasikan</p>
                            </div>
                        </div>

                        <div class="card-body-styled">
                            {{-- Judul --}}
                            <div class="form-group-custom">
                                <label for="judul">Judul Berita <span class="req">*</span></label>
                                <div class="input-icon-wrapper">
                                    <input type="text" id="judul" name="judul" value="{{ old('judul') }}"
                                        placeholder="Contoh: Penyaluran Bantuan Tahap 1 Selesai" required>
                                </div>
                                @error('judul')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Custom Slug --}}
                            <div class="form-group-custom">
                                <label for="custom_slug">Link Berita <small>(Opsional)</small></label>
                                <input type="text" id="custom_slug" name="custom_slug" value="{{ old('custom_slug') }}"
                                    placeholder="contoh: penyaluran-bantuan-tahap-1">
                                <small style="color: var(--muted); font-size: 12px;">Kosongkan untuk menggunakan slug otomatis dari judul.</small>
                                @error('custom_slug')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Thumbnail --}}
                            <div class="form-group-custom">
                                <label for="thumbnail">Thumbnail <span class="req">*</span></label>
                                <div class="upload-dropzone" id="thumbnailDropzone">
                                    <div class="upload-placeholder" id="thumbnailPlaceholder">
                                        <i class="bi bi-image"></i>
                                        <p>Klik atau seret gambar thumbnail</p>
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
                                            <span>Pilih Gambar</span>
                                        </button>
                                        <span class="upload-hint">atau seret gambar ke sini</span>
                                    </div>
                                    <input type="file" id="thumbnail" name="thumbnail" class="upload-input-hidden" accept="image/*" required>
                                </div>
                                @error('thumbnail')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Isi Berita --}}
                            <div class="form-group-custom">
                                <label for="isi">Isi Berita <span class="req">*</span></label>
                                <x-rich-text-editor name="isi" id="isi" :value="old('isi')" />
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
                            <span class="info-label">Dipublikasikan sebagai</span>
                            <span class="info-value" style="font-weight: 600; color: var(--primary);">{{ Auth::user()->name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Kategori</span>
                            <span class="info-badge">BERITA</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary-action" id="submitBtn">
                        <i class="bi bi-send-fill"></i> Publikasikan Berita
                    </button>

                    <a href="{{ route('berita.index') }}" class="btn-secondary-action" style="display:flex; align-items:center; justify-content:center; gap:6px; padding: 12px; border-radius: 12px; background: #eef2f7; color: var(--text); text-align:center; margin-top: 8px; font-weight:500; transition:.2s ease;">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                </aside>
            </form>

        </div>
    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script>
        // Thumbnail preview
        const thumbnailInput = document.getElementById('thumbnail');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        const thumbnailPlaceholder = document.getElementById('thumbnailPlaceholder');
        const thumbnailPreviewImg = document.getElementById('thumbnailPreviewImg');
        const thumbnailRemoveBtn = document.getElementById('thumbnailRemoveBtn');
        const thumbnailDropzone = document.getElementById('thumbnailDropzone');
        const thumbnailUploadWrapper = document.getElementById('thumbnailUploadWrapper');

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

        thumbnailDropzone.addEventListener('click', function (e) {
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
