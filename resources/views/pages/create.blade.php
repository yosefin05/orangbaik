<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Campaign Baru - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/campaign-create.css') }}">
</head>

<body>

@include('components.header')

<main class="campaign-create-page">

    <section class="campaign-create-section">
        <div class="container">

            <a href="javascript:history.back()" class="campaign-create-back">
                <i class="bi bi-chevron-left"></i>
                <span>Kembali</span>
            </a>

            <form
                action="{{ url('/campaign/store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="campaign-create-layout"
                id="campaignCreateForm">
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
                            <input type="file" id="thumbnail" name="thumbnail" accept="image/png,image/jpeg,image/jpg" hidden>

                            <img src="" alt="" class="campaign-upload-preview" data-preview="thumbnail" hidden>

                            <span class="campaign-upload-placeholder">
                                <i class="bi bi-image"></i>
                            </span>

                            <span class="campaign-upload-button">
                                <i class="bi bi-camera-fill"></i>
                            </span>
                        </label>

                        <small class="campaign-note">Catatan: Ukuran thumbnail/poster disarankan 734 × 394 px.</small>
                    </section>

                    {{-- INFO CAMPAIGN --}}
                    <section class="campaign-create-card">
                        <div class="campaign-create-card-head">
                            <h2>Informasi Campaign</h2>
                            <p>Lengkapi informasi campaign secara jelas agar mudah dipahami dan dipercaya calon donatur.</p>
                        </div>

                        <div class="campaign-field">
                            <label for="judul_campaign">Judul Campaign <span>*</span></label>
                            <div class="campaign-input-wrap">
                                <input type="text" id="judul_campaign" name="judul_campaign" placeholder="Masukkan judul campaign Anda" required>
                                <i class="bi bi-pencil-fill"></i>
                            </div>
                        </div>

                        <div class="campaign-field">
                            <label for="deskripsi_campaign">Deskripsi Campaign <span>*</span></label>
                            <div class="campaign-input-wrap">
                                <textarea id="deskripsi_campaign" name="deskripsi_campaign" rows="7" placeholder="Masukkan deskripsi campaign Anda" required></textarea>
                                <i class="bi bi-pencil-fill"></i>
                            </div>
                        </div>

                        <div class="campaign-field">
                            <label>Gambar Pendukung <small>(Opsional)</small></label>

                            <div class="campaign-support-grid">
                                <label class="campaign-upload-small" for="gambar_pendukung_1">
                                    <input type="file" id="gambar_pendukung_1" name="gambar_pendukung[]" accept="image/png,image/jpeg,image/jpg" hidden>

                                    <img src="" alt="" class="campaign-upload-preview" data-preview="gambar_pendukung_1" hidden>

                                    <span class="campaign-upload-placeholder">
                                        <i class="bi bi-image"></i>
                                    </span>

                                    <span class="campaign-upload-button small">
                                        <i class="bi bi-camera-fill"></i>
                                    </span>
                                </label>

                                <label class="campaign-upload-small" for="gambar_pendukung_2">
                                    <input type="file" id="gambar_pendukung_2" name="gambar_pendukung[]" accept="image/png,image/jpeg,image/jpg" hidden>

                                    <img src="" alt="" class="campaign-upload-preview" data-preview="gambar_pendukung_2" hidden>

                                    <span class="campaign-upload-placeholder">
                                        <i class="bi bi-image"></i>
                                    </span>

                                    <span class="campaign-upload-button small">
                                        <i class="bi bi-camera-fill"></i>
                                    </span>
                                </label>
                            </div>

                            <small class="campaign-note">Catatan: Ukuran gambar pendukung disarankan 354 × 190 px.</small>
                        </div>
                    </section>

                    {{-- DETAIL CAMPAIGN --}}
                    <section class="campaign-create-card">
                        <div class="campaign-create-card-head">
                            <h2>Detail Campaign</h2>
                            <p>Atur periode campaign dan target donasi sebagai acuan selama proses penggalangan dana berlangsung.</p>
                        </div>

                        <div class="campaign-two-grid">
                            <div class="campaign-field">
                                <label for="tanggal_mulai">Tanggal Mulai <span>*</span></label>
                                <div class="campaign-input-wrap">
                                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" required>
                                    <i class="bi bi-calendar-event-fill"></i>
                                </div>
                            </div>

                            <div class="campaign-field">
                                <label for="tanggal_akhir">Tanggal Akhir <span>*</span></label>
                                <div class="campaign-input-wrap">
                                    <input type="date" id="tanggal_akhir" name="tanggal_akhir" required>
                                    <i class="bi bi-calendar-event-fill"></i>
                                </div>
                            </div>
                        </div>

                        <div class="campaign-field">
                            <label for="target_donasi">Target Donasi <span>*</span></label>
                            <div class="campaign-money-wrap">
                                <span>Rp</span>
                                <input type="text" id="target_donasi" name="target_donasi" placeholder="0" inputmode="numeric" data-money required>
                            </div>
                        </div>

                        <div class="campaign-field">
                            <label for="minimal_donasi">Minimal Donasi <span>*</span></label>
                            <div class="campaign-money-wrap">
                                <span>Rp</span>
                                <input type="text" id="minimal_donasi" name="minimal_donasi" placeholder="0" inputmode="numeric" data-money required>
                            </div>
                        </div>
                    </section>

                    {{-- CATEGORY --}}
                    <section class="campaign-create-card">
                        <div class="campaign-create-card-head">
                            <h2>Pilih Kategori untuk Campaign Anda</h2>
                            <p>Pilih kategori sesuai kebutuhan campaign agar informasi tersampaikan dengan lebih jelas kepada donatur.</p>
                        </div>

                        <div class="campaign-field">
                            <label for="kategori_campaign">Kategori Campaign <span>*</span></label>
                            <div class="campaign-select-wrap">
                                <select id="kategori_campaign" name="kategori_campaign" required>
                                    <option value="">Pilih kategori campaign Anda</option>
                                    <option value="pendidikan">Bantuan Pendidikan</option>
                                    <option value="bencana">Bencana Alam</option>
                                    <option value="difabel">Difabel</option>
                                    <option value="panti">Panti Asuhan</option>
                                    <option value="umkm">Pemberdayaan UMKM</option>
                                    <option value="lingkungan">Lingkungan</option>
                                    <option value="masjid">Masjid Berdaya</option>
                                    <option value="mualaf">Mualaf</option>
                                    <option value="kesehatan">Bantuan Kesehatan</option>
                                    <option value="negara">Negara Terdampak</option>
                                </select>
                                <i class="bi bi-chevron-down"></i>
                            </div>
                        </div>

                        <div class="campaign-field">
                            <label>Filter Campaign <span>*</span></label>

                            <div class="campaign-filter-grid">
                                @foreach([
                                    'Bantuan Pendidikan',
                                    'Bencana Alam',
                                    'Difabel',
                                    'Panti Asuhan',
                                    'Pemberdayaan UMKM',
                                    'Lingkungan',
                                    'Masjid Berdaya',
                                    'Mualaf',
                                    'Bantuan Kesehatan',
                                    'Negara Terdampak'
                                ] as $filter)
                                    <label class="campaign-filter-item">
                                        <input type="checkbox" name="filter_campaign[]" value="{{ $filter }}">
                                        <span>{{ $filter }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <small class="campaign-note" id="filterNote">Catatan: maksimal 4 filter.</small>
                        </div>
                    </section>

                </div>

                {{-- RIGHT SIDEBAR --}}
                <aside class="campaign-create-sidebar">

                    <section class="campaign-side-card">
                        <div class="campaign-side-head">
                            <h2>Form Type</h2>
                            <p>Atur bentuk donasi yang akan tampil kepada donatur.</p>
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
                                    <input type="file" name="packages[0][image]" accept="image/png,image/jpeg,image/jpg" hidden>
                                    <span>
                                        <i class="bi bi-image"></i>
                                    </span>
                                    <small>Tambahkan Gambar</small>
                                </label>

                                <div class="campaign-field compact">
                                    <label>Judul Package <span>*</span></label>
                                    <div class="campaign-input-wrap">
                                        <input type="text" name="packages[0][title]" placeholder="Masukkan judul package" required>
                                        <i class="bi bi-pencil-fill"></i>
                                    </div>
                                </div>

                                <div class="campaign-field compact">
                                    <label>Deskripsi Package <small>(Opsional)</small></label>
                                    <div class="campaign-input-wrap">
                                        <textarea name="packages[0][description]" rows="3" placeholder="Masukkan deskripsi package"></textarea>
                                        <i class="bi bi-pencil-fill"></i>
                                    </div>
                                </div>

                                <div class="campaign-field compact">
                                    <label>Nominal Package <span>*</span></label>
                                    <div class="campaign-money-wrap">
                                        <span>Rp</span>
                                        <input type="text" name="packages[0][nominal]" placeholder="0" inputmode="numeric" data-money required>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <button type="button" class="campaign-add-package" id="addPackageButton">
                            <i class="bi bi-plus-lg"></i>
                            <span>Tambahkan Package Baru</span>
                        </button>
                    </section>

                    <section class="campaign-side-card">
                        <div class="campaign-side-head">
                            <h2>Fitur Tambahan</h2>
                            <p>Aktifkan informasi tambahan yang ingin ditampilkan.</p>
                        </div>

                        <div class="campaign-extra-list">
                            <label>
                                <input type="checkbox" name="fitur[]" value="jumlah_paket" checked>
                                <span>Jumlah package</span>
                            </label>

                            <label>
                                <input type="checkbox" name="fitur[]" value="nama_pekurban" checked>
                                <span>Nama pekurban / donatur</span>
                            </label>

                            <label>
                                <input type="checkbox" name="fitur[]" value="nominal_lainnya">
                                <span>Nominal lainnya</span>
                            </label>
                        </div>
                    </section>

                    <section class="campaign-side-card">
                        <div class="campaign-side-head">
                            <h2>Contoh Layout</h2>
                            <p>Preview sederhana tampilan pilihan donasi.</p>
                        </div>

                        <div class="campaign-layout-preview">
                            <div class="preview-package">
                                <span class="preview-image">
                                    <i class="bi bi-image"></i>
                                </span>

                                <div>
                                    <span></span>
                                    <span></span>
                                </div>

                                <strong>-</strong>
                                <b>1</b>
                                <strong>+</strong>
                            </div>

                            <div class="preview-list">
                                <span>Rp10.000</span>
                                <span>Rp50.000</span>
                                <span>Rp100.000</span>

                                <div class="preview-custom">
                                    <small>Masukkan donasi lainnya</small>
                                    <p>Rp 0</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <button type="submit" class="campaign-submit-button">
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

</body>
</html>