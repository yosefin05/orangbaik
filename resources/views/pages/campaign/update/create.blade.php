<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Update - {{ $campaign->judul }} - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/campaign-update.css') }}?v={{ time() }}">
</head>
<body class="bg-light">

    @include('components.header')

    <main class="campaign-update-page">
        <div class="container">

            <x-alert-error />

            {{-- Top Compact Navigation & Title --}}
            <div class="update-top-bar">
                <a href="{{ route('campaign.show', $campaign->slug) }}" class="back-link">
                    <i class="bi bi-arrow-left"></i> Kembali ke Campaign
                </a>
                <div class="header-titles">
                    <h1>Tulis Update Kabar Terbaru</h1>
                    <p>Bagikan cerita atau progres terbaru untuk donatur campaign <strong>{{ $campaign->judul }}</strong></p>
                </div>
            </div>

            <form action="{{ route('campaign.update.store', $campaign->slug) }}" method="POST" enctype="multipart/form-data" class="update-grid-layout" id="updateForm" novalidate>
                @csrf

                {{-- LEFT COLUMN: FORM EDITING --}}
                <div class="update-main-content">
                    <div class="custom-card editor-card">
                        <div class="card-header-styled">
                            <div class="icon-badge"><i class="bi bi-pencil-square"></i></div>
                            <div>
                                <h2>Form Detail Update</h2>
                                <p>Pastikan informasi yang ditulis jelas dan transparan</p>
                            </div>
                        </div>

                        <div class="card-body-styled">
                            {{-- Input Judul --}}
                            <div class="form-group-custom">
                                <label for="judul_update">Judul Update <span class="req">*</span></label>
                                <div class="input-icon-wrapper">
                                    <input type="text" id="judul_update" name="judul_update" value="{{ old('judul_update') }}" placeholder="Contoh: Penyaluran Dana Tahap 1 Telah Selesai" required>
                                </div>
                                @error('judul_update')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Input Rich Text Editor --}}
                            <div class="form-group-custom">
                                <label for="isi_update">Isi Update / Berita <span class="req">*</span></label>
                                <x-rich-text-editor name="isi_update" id="isi_update" :value="old('isi_update')" />
                                @error('isi_update')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: REALTIME FEED MOCKUP --}}
                <aside class="update-sidebar-content">
                    
                    {{-- Real-time Feed Live Preview Box --}}
                    <div class="custom-card preview-card">
                        <div class="card-header-styled compact">
                            <div class="live-badge"><span class="dot"></span> Live Preview</div>
                            <small class="text-muted">Tampilan di mata donatur</small>
                        </div>
                        
                        <div class="feed-preview-wrapper">
                            <div class="feed-header">
                                <div class="author-avatar">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                                <div class="author-info">
                                    <span class="author-name">{{ $campaign->penggalangDana->nama_penggalang ?? 'Penggalang Dana' }}</span>
                                    <span class="post-time">Baru saja · Kabar Terbaru</span>
                                </div>
                            </div>
                            <div class="feed-content">
                                <h3 id="previewJudul">Judul Update Anda Akan Muncul Di Sini</h3>
                                <div id="previewIsi" class="feed-body-text">
                                    Tuliskan detail berita di form sebelah kiri untuk melihat simulasi tampilan postingan update...
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Campaign Summary Card --}}
                    <div class="custom-card info-card">
                        <div class="info-row">
                            <span class="info-label">Target Donasi</span>
                            <span class="info-value">Rp {{ number_format($campaign->target_donasi, 0, ',', '.') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Kategori Campaign</span>
                            <span class="info-badge">{{ strtoupper($campaign->campaign_type ?? 'REGULAR') }}</span>
                        </div>
                    </div>

                    {{-- Submit Action --}}
                    <button type="submit" class="btn-primary-action" id="submitBtn">
                        <i class="bi bi-send-fill"></i> Kirim Update Kabar
                    </button>
                </aside>
            </form>

        </div>
    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const previewJudul = document.getElementById('previewJudul');
            const previewIsi = document.getElementById('previewIsi');
            const judulInput = document.getElementById('judul_update');

            function updatePreview() {
                const judul = judulInput.value.trim();
                previewJudul.textContent = judul || 'Judul Update Anda Akan Muncul Di Sini';

                let content = '';
                const editor = document.getElementById('isi_update');
                if (editor) {
                    content = editor.contentEditable === 'true' ? editor.innerHTML : editor.value;
                } else {
                    const fallback = document.querySelector('textarea[name="isi_update"]') || document.querySelector('[contenteditable="true"]');
                    if(fallback) content = fallback.innerHTML || fallback.value;
                }

                const textOnly = content.replace(/<[^>]*>/g, '').trim();
                if (content && textOnly) {
                    previewIsi.innerHTML = content;
                } else {
                    previewIsi.innerHTML = '<span style="color: #a0aec0;">Tuliskan detail berita di form sebelah kiri untuk melihat simulasi tampilan postingan update...</span>';
                }
            }

            judulInput.addEventListener('input', updatePreview);
            document.addEventListener('keyup', updatePreview);
            document.addEventListener('click', function() { setTimeout(updatePreview, 100); });
            setTimeout(updatePreview, 200);
        });
    </script>
</body>
</html>