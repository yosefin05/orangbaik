<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Update - {{ $campaign->judul }} - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/campaign-update.css') }}">
</head>

<body>

    @include('components.header')

    <main class="campaign-update-page">
        <section class="campaign-update-section">
            <div class="container">

                {{-- Alert Error --}}
                <x-alert-error />

                <form action="{{ route('campaign.update.store', $campaign->slug) }}" method="POST"
                    enctype="multipart/form-data" class="campaign-update-layout" id="updateForm" novalidate>
                    @csrf

                    {{-- ========================================================== --}}
                    {{-- LEFT FORM                                                  --}}
                    {{-- ========================================================== --}}
                    <div class="campaign-update-main">

                        {{-- HEADER --}}
                        <div class="campaign-update-heading">
                            <h1>Buat Update Kabar Terbaru</h1>
                            <p>
                                Bagikan kabar terbaru untuk campaign
                                <strong>{{ $campaign->judul }}</strong>
                            </p>
                            <a href="{{ route('campaign.show', $campaign->slug) }}" class="back-button">
                                <i class="bi bi-arrow-left"></i>
                                Kembali ke Campaign
                            </a>
                        </div>

                        {{-- ========================================================== --}}
                        {{-- FORM UPDATE                                              --}}
                        {{-- ========================================================== --}}
                        <section class="campaign-update-card">

                            <div class="campaign-update-card-head">
                                <h2>Detail Update</h2>
                                <p>Lengkapi informasi update yang ingin Anda bagikan kepada donatur.</p>
                            </div>

                            {{-- Judul Update --}}
                            <div class="campaign-field">
                                <label for="judul_update">Judul Update <span>*</span></label>
                                <div class="campaign-input-wrap">
                                    <input type="text" id="judul_update" name="judul_update"
                                        value="{{ old('judul_update') }}"
                                        placeholder="Masukkan judul update" required>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                                @error('judul_update')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Isi Update --}}
                            <div class="campaign-field">
                                <label for="isi_update">Isi Update <span>*</span></label>
                                <div class="campaign-input-wrap">
                                    <textarea id="isi_update" name="isi_update" rows="8"
                                        placeholder="Tulis kabar terbaru di sini..." required>{{ old('isi_update') }}</textarea>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                                @error('isi_update')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Gambar --}}
                            <div class="campaign-field">
                                <label>Gambar <small>(Opsional, maksimal 5)</small></label>
                                <div class="campaign-update-gallery-grid" id="galleryGrid">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <label class="campaign-upload-small" for="gambar_{{ $i }}">
                                            <input type="file" id="gambar_{{ $i }}" name="gambar[]"
                                                accept="image/png,image/jpeg,image/jpg" hidden>
                                            <img src="" alt="" class="campaign-upload-preview"
                                                data-preview="gambar_{{ $i }}" hidden>
                                            <span class="campaign-upload-placeholder">
                                                <i class="bi bi-image"></i>
                                            </span>
                                            <span class="campaign-upload-button small">
                                                <i class="bi bi-camera-fill"></i>
                                            </span>
                                        </label>
                                    @endfor
                                </div>
                                <small class="campaign-note">
                                    <i class="bi bi-info-circle"></i>
                                    Maksimal 5 gambar. Ukuran maksimal 2MB per gambar. Format: JPG, PNG, JPEG
                                </small>
                                @error('gambar.*')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </section>

                    </div>

                    {{-- ========================================================== --}}
                    {{-- RIGHT SIDEBAR                                             --}}
                    {{-- ========================================================== --}}
                    <aside class="campaign-update-sidebar">

                        {{-- Info Campaign --}}
                        <section class="campaign-side-card">
                            <div class="campaign-side-head">
                                <h2>Informasi Campaign</h2>
                            </div>
                            <div class="campaign-info-preview">
                                <div class="campaign-info-item">
                                    <span class="label">Judul</span>
                                    <span class="value">{{ $campaign->judul }}</span>
                                </div>
                                <div class="campaign-info-item">
                                    <span class="label">Penggalang</span>
                                    <span class="value">{{ $campaign->penggalangDana->nama_penggalang }}</span>
                                </div>
                                <div class="campaign-info-item">
                                    <span class="label">Target</span>
                                    <span class="value">Rp {{ number_format($campaign->target_donasi, 0, ',', '.') }}</span>
                                </div>
                                <div class="campaign-info-item">
                                    <span class="label">Tipe</span>
                                    <span class="value">
                                        @if($campaign->campaign_type == 'emergency')
                                            🔥 Darurat
                                        @elseif($campaign->campaign_type == 'sustainable')
                                            ♻️ Berkelanjutan
                                        @else
                                            📋 Regular
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </section>

                        {{-- Preview --}}
                        <section class="campaign-side-card">
                            <div class="campaign-side-head">
                                <h2>Preview Update</h2>
                                <p>Preview tampilan update yang akan dibagikan.</p>
                            </div>
                            <div class="update-preview" id="updatePreview">
                                <div class="update-preview-header">
                                    <strong id="previewJudul">Judul Update</strong>
                                    <span class="date">{{ now()->format('d M Y') }}</span>
                                </div>
                                <div class="update-preview-body" id="previewIsi">
                                    Isi update akan muncul di sini...
                                </div>
                                <div class="update-preview-gallery" id="previewGallery"></div>
                            </div>
                        </section>

                        {{-- Submit --}}
                        <button type="submit" class="campaign-submit-button" id="submitBtn">
                            <i class="bi bi-send-fill"></i>
                            <span>Kirim Update</span>
                        </button>

                    </aside>

                </form>

            </div>
        </section>
    </main>

    @include('components.footer')

    {{-- ========================================================== --}}
    {{-- SCRIPTS                                                    --}}
    {{-- ========================================================== --}}
    <script src="{{ asset('js/header.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ==========================================================
            // PREVIEW UPDATE (Live Preview)
            // ==========================================================
            const judulInput = document.getElementById('judul_update');
            const isiInput = document.getElementById('isi_update');
            const previewJudul = document.getElementById('previewJudul');
            const previewIsi = document.getElementById('previewIsi');

            judulInput.addEventListener('input', function() {
                previewJudul.textContent = this.value || 'Judul Update';
            });

            isiInput.addEventListener('input', function() {
                previewIsi.textContent = this.value || 'Isi update akan muncul di sini...';
            });

            // ==========================================================
            // GAMBAR PREVIEW
            // ==========================================================
            const galleryGrid = document.getElementById('galleryGrid');
            const previewGallery = document.getElementById('previewGallery');

            galleryGrid.querySelectorAll('input[type="file"]').forEach((input, index) => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    const previewImg = this.closest('.campaign-upload-small')
                        .querySelector('.campaign-upload-preview');
                    const placeholder = this.closest('.campaign-upload-small')
                        .querySelector('.campaign-upload-placeholder');

                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewImg.hidden = false;
                            placeholder.style.display = 'none';
                        }
                        reader.readAsDataURL(file);

                        // Update preview gallery
                        updatePreviewGallery();
                    } else {
                        previewImg.src = '';
                        previewImg.hidden = true;
                        placeholder.style.display = 'flex';
                        updatePreviewGallery();
                    }
                });
            });

            function updatePreviewGallery() {
                const previewImages = [];
                galleryGrid.querySelectorAll('input[type="file"]').forEach(input => {
                    if (input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImages.push(e.target.result);
                            renderPreviewGallery(previewImages);
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                });

                // Kalau ga ada gambar, kosongkan preview
                if (galleryGrid.querySelectorAll('input[type="file"]:not([value=""])').length === 0) {
                    previewGallery.innerHTML = '';
                    previewGallery.style.display = 'none';
                }
            }

            function renderPreviewGallery(images) {
                previewGallery.style.display = 'grid';
                previewGallery.innerHTML = images.map(img =>
                    `<div class="preview-gallery-item"><img src="${img}" alt="Preview"></div>`
                ).join('');
            }

            // ==========================================================
            // DRAG & DROP SUPPORT
            // ==========================================================
            const dropzones = document.querySelectorAll('.campaign-upload-small');

            dropzones.forEach(dropzone => {
                const input = dropzone.querySelector('input[type="file"]');

                dropzone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });

                dropzone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });

                dropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');

                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        input.files = files;
                        input.dispatchEvent(new Event('change'));
                    }
                });
            });

        });
    </script>

</body>

</html>