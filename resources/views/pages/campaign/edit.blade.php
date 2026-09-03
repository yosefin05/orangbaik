<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Campaign - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/campaign-create.css') }}">

    <!-- Tambahan style untuk custom slug (opsional) -->
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
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('campaign.update', $campaign->id) }}" method="POST" enctype="multipart/form-data"
                    class="campaign-create-layout" id="campaignCreateForm" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- LEFT FORM --}}
                    <div class="campaign-create-main">

                        <div class="campaign-create-heading">
                            <h1>Edit Campaign</h1>
                            <p>Perbarui informasi campaign Anda agar tetap akurat dan menarik bagi donatur.</p>
                        </div>

                        {{-- THUMBNAIL --}}
                        <section class="campaign-create-card">
                            <div class="campaign-create-card-head">
                                <h2>Thumbnail / Poster Campaign</h2>
                                <p>Unggah thumbnail atau poster yang menarik dan sesuai dengan tujuan campaign.</p>
                            </div>

                            <label class="campaign-upload-large" for="thumbnail">
                                <input type="file" id="thumbnail" name="thumbnail"
                                    accept="image/png,image/jpeg,image/jpg" hidden>
                                <img src="{{ $campaign->thumbnail ? asset('storage/' . $campaign->thumbnail) : '' }}"
                                    alt="Thumbnail Campaign" class="campaign-upload-preview" data-preview="thumbnail" {{ $campaign->thumbnail ? '' : 'hidden' }}>
                                <span class="campaign-upload-placeholder" {{ $campaign->thumbnail ? 'hidden' : '' }}>
                                    <i class="bi bi-image"></i>
                                </span>
                                <span class="campaign-upload-button">
                                    <i class="bi bi-camera-fill"></i>
                                </span>
                            </label>

                            <small class="campaign-note">Catatan: Ukuran thumbnail/poster disarankan 734 × 394 px.
                                Kosongkan jika tidak ingin mengubah.</small>
                        </section>

                        {{-- INFO CAMPAIGN --}}
                        <section class="campaign-create-card">
                            <div class="campaign-create-card-head">
                                <h2>Informasi Campaign</h2>
                                <p>Perbarui informasi campaign secara jelas agar mudah dipahami dan dipercaya calon
                                    donatur.</p>
                            </div>

                            <!-- Judul -->
                            <div class="campaign-field">
                                <label for="judul">Judul Campaign <span>*</span></label>
                                <div class="campaign-input-wrap">
                                    <input type="text" id="judul" name="judul"
                                        value="{{ old('judul', $campaign->judul) }}"
                                        placeholder="Masukkan judul campaign Anda" required>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                                @error('judul')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- ====== CUSTOM SLUG ====== -->
                            <div class="campaign-field">
                                <label for="custom_slug">Link Campaign</label>
                                <div class="campaign-slug-wrap">
                                    <span class="slug-prefix">orangbaik.id/</span>
                                    <input type="text" name="custom_slug" id="custom_slug" class="slug-input"
                                        placeholder="contoh: bantu-korban-banjir"
                                        value="{{ old('custom_slug', $campaign->custom_slug) }}">
                                </div>
                                <small class="campaign-note">Gunakan nama singkat agar mudah dibagikan. Kosongkan untuk
                                    menggunakan slug otomatis.</small>
                            </div>
                            <!-- ====== END CUSTOM SLUG ====== -->

                            <!-- Deskripsi -->
                            <div class="campaign-field">
                                <label for="deskripsi_campaign">Deskripsi Campaign <span>*</span></label>
                                <div class="campaign-input-wrap">
                                    <textarea id="deskripsi_campaign" name="deskripsi" rows="7"
                                        placeholder="Masukkan deskripsi campaign Anda"
                                        required>{{ old('deskripsi', $campaign->deskripsi) }}</textarea>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                                @error('deskripsi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Gambar Pendukung -->
                            <div class="campaign-field">
                                <label>Gambar Pendukung <small>(Opsional)</small></label>
                                <div class="campaign-support-grid">
                                    @php
                                        $gambarPendukung = $campaign->campaignGambar ?? collect();
                                    @endphp

                                    @for ($i = 0; $i < 2; $i++)
                                        <label class="campaign-upload-small" for="gambar_pendukung_{{ $i + 1 }}">
                                            <input type="file" id="gambar_pendukung_{{ $i + 1 }}" name="gambar_pendukung[]"
                                                accept="image/png,image/jpeg,image/jpg" hidden>
                                            <img src="{{ isset($gambarPendukung[$i]) ? asset('storage/' . $gambarPendukung[$i]->foto) : '' }}"
                                                alt="Gambar Pendukung {{ $i + 1 }}" class="campaign-upload-preview"
                                                data-preview="gambar_pendukung_{{ $i + 1 }}" {{ isset($gambarPendukung[$i]) ? '' : 'hidden' }}>
                                            <span class="campaign-upload-placeholder" {{ isset($gambarPendukung[$i]) ? 'hidden' : '' }}>
                                                <i class="bi bi-image"></i>
                                            </span>
                                            <span class="campaign-upload-button small">
                                                <i class="bi bi-camera-fill"></i>
                                            </span>
                                        </label>
                                    @endfor
                                </div>
                                <small class="campaign-note">Catatan: Ukuran gambar pendukung disarankan 354 × 190 px.
                                    Kosongkan jika tidak ingin mengubah.</small>
                            </div>
                        </section>

                        {{-- DETAIL CAMPAIGN --}}
                        <section class="campaign-create-card">
                            <div class="campaign-create-card-head">
                                <h2>Detail Campaign</h2>
                                <p>Perbarui periode campaign dan target donasi sebagai acuan selama proses penggalangan
                                    dana berlangsung.</p>
                            </div>

                            <div class="campaign-two-grid">
                                <div class="campaign-field">
                                    <label for="tanggal_mulai">Tanggal Mulai <span>*</span></label>
                                    <div class="campaign-input-wrap">
                                        <input type="date" id="tanggal_mulai" name="tanggal_mulai"
                                            value="{{ old('tanggal_mulai', \Carbon\Carbon::parse($campaign->tanggal_mulai)->format('Y-m-d')) }}"
                                            required>
                                        <i class="bi bi-calendar-event-fill"></i>
                                    </div>
                                    @error('tanggal_mulai')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="campaign-field">
                                    <label for="tanggal_akhir">Tanggal Akhir <small>(Opsional, tanpa batas jika
                                            kosong)</small></label>
                                    <div class="campaign-input-wrap">
                                        <input type="date" id="tanggal_akhir" name="tanggal_berakhir"
                                            value="{{ old('tanggal_berakhir', $campaign->tanggal_berakhir ? \Carbon\Carbon::parse($campaign->tanggal_berakhir)->format('Y-m-d') : '') }}">
                                        <i class="bi bi-calendar-event-fill"></i>
                                    </div>
                                    @error('tanggal_berakhir')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="campaign-field">
                                <label for="target_donasi">Target Donasi <span>*</span></label>
                                <div class="campaign-money-wrap">
                                    <span>Rp</span>
                                    <input type="text" id="target_donasi" name="target_donasi"
                                        value="{{ old('target_donasi', number_format($campaign->target_donasi, 0, ',', '.')) }}"
                                        placeholder="0" inputmode="numeric" data-money required>
                                </div>
                                @error('target_donasi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="campaign-field">
                                <label for="minimal_donasi">Minimal Donasi <span>*</span></label>
                                <div class="campaign-money-wrap">
                                    <span>Rp</span>
                                    <input type="text" id="minimal_donasi" name="minimal_donasi"
                                        value="{{ old('minimal_donasi', number_format($campaign->minimal_donasi, 0, ',', '.')) }}"
                                        placeholder="0" inputmode="numeric" data-money required>
                                </div>
                                @error('minimal_donasi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </section>

                        {{-- CATEGORY --}}
                        <section class="campaign-create-card">
                            <div class="campaign-create-card-head">
                                <h2>Pilih Kategori untuk Campaign Anda</h2>
                                <p>Pilih kategori sesuai kebutuhan campaign agar informasi tersampaikan dengan lebih
                                    jelas kepada donatur.</p>
                            </div>

                            {{-- Kategori --}}
                            <div class="campaign-field">
                                <label for="kategori_id">Kategori Campaign <span>*</span></label>
                                <div class="campaign-select-wrap">
                                    <select id="kategori_id" name="kategori_id" required>
                                        <option value="">Pilih kategori campaign Anda</option>
                                        @foreach ($kategori as $item)
                                            <option value="{{ $item->id }}" {{ old('kategori_id', $campaign->kategori_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                @error('kategori_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Tipe Campaign -->
                            <div class="campaign-field">
                                <label for="campaign_type">
                                    Tipe Campaign <span>*</span>
                                </label>

                                <div class="campaign-select-wrap">
                                    <select id="campaign_type" name="campaign_type" required>
                                        <option value="regular" {{ old('campaign_type', $campaign->campaign_type) === 'regular' ? 'selected' : '' }}>
                                            Campaign Reguler
                                        </option>

                                        <option value="emergency" {{ old('campaign_type', $campaign->campaign_type) === 'emergency' ? 'selected' : '' }}>
                                            🔥 Donasi Darurat
                                        </option>

                                        <option value="sustainable" {{ old('campaign_type', $campaign->campaign_type) === 'sustainable' ? 'selected' : '' }}>
                                            ♻️ Donasi Berkelanjutan
                                        </option>
                                    </select>

                                    <i class="bi bi-chevron-down"></i>
                                </div>

                                <small class="campaign-note" id="campaignTypeNote">
                                    @if ($campaign->campaign_type === 'emergency')
                                        <strong>🔥 Donasi Darurat</strong><br>
                                        Campaign darurat memerlukan persetujuan admin sebelum ditampilkan
                                        di halaman utama. Campaign akan muncul pada section
                                        <strong>"Darurat! Bantu Sekarang"</strong>.
                                    @elseif ($campaign->campaign_type === 'sustainable')
                                        <strong>♻️ Donasi Berkelanjutan</strong><br>
                                        Campaign berkelanjutan memerlukan persetujuan admin sebelum
                                        ditampilkan di halaman utama. Campaign akan muncul pada section
                                        <strong>"Pemberdayaan Berkelanjutan"</strong>.
                                    @else
                                        <strong>Catatan:</strong>
                                        Campaign reguler langsung tampil di halaman donasi.
                                    @endif
                                </small>

                                @error('campaign_type')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </div>

                            {{-- Filter --}}
                            <div class="campaign-field">
                                <label>Filter Campaign <span>*</span></label>
                                <div class="campaign-filter-grid">
                                    @foreach ($filter as $item)
                                        <label class="campaign-filter-item">
                                            <input type="checkbox" name="filter[]" value="{{ $item->id }}"
                                                @checked(in_array($item->id, old('filter', $campaign->filter->pluck('id')->toArray())))>
                                            <span>{{ $item->nama_filter }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <small class="campaign-note">Catatan: maksimal 4 filter.</small>
                                @error('filter')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
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
                                @php
                                    $packages = $campaign->packages ?? collect();
                                @endphp

                                @if($packages->count() > 0)
                                    @foreach($packages as $index => $package)
                                        <div class="campaign-package-item" data-package-item>
                                            <div class="campaign-package-title">
                                                <strong>Package {{ $index + 1 }}</strong>
                                                <button type="button" data-remove-package {{ $index === 0 ? 'hidden' : '' }}>
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>

                                            <input type="hidden" name="packages[{{ $index }}][id]" value="{{ $package->id }}">

                                            <label class="package-image-upload">
                                                <input type="file" name="packages[{{ $index }}][image]"
                                                    accept="image/png,image/jpeg,image/jpg" hidden>
                                                @if($package->gambar)
                                                    <img src="{{ asset('storage/' . $package->gambar) }}" alt="Package Image">
                                                @else
                                                    <span><i class="bi bi-image"></i></span>
                                                    <small>Tambahkan Gambar</small>
                                                @endif
                                            </label>

                                            <div class="campaign-field compact">
                                                <label>Judul Package <span>*</span></label>
                                                <div class="campaign-input-wrap">
                                                    <input type="text" name="packages[{{ $index }}][title]"
                                                        value="{{ old("packages.$index.title", $package->judul) }}"
                                                        placeholder="Masukkan judul package" required>
                                                    <i class="bi bi-pencil-fill"></i>
                                                </div>
                                            </div>

                                            <div class="campaign-field compact">
                                                <label>Deskripsi Package <small>(Opsional)</small></label>
                                                <div class="campaign-input-wrap">
                                                    <textarea name="packages[{{ $index }}][description]" rows="3"
                                                        placeholder="Masukkan deskripsi package">{{ old("packages.$index.description", $package->deskripsi) }}</textarea>
                                                    <i class="bi bi-pencil-fill"></i>
                                                </div>
                                            </div>

                                            <div class="campaign-field compact">
                                                <label>Nominal Package <span>*</span></label>
                                                <div class="campaign-money-wrap">
                                                    <span>Rp</span>
                                                    <input type="text" name="packages[{{ $index }}][nominal]"
                                                        value="{{ old("packages.$index.nominal", number_format($package->nominal, 0, ',', '.')) }}"
                                                        placeholder="0" inputmode="numeric" data-money required>
                                                </div>
                                                <div class="package-extra-feature"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    {{-- Fallback: 1 package kosong --}}
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
                                            <span><i class="bi bi-image"></i></span>
                                            <small>Tambahkan Gambar</small>
                                        </label>
                                        <div class="campaign-field compact">
                                            <label>Judul Package <span>*</span></label>
                                            <div class="campaign-input-wrap">
                                                <input type="text" name="packages[0][title]"
                                                    placeholder="Masukkan judul package"
                                                    value="{{ old('packages.0.title') }}" required>
                                                <i class="bi bi-pencil-fill"></i>
                                            </div>
                                        </div>
                                        <div class="campaign-field compact">
                                            <label>Deskripsi Package <small>(Opsional)</small></label>
                                            <div class="campaign-input-wrap">
                                                <textarea name="packages[0][description]" rows="3"
                                                    placeholder="Masukkan deskripsi package">{{ old('packages.0.description') }}</textarea>
                                                <i class="bi bi-pencil-fill"></i>
                                            </div>
                                        </div>
                                        <div class="campaign-field compact">
                                            <label>Nominal Package <span>*</span></label>
                                            <div class="campaign-money-wrap">
                                                <span>Rp</span>
                                                <input type="text" name="packages[0][nominal]" placeholder="0"
                                                    inputmode="numeric" data-money required
                                                    value="{{ old('packages.0.nominal') }}">
                                            </div>
                                            <div class="package-extra-feature"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button type="button" class="campaign-add-package" id="addPackageButton">
                                <i class="bi bi-plus-lg"></i>
                                <span>Tambahkan Package Baru</span>
                            </button>
                        </section>

                        <section class="campaign-side-card">
                            <div class="campaign-side-head">
                                <h2>Tambahkan Fitur Lainnya <span>(Opsional)</span></h2>
                            </div>

                            <div class="feature-row">
                                <label class="feature-check">
                                    <input type="checkbox" id="toggleQuantity" name="enable_quantity" {{ $campaign->enable_quantity ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                                <div class="feature-counter">
                                    <button type="button" class="minus"><i class="bi bi-dash"></i></button>
                                    <span class="qty">{{ $campaign->default_quantity ?? 1 }}</span>
                                    <button type="button" class="plus"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>

                            <div class="feature-input-card">
                                <label class="feature-check">
                                    <input type="checkbox" id="toggleDonatur" name="enable_nama_donatur" {{ $campaign->enable_nama_donatur ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                                <div class="feature-input">
                                    <input type="text" placeholder="Nama Pekurban"
                                        value="{{ $campaign->donatur_name_label ?? 'Nama Pekurban' }}"
                                        name="donatur_name_label">
                                    <small>Masukkan Atas Nama Pekurban</small>
                                    <i class="bi bi-pencil-fill"></i>
                                </div>
                            </div>

                            <div class="feature-input-card">
                                <label class="feature-check">
                                    <input type="checkbox" id="toggleNominal" name="enable_custom_nominal" {{ $campaign->enable_custom_nominal ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                                <div class="feature-money">
                                    <label>Nominal Lainnya</label>
                                    <div class="money-box">
                                        <span>Rp</span>
                                        <input type="text" placeholder="0" inputmode="numeric" data-money
                                            value="{{ old('custom_nominal', $campaign->custom_nominal ? number_format($campaign->custom_nominal, 0, ',', '.') : '') }}"
                                            name="custom_nominal">
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="campaign-side-card">
                            <div class="campaign-side-head">
                                <h2>Pratinjau Tata Letak Package</h2>
                                <p>Preview sederhana tampilan pilihan donasi.</p>
                            </div>
                            <div class="campaign-layout-preview">
                                <div id="previewPackageList"></div>
                            </div>
                        </section>

                        <button type="submit" class="campaign-submit-button">
                            <i class="bi bi-send-fill"></i>
                            <span>Simpan Perubahan</span>
                        </button>

                    </aside>

                </form>
            </div>
        </section>
    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/campaign-edit.js') }}"></script>
    <script src="{{ asset('js/campaign-type.js') }}"></script>
</body>

</html>