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
            const judulInput = document.getElementById('judul_update');
            const isiInput = document.getElementById('isi_update');
            const previewJudul = document.getElementById('previewJudul');
            const previewIsi = document.getElementById('previewIsi');

            judulInput.addEventListener('input', function () {
                previewJudul.textContent = this.value || 'Judul Update';
            });

            isiInput.addEventListener('input', function () {
                previewIsi.textContent = this.value || 'Isi update akan muncul di sini...';
            });

        });
    </script>

</body>

</html>