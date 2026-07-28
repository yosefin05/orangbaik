<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Update - {{ $campaign->judul }} - OrangBaik.id</title>

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

                <form action="{{ route('campaign.update.update', [$campaign->slug, $update->id]) }}" method="POST"
                    enctype="multipart/form-data" class="campaign-update-layout" id="updateForm" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- ========================================================== --}}
                    {{-- LEFT FORM                                                  --}}
                    {{-- ========================================================== --}}
                    <div class="campaign-update-main">

                        {{-- HEADER --}}
                        <div class="campaign-update-heading">
                            <h1>Edit Update Kabar Terbaru</h1>
                            <p>
                                Perbarui kabar terbaru untuk campaign
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
                                <p>Perbarui informasi update yang ingin Anda bagikan kepada donatur.</p>
                            </div>

                            {{-- Judul Update --}}
                            <div class="campaign-field">
                                <label for="judul_update">Judul Update <span>*</span></label>
                                <div class="campaign-input-wrap">
                                    <input type="text" id="judul_update" name="judul_update"
                                        value="{{ old('judul_update', $update->judul_update) }}"
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
                                        placeholder="Tulis kabar terbaru di sini..." required>{{ old('isi_update', $update->isi_update) }}</textarea>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                                @error('isi_update')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Gambar Saat Ini --}}
                            @if($update->campaign_update_gambar->count() > 0)
                                <div class="campaign-field">
                                    <label>Gambar Saat Ini</label>
                                    <div class="campaign-current-images" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: var(--space-3); margin-top: var(--space-2);">
                                        @foreach($update->campaign_update_gambar as $gambar)
                                            <div class="campaign-current-image-item" style="position: relative; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--border-soft); aspect-ratio: 1/1; background: var(--bg-soft);">
                                                <img src="{{ asset('storage/' . $gambar->gambar_update) }}"
                                                     alt="Gambar update"
                                                     style="width: 100%; height: 100%; object-fit: cover;">
                                                <label style="position: absolute; bottom: 6px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.65); color: #fff; padding: 3px 10px; border-radius: var(--radius-full); font-size: var(--fs-xxs); cursor: pointer; display: flex; align-items: center; gap: 4px; white-space: nowrap;">
                                                    <input type="checkbox"
                                                           name="hapus_gambar[]"
                                                           value="{{ $gambar->id }}"
                                                           style="accent-color: var(--danger); margin: 0;">
                                                    Hapus
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="campaign-note">
                                        <i class="bi bi-info-circle"></i>
                                        Centang gambar yang ingin dihapus.
                                    </small>
                                </div>
                            @endif

                            {{-- Tambah Gambar Baru --}}
                            <div class="campaign-field">
                                <label>Tambah Gambar Baru <small>(Opsional, maksimal 5)</small></label>
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
                                    Maksimal 5 gambar tambahan. Ukuran maksimal 2MB per gambar. Format: JPG, PNG, JPEG
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
                                    <strong id="previewJudul">{{ $update->judul_update }}</strong>
                                    <span class="date">{{ $update->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="update-preview-body" id="previewIsi">
                                    {{ Str::limit($update->isi_update, 150) }}
                                </div>
                                <div class="update-preview-gallery" id="previewGallery">
                                    @foreach($update->campaign_update_gambar as $gambar)
                                        <div class="preview-gallery-item">
                                            <img src="{{ asset('storage/' . $gambar->gambar_update) }}" alt="Gambar update">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </section>

                        {{-- Submit --}}
                        <button type="submit" class="campaign-submit-button" id="submitBtn">
                            <i class="bi bi-pencil-fill"></i>
                            <span>Perbarui Update</span>
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
            // GAMBAR PREVIEW (untuk gambar baru)
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
                // Hapus preview gambar lama (yang dari existing)
                // Tapi kita mau tambahkan di bawah existing
                const existingImages = document.querySelectorAll('.preview-gallery-item');
                const existingCount = existingImages.length;

                // Ambil file baru
                const newFiles = [];
                galleryGrid.querySelectorAll('input[type="file"]').forEach(input => {
                    if (input.files[0]) {
                        newFiles.push(input.files[0]);
                    }
                });

                // Render preview: existing + new
                if (newFiles.length > 0) {
                    // Hapus preview existing yang kita render ulang
                    // Tapi kita biarkan existing tetap, tambahkan new di bawah
                    newFiles.forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Cek apakah sudah ada preview untuk file ini
                            const previewItems = previewGallery.querySelectorAll('.preview-gallery-item');
                            // Jika total preview > existingCount, berarti sudah ada
                            if (previewItems.length > existingCount + index) {
                                // Update gambar di posisi yang sesuai
                                const img = previewItems[existingCount + index]?.querySelector('img');
                                if (img) {
                                    img.src = e.target.result;
                                }
                            } else {
                                // Tambah baru
                                const div = document.createElement('div');
                                div.className = 'preview-gallery-item';
                                div.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                                previewGallery.appendChild(div);
                            }
                        }
                        reader.readAsDataURL(file);
                    });
                }

                // Jika tidak ada file baru, tapi ada existing, tampilkan existing
                if (newFiles.length === 0 && previewGallery.querySelectorAll('.preview-gallery-item').length === 0) {
                    // Tampilkan existing dari server
                    // Existing sudah dirender dari server, jadi tidak perlu melakukan apa-apa
                }
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

            // ==========================================================
            // HAPUS GAMBAR EXISTING (toggle preview)
            // ==========================================================
            document.querySelectorAll('input[name="hapus_gambar[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const parent = this.closest('.campaign-current-image-item');
                    if (this.checked) {
                        parent.style.opacity = '0.4';
                        parent.style.border = '2px solid var(--danger)';
                    } else {
                        parent.style.opacity = '1';
                        parent.style.border = '1px solid var(--border-soft)';
                    }

                    // Update preview gallery
                    const imageSrc = parent.querySelector('img')?.src;
                    if (imageSrc) {
                        const previewItems = previewGallery.querySelectorAll('.preview-gallery-item img');
                        previewItems.forEach(img => {
                            if (img.src === imageSrc) {
                                if (this.checked) {
                                    img.closest('.preview-gallery-item').style.opacity = '0.3';
                                    img.closest('.preview-gallery-item').style.textDecoration = 'line-through';
                                } else {
                                    img.closest('.preview-gallery-item').style.opacity = '1';
                                    img.closest('.preview-gallery-item').style.textDecoration = 'none';
                                }
                            }
                        });
                    }
                });
            });

        });
    </script>

</body>

</html>