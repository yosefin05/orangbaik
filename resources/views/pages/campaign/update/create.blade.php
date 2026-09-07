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
                    {{-- LEFT FORM --}}
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
                        {{-- FORM UPDATE --}}
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
                                        value="{{ old('judul_update') }}" placeholder="Masukkan judul update" required>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                                @error('judul_update')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Isi Update --}}
                            <div class="campaign-field">
                                <label for="isi_update">Isi Update <span>*</span></label>
                                <x-rich-text-editor name="isi_update" id="isi_update" :value="old('isi_update')" />
                                @error('isi_update')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </section>

                    </div>

                    {{-- ========================================================== --}}
                    {{-- RIGHT SIDEBAR --}}
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
                                    <span class="value">Rp
                                        {{ number_format($campaign->target_donasi, 0, ',', '.') }}</span>
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
    {{-- SCRIPTS --}}
    {{-- ========================================================== --}}
    <script src="{{ asset('js/header.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ==========================================================
            // PREVIEW UPDATE (Live Preview)
            // ==========================================================
            const previewJudul = document.getElementById('previewJudul');
            const previewIsi = document.getElementById('previewIsi');
            const judulInput = document.getElementById('judul_update');

            // Cari editor dengan benar - gunakan ID yang diberikan ke komponen
            // Komponen rich-text-editor menggunakan ID "isi_update" untuk contenteditable div
            const editor = document.getElementById('isi_update');

            // Fungsi untuk mendapatkan konten dari editor
            function getEditorContent() {
                if (editor) {
                    // Jika editor adalah contenteditable div
                    if (editor.contentEditable === 'true') {
                        return editor.innerHTML;
                    }
                    // Jika editor adalah textarea
                    if (editor.tagName === 'TEXTAREA') {
                        return editor.value;
                    }
                }
                
                // Fallback: cari contenteditable lain
                const contentEditable = document.querySelector('[contenteditable="true"]');
                if (contentEditable) {
                    return contentEditable.innerHTML;
                }

                // Fallback: cari textarea dengan name isi_update
                const textarea = document.querySelector('textarea[name="isi_update"]');
                if (textarea) {
                    return textarea.value;
                }

                return '';
            }

            // Fungsi update preview
            function updatePreview() {
                // Preview judul
                const judul = judulInput.value.trim();
                previewJudul.textContent = judul || 'Judul Update';

                // Preview isi update
                const content = getEditorContent();
                const textContent = content.replace(/<[^>]*>/g, '').trim();

                if (content && textContent) {
                    previewIsi.innerHTML = content;
                } else {
                    previewIsi.innerHTML = 'Isi update akan muncul di sini...';
                }
            }

            // Event listener untuk judul
            judulInput.addEventListener('input', updatePreview);
            judulInput.addEventListener('keyup', updatePreview);

            // Event listener untuk editor (contenteditable)
            if (editor && editor.contentEditable === 'true') {
                editor.addEventListener('input', updatePreview);
                editor.addEventListener('keyup', updatePreview);
                editor.addEventListener('paste', function () {
                    setTimeout(updatePreview, 50);
                });
                
                // MutationObserver untuk mendeteksi perubahan yang tidak terdeteksi oleh event biasa
                const observer = new MutationObserver(function() {
                    updatePreview();
                });
                observer.observe(editor, {
                    childList: true,
                    subtree: true,
                    characterData: true,
                    attributes: true
                });
            }

            // Event listener untuk textarea (jika editor adalah textarea)
            if (editor && editor.tagName === 'TEXTAREA') {
                editor.addEventListener('input', updatePreview);
                editor.addEventListener('keyup', updatePreview);
                editor.addEventListener('change', updatePreview);
            }

            // Event listener untuk tombol toolbar (jika ada)
            const toolbarButtons = document.querySelectorAll('[data-command], [data-insert-image]');
            toolbarButtons.forEach(button => {
                button.addEventListener('click', function() {
                    setTimeout(updatePreview, 100);
                });
            });

            // Jalankan preview saat halaman pertama kali dibuka
            // Gunakan multiple timeout untuk memastikan editor sudah siap
            setTimeout(updatePreview, 100);
            setTimeout(updatePreview, 300);
            setTimeout(updatePreview, 500);

            // ==========================================================
            // VALIDASI FORM
            // ==========================================================
            const form = document.getElementById('updateForm');
            
            form.addEventListener('submit', function(e) {
                const judul = judulInput.value.trim();
                const content = getEditorContent();
                const textContent = content.replace(/<[^>]*>/g, '').trim();

                let errors = [];

                if (!judul) {
                    errors.push('Judul update harus diisi');
                }

                if (!textContent) {
                    errors.push('Isi update harus diisi');
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    alert('Mohon lengkapi data berikut:\n- ' + errors.join('\n- '));
                }
            });

            console.log('Preview update siap!');
            console.log('Editor ditemukan:', !!editor);

        });
    </script>

</body>

</html>