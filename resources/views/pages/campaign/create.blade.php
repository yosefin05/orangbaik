<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Campaign Baru - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/campaign-create.css') }}">

    <style>
        .campaign-slug-wrap {
            display: flex;
            align-items: center;
            border: 1px solid #ced4da;
            border-radius: 0.5rem;
            padding: 0 0.75rem;
            background: #fff;
            transition: border-color 0.2s;
        }
        .campaign-slug-wrap:focus-within {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .campaign-slug-wrap .slug-prefix {
            font-weight: 500;
            color: #6c757d;
            margin-right: 0.5rem;
            white-space: nowrap;
        }
        .campaign-slug-wrap .slug-input {
            border: none;
            outline: none;
            flex: 1;
            padding: 0.75rem 0;
            background: transparent;
            font-size: 1rem;
        }
        .campaign-slug-wrap .slug-input::placeholder {
            color: #adb5bd;
        }
        .campaign-slug-wrap .slug-input:focus {
            box-shadow: none;
        }
    </style>
</head>

<body>

    @include('components.header')
    <main class="campaign-create-page">
        <section class="campaign-create-section">
            <div class="container">
                <x-alert-error />
                <form action="{{ route('campaign.store') }}" method="POST" enctype="multipart/form-data"
                    class="campaign-create-layout" id="campaignCreateForm" novalidate>
                    @csrf
                    {{-- LEFT FORM --}}
                    <div class="campaign-create-main">
                        <div class="campaign-create-heading">
                            <h1>Buat Campaign Baru</h1>
                            <p>Lengkapi informasi campaign Anda dengan jelas agar siap dipublikasikan.</p>
                        </div>
                        {{-- THUMBNAIL --}}
                        <section class="campaign-create-card">
                            <div class="campaign-create-card-head">
                                <h2>Thumbnail / Poster Campaign</h2>
                                <p>Unggah thumbnail atau poster yang menarik dan sesuai dengan tujuan campaign.</p>
                            </div>
                            <label class="campaign-upload-large" for="thumbnail">
                                <input type="file" id="thumbnail" name="thumbnail"
                                    accept="image/png,image/jpeg,image/jpg" hidden required>
                                <img src="" alt="" class="campaign-upload-preview" data-preview="thumbnail" hidden>
                                <span class="campaign-upload-placeholder">
                                    <i class="bi bi-image"></i>
                                </span>
                                <span class="campaign-upload-button">
                                    <i class="bi bi-camera-fill"></i>
                                </span>
                            </label>
                            <small class="campaign-note">Catatan: Ukuran thumbnail/poster disarankan 734 × 394
                                px.</small>
                        </section>

                        {{-- INFO CAMPAIGN --}}
                        <section class="campaign-create-card">
                            <div class="campaign-create-card-head">
                                <h2>Informasi Campaign</h2>
                                <p>Lengkapi informasi campaign secara jelas agar mudah dipahami dan dipercaya calon
                                    donatur.</p>
                            </div>
                            <div class="campaign-field">
                                <label for="judul_campaign">Judul Campaign <span>*</span></label>
                                <div class="campaign-input-wrap">
                                    <input type="text" id="judul_campaign" name="judul_campaign"
                                        value="{{ old('judul_campaign') }}" placeholder="Masukkan judul campaign Anda"
                                        required>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                            </div>

                            {{-- Custom Slug --}}
                            <div class="campaign-field">
                                <label for="custom_slug">Link Campaign <small>(Opsional)</small></label>
                                <div class="campaign-slug-wrap">
                                    <span class="slug-prefix">orangbaik.id/</span>
                                    <input type="text" name="custom_slug" id="custom_slug"
                                           class="slug-input"
                                           placeholder="contoh: bantu-korban-banjir"
                                           value="{{ old('custom_slug') }}">
                                </div>
                                <small class="campaign-note">Gunakan nama singkat agar mudah dibagikan.</small>
                            </div>

                            <div class="campaign-field">
                                <label for="deskripsi_campaign">Deskripsi Campaign <span>*</span></label>
                                <div class="campaign-input-wrap">
                                    <textarea id="deskripsi_campaign" name="deskripsi_campaign" rows="7"
                                        placeholder="Masukkan deskripsi campaign Anda" required>{{ old('deskripsi_campaign') }}</textarea>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                            </div>
                            <div class="campaign-field">
                                <label>Gambar Pendukung <small>(Opsional)</small></label>
                                <div class="campaign-support-grid">
                                    <label class="campaign-upload-small" for="gambar_pendukung_1">
                                        <input type="file" id="gambar_pendukung_1" name="gambar_pendukung[]"
                                            accept="image/png,image/jpeg,image/jpg" hidden>
                                        <img src="" alt="" class="campaign-upload-preview"
                                            data-preview="gambar_pendukung_1" hidden>
                                        <span class="campaign-upload-placeholder">
                                            <i class="bi bi-image"></i>
                                        </span>
                                        <span class="campaign-upload-button small">
                                            <i class="bi bi-camera-fill"></i>
                                        </span>
                                    </label>
                                    <label class="campaign-upload-small" for="gambar_pendukung_2">
                                        <input type="file" id="gambar_pendukung_2" name="gambar_pendukung[]"
                                            accept="image/png,image/jpeg,image/jpg" hidden>
                                        <img src="" alt="" class="campaign-upload-preview"
                                            data-preview="gambar_pendukung_2" hidden>
                                        <span class="campaign-upload-placeholder">
                                            <i class="bi bi-image"></i>
                                        </span>
                                        <span class="campaign-upload-button small">
                                            <i class="bi bi-camera-fill"></i>
                                        </span>
                                    </label>
                                </div>
                                <small class="campaign-note">Catatan: Ukuran gambar pendukung disarankan 354 × 190
                                    px.</small>
                            </div>
                        </section>

                        {{-- DETAIL CAMPAIGN --}}
                        <section class="campaign-create-card">
                            <div class="campaign-create-card-head">
                                <h2>Detail Campaign</h2>
                                <p>Atur periode campaign dan target donasi sebagai acuan selama proses penggalangan dana
                                    berlangsung.</p>
                            </div>
                            <div class="campaign-two-grid">
                                <div class="campaign-field">
                                    <label for="tanggal_mulai">Tanggal Mulai <span>*</span></label>
                                    <div class="campaign-input-wrap">
                                        <input type="date" id="tanggal_mulai" name="tanggal_mulai"
                                            value="{{ old('tanggal_mulai', $today) }}" required>
                                        <i class="bi bi-calendar-event-fill"></i>
                                    </div>
                                </div>
                                <div class="campaign-field">
                                    <label for="tanggal_akhir">Tanggal Akhir <span>*</span></label>
                                    <div class="campaign-input-wrap">
                                        <input type="date" id="tanggal_akhir" name="tanggal_akhir"
                                            value="{{ old('tanggal_akhir') }}" required>
                                        <i class="bi bi-calendar-event-fill"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="campaign-field">
                                <label for="target_donasi">Target Donasi <span>*</span></label>
                                <div class="campaign-money-wrap">
                                    <span>Rp</span>
                                    <input type="text" id="target_donasi" name="target_donasi"
                                        value="{{ old('target_donasi') }}" placeholder="0" inputmode="numeric"
                                        data-money required>
                                </div>
                            </div>
                            <div class="campaign-field">
                                <label for="minimal_donasi">Minimal Donasi <small>(Opsional)</small></label>
                                <div class="campaign-money-wrap">
                                    <span>Rp</span>
                                    <input type="text" id="minimal_donasi" name="minimal_donasi"
                                        value="{{ old('minimal_donasi', '5000') }}" placeholder="Minimal Rp 5.000" inputmode="numeric"
                                        data-money>
                                </div>
                                <small class="campaign-note">Minimal donasi default Rp 5.000 jika tidak diisi.</small>
                            </div>
                        </section>

                        {{-- CATEGORY --}}
                        <section class="campaign-create-card">
                            <div class="campaign-create-card-head">
                                <h2>Pilih Kategori untuk Campaign Anda</h2>
                                <p>
                                    Pilih kategori sesuai kebutuhan campaign agar informasi
                                    tersampaikan dengan lebih jelas kepada donatur.
                                </p>
                            </div>
                            {{-- Kategori --}}
                            <div class="campaign-field">
                                <label for="kategori_id">
                                    Kategori Campaign <span>*</span>
                                </label>
                                <div class="campaign-select-wrap">
                                    <select id="kategori_id" name="kategori_id" required>
                                        <option value="">
                                            Pilih kategori campaign Anda
                                        </option>
                                        @foreach ($kategori as $item)
                                            <option value="{{ $item->id }}" {{ old('kategori_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                @error ('kategori_id')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </div>

                            {{-- TIPE CAMPAIGN --}}
                            <div class="campaign-field">
                                <label for="campaign_type">
                                    Tipe Campaign <span>*</span>
                                </label>
                                <div class="campaign-select-wrap">
                                    <select id="campaign_type" name="campaign_type" required>
                                        <option value="regular" {{ old('campaign_type') == 'regular' ? 'selected' : '' }}>
                                            Campaign Reguler
                                        </option>
                                        <option value="emergency" {{ old('campaign_type') == 'emergency' ? 'selected' : '' }}>
                                            🔥 Donasi Darurat (Perlu Persetujuan Admin)
                                        </option>
                                        <option value="sustainable" {{ old('campaign_type') == 'sustainable' ? 'selected' : '' }}>
                                            ♻️ Donasi Berkelanjutan (Perlu Persetujuan Admin)
                                        </option>
                                    </select>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                @error ('campaign_type')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror
                                <small class="campaign-note" id="campaignTypeNote">
                                    <strong>Catatan:</strong> Campaign darurat dan berkelanjutan memerlukan
                                    persetujuan admin sebelum ditampilkan di halaman utama.
                                    Campaign reguler langsung tampil.
                                </small>
                            </div>

                            {{-- Filter --}}
                            <div class="campaign-field">
                                <label>
                                    Filter Campaign <span>*</span>
                                </label>
                                <div class="campaign-filter-grid">
                                    @foreach ($filter as $item)
                                        <label class="campaign-filter-item">
                                            <input type="checkbox" name="filter[]" value="{{ $item->id }}"
                                                @checked(in_array($item->id, old('filter', [])))>
                                            <span>
                                                {{ $item->nama_filter }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <small class="campaign-note" id="filterNote">
                                    Catatan: maksimal 4 filter.
                                </small>
                                @error ('filter')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </div>
                        </section>
                    </div>
                    {{-- RIGHT SIDEBAR --}}
                    <aside class="campaign-create-sidebar">
                        {{-- PACKAGE SECTION --}}
                        <section class="campaign-side-card">
                            <div class="campaign-side-head">
                                <h2>Package Donasi <small>(Opsional)</small></h2>
                                <p>Tambahkan package donasi agar donatur lebih mudah memilih. Jika tidak diisi, akan muncul pilihan nominal default.</p>
                            </div>
                            <div class="campaign-package-list" id="packageList">
                                <div class="campaign-package-item" data-package-item>
                                    <div class="campaign-package-title">
                                        <strong>Package 1</strong>
                                        <button type="button" data-remove-package hidden>
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                    <label class="package-image-upload">
                                        <input type="file" name="packages[0][image]"
                                            accept="image/png,image/jpeg,image/jpg" hidden>
                                        <span>
                                            <i class="bi bi-image"></i>
                                        </span>
                                        <small>Tambahkan Gambar</small>
                                    </label>
                                    <div class="campaign-field compact">
                                        <label>Judul Package <small>(Opsional)</small></label>
                                        <div class="campaign-input-wrap">
                                            <input type="text" name="packages[0][title]"
                                                placeholder="Masukkan judul package">
                                            <i class="bi bi-pencil-fill"></i>
                                        </div>
                                    </div>
                                    <div class="campaign-field compact">
                                        <label>Deskripsi Package <small>(Opsional)</small></label>
                                        <div class="campaign-input-wrap">
                                            <textarea name="packages[0][description]" rows="3"
                                                placeholder="Masukkan deskripsi package"></textarea>
                                            <i class="bi bi-pencil-fill"></i>
                                        </div>
                                    </div>
                                    <div class="campaign-field compact">
                                        <label>Nominal Package <small>(Opsional)</small></label>
                                        <div class="campaign-money-wrap">
                                            <span>Rp</span>
                                            <input type="text" name="packages[0][nominal]" placeholder="0"
                                                inputmode="numeric" data-money>
                                        </div>
                                        <div class="package-extra-feature"></div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="campaign-add-package" id="addPackageButton">
                                <i class="bi bi-plus-lg"></i>
                                <span>Tambahkan Package Baru</span>
                            </button>
                            <small class="campaign-note">Jika tidak menambahkan package, akan muncul pilihan nominal default (Rp 10.000, Rp 25.000, Rp 50.000, Rp 100.000).</small>
                        </section>

                        {{-- FITUR LAINNYA --}}
                        <section class="campaign-side-card">
                            <div class="campaign-side-head">
                                <h2>Tambahkan Fitur Lainnya <span>(Opsional)</span></h2>
                            </div>

                            <!-- Jumlah Package -->
                            <div class="feature-row">
                                <label class="feature-check">
                                    <input type="checkbox" id="toggleQuantity" name="enable_quantity">
                                    <span></span>
                                </label>
                                <div class="feature-counter">
                                    <button type="button" class="minus">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <span class="qty">1</span>
                                    <button type="button" class="plus">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Nama Pekurban -->
                            <div class="feature-input-card">
                                <label class="feature-check">
                                    <input type="checkbox" id="toggleDonatur" name="enable_donatur_name">
                                    <span></span>
                                </label>
                                <div class="feature-input">
                                    <input type="text" placeholder="Nama Pekurban">
                                    <small>Masukkan Atas Nama Pekurban</small>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                            </div>

                            <!-- Nominal -->
                            <div class="feature-input-card">
                                <label class="feature-check">
                                    <input type="checkbox" id="toggleNominal" name="enable_custom_nominal">
                                    <span></span>
                                </label>
                                <div class="feature-money">
                                    <label>Nominal Lainnya</label>
                                    <div class="money-box">
                                        <span>Rp</span>
                                        <input type="text" placeholder="0" inputmode="numeric" data-money>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- PREVIEW --}}
                        <section class="campaign-side-card">
                            <div class="campaign-side-head">
                                <h2>Pratinjau Tata Letak Package</h2>
                                <p>Preview sederhana tampilan pilihan donasi.</p>
                            </div>
                            <div class="campaign-layout-preview">
                                <div id="previewPackageList"></div>
                            </div>
                        </section>

                        <button type="submit" class="campaign-submit-button" id="publishBtn">
                            <i class="bi bi-send-fill"></i>
                            <span>Publikasikan Campaign</span>
                        </button>
                    </aside>

                </form>
            </div>
        </section>
    </main>

    @include('components.footer')
    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/campaign-create.js') }}"></script>
    <script src="{{ asset('js/campaign-type.js') }}"></script>

</body>

</html>