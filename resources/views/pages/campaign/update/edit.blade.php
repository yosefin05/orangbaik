<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Update - {{ $campaign->judul }} - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/campaign-update.css') }}?v={{ time() }}">
</head>

<body>

    @include('components.header')

    <main class="campaign-update-page">
        <div class="container">

            {{-- Alert Error --}}
            <x-alert-error />

            {{-- Top Navigation & Title --}}
            <div class="update-top-bar">
                <a href="{{ route('campaign.show', $campaign->slug) }}" class="back-link">
                    <i class="bi bi-arrow-left"></i> Kembali ke Campaign
                </a>
                <div class="header-titles">
                    <h1>Edit Update Kabar Terbaru</h1>
                    <p>Perbarui cerita atau informasi kabar terbaru untuk donatur <strong>{{ $campaign->judul }}</strong></p>
                </div>
            </div>

            <form action="{{ route('campaign.update.update', [$campaign->slug, $update->id]) }}" method="POST"
                enctype="multipart/form-data" class="update-grid-layout" id="updateForm" novalidate>
                @csrf
                @method('PUT')

                {{-- LEFT COLUMN: FORM EDITING --}}
                <div class="update-main-content">
                    <div class="custom-card editor-card">
                        <div class="card-header-styled">
                            <div class="icon-badge"><i class="bi bi-pencil-square"></i></div>
                            <div>
                                <h2>Detail Update</h2>
                                <p>Perbarui informasi update yang ingin Anda bagikan kepada donatur.</p>
                            </div>
                        </div>

                        <div class="card-body-styled">
                            {{-- Judul Update --}}
                            <div class="form-group-custom">
                                <label for="judul_update">Judul Update <span class="req">*</span></label>
                                <div class="input-icon-wrapper">
                                    <input type="text" id="judul_update" name="judul_update"
                                        value="{{ old('judul_update', $update->judul_update) }}"
                                        placeholder="Masukkan judul update" required>
                                </div>
                                @error('judul_update')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Isi Update --}}
                            <div class="form-group-custom">
                                <label for="isi_update">Isi Update / Berita <span class="req">*</span></label>
                                <x-rich-text-editor name="isi_update" id="isi_update"
                                    :value="old('isi_update', $update->isi_update)" />
                                @error('isi_update')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: SIDEBAR & LIVE PREVIEW --}}
                <aside class="update-sidebar-content">

                    {{-- Live Feed Preview Card --}}
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
                                    <span class="post-time">{{ optional($update->created_at)->translatedFormat('d M Y') ?? 'Baru saja' }}</span>
                                </div>
                            </div>
                            <div class="feed-content">
                                <h3 id="previewJudul">{{ $update->judul_update ?? 'Judul Update' }}</h3>
                                <div id="previewIsi" class="feed-body-text">
                                    {!! $update->isi_update ?? 'Isi update akan muncul di sini...' !!}
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
                        <i class="bi bi-check-circle-fill"></i> Perbarui Update
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

            function getEditorContent() {
                const editor = document.getElementById('isi_update');
                if (editor) {
                    if (editor.contentEditable === 'true') return editor.innerHTML;
                    if (editor.tagName === 'TEXTAREA') return editor.value;
                }
                const fallback = document.querySelector('textarea[name="isi_update"]') || document.querySelector('[contenteditable="true"]');
                return fallback ? (fallback.innerHTML || fallback.value) : '';
            }

            function updatePreview() {
                const judul = judulInput.value.trim();
                previewJudul.textContent = judul || 'Judul Update';

                const content = getEditorContent();
                const textOnly = content.replace(/<[^>]*>/g, '').trim();

                if (content && textOnly) {
                    previewIsi.innerHTML = content;
                } else {
                    previewIsi.innerHTML = '<span style="color: var(--muted-light);">Isi update akan muncul di sini...</span>';
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