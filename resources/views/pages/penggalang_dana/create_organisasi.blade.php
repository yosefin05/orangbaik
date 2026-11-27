<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Penggalang Dana Organisasi - Orang Baik</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penggalang-register.css') }}">
</head>

<body>

    @include('components.header')

    @php
        $user = auth()->user();
    @endphp

    <main class="reg-page">
        <div class="container-form">

            {{-- ==========================================
                JUDUL
            =========================================== --}}
            <header class="reg-head">
                <span class="reg-eyebrow">Verifikasi Organisasi</span>

                <h1>Daftar Sebagai Penggalang Dana Organisasi</h1>

                <p>
                    Isi data organisasi Anda di bawah ini. Data dipakai untuk verifikasi akun dan
                    pengelolaan campaign di OrangBaik.id. Tanda <strong>*</strong> berarti wajib diisi.
                </p>

                <div class="reg-prepare">
                    <p class="reg-prepare-title">Siapkan dulu supaya cepat selesai:</p>
                    <ul>
                        <li><i class="bi bi-check-circle-fill"></i> Logo organisasi</li>
                        <li><i class="bi bi-check-circle-fill"></i> Dokumen legalitas (SK, Akta, dll.)</li>
                        <li><i class="bi bi-check-circle-fill"></i> Email &amp; nomor telepon aktif</li>
                    </ul>
                </div>
            </header>

            <div class="reg-layout">

                {{-- ==========================================
                    PROGRES (kolom kiri di desktop, bar ringkas di mobile)
                =========================================== --}}
                <aside class="reg-side" aria-label="Progres pengisian">
                    <div class="reg-progress">
                        <div class="reg-progress-top">
                            <strong>Progres pengisian</strong>
                            <span data-reg-percent>0%</span>
                        </div>

                        <div class="reg-bar">
                            <div class="reg-bar-fill" data-reg-bar style="width: 0%;"></div>
                        </div>

                        <p class="reg-progress-text" data-reg-count>Isi data wajib untuk memulai</p>
                    </div>

                    <ol class="reg-steps">
                        <li>
                            <a href="#sec-profil" data-reg-step="sec-profil">
                                <span class="reg-step-num"><b>1</b><i class="bi bi-check-lg"></i></span>
                                <span class="reg-step-label">Profil organisasi</span>
                            </a>
                        </li>
                        <li>
                            <a href="#sec-tentang" data-reg-step="sec-tentang">
                                <span class="reg-step-num"><b>2</b><i class="bi bi-check-lg"></i></span>
                                <span class="reg-step-label">Tentang organisasi</span>
                            </a>
                        </li>
                        <li>
                            <a href="#sec-dokumen" data-reg-step="sec-dokumen">
                                <span class="reg-step-num"><b>3</b><i class="bi bi-check-lg"></i></span>
                                <span class="reg-step-label">Dokumen legalitas</span>
                            </a>
                        </li>
                        <li>
                            <a href="#sec-kontak" data-reg-step="sec-kontak">
                                <span class="reg-step-num"><b>4</b><i class="bi bi-check-lg"></i></span>
                                <span class="reg-step-label">Kontak</span>
                            </a>
                        </li>
                        <li>
                            <a href="#sec-sosial" data-reg-step="sec-sosial">
                                <span class="reg-step-num"><b>5</b><i class="bi bi-check-lg"></i></span>
                                <span class="reg-step-label">Sosial media <span class="reg-step-tag">(opsional)</span></span>
                            </a>
                        </li>
                    </ol>
                </aside>


                {{-- ==========================================
                    FORM
                =========================================== --}}
                <div class="reg-main">

                    <x-alert-error />

                    <form
                        class="reg-form verify-form"
                        id="regForm"
                        action="{{ route('penggalang_dana.organisasi.store') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        novalidate
                    >
                        @csrf

                        {{-- 1. PROFIL --}}
                        <section class="reg-card" id="sec-profil" data-reg-section>
                            <div class="reg-card-head">
                                <h2>Profil Organisasi</h2>
                                <p>Data dasar yang akan tampil di halaman profil penggalang dana.</p>
                            </div>

                            <div class="reg-stack">

                                {{-- Banner --}}
                                <div class="reg-field">
                                    <span class="reg-label">Banner <em>(opsional)</em></span>

                                    <div class="reg-cover verify-cover-upload">
                                        <img
                                            id="thumbnailPreview"
                                            class="reg-cover-preview verify-cover-preview"
                                            src=""
                                            alt="Preview banner organisasi"
                                        >

                                        <div class="reg-cover-placeholder verify-cover-placeholder" id="thumbnailPlaceholder">
                                            <i class="bi bi-image-fill"></i>
                                            <strong>Belum ada banner</strong>
                                            <span>Format JPG, PNG, atau WEBP</span>
                                        </div>

                                        <label class="reg-cover-button verify-cover-button">
                                            <input type="file" id="thumbnailInput" name="thumbnail" accept="image/*">
                                            <i class="bi bi-camera-fill"></i>
                                            <span>Pilih Banner</span>
                                        </label>
                                    </div>

                                    @error('thumbnail')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Logo --}}
                                <div class="reg-field">
                                    <span class="reg-label">Logo <em>(opsional)</em></span>

                                    <div class="reg-avatar">
                                        <div class="reg-avatar-preview verify-avatar-preview" id="fotoProfilPreview">
                                            <i class="bi bi-building-fill"></i>
                                        </div>

                                        <div class="reg-avatar-actions">
                                            <label class="reg-upload-btn verify-upload-button">
                                                <input type="file" id="fotoProfilInput" name="foto_profil" accept="image/*">
                                                <i class="bi bi-camera-fill"></i>
                                                <span>Upload Logo</span>
                                            </label>
                                            <p class="reg-hint">Gambar persegi akan terlihat paling rapi.</p>
                                        </div>
                                    </div>

                                    @error('foto_profil')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="reg-grid-2">
                                    <label class="reg-field">
                                        <span class="reg-label">Kategori <b>*</b></span>

                                        <select name="jenis_penggalang" id="jenisPenggalangSelect" data-reg-required>
                                            <option value="organisasi" {{ (old('jenis_penggalang', $selectedCategory ?? 'organisasi') === 'organisasi') ? 'selected' : '' }}>Organisasi</option>
                                            <option value="yayasan_lembaga" {{ (old('jenis_penggalang', $selectedCategory ?? '') === 'yayasan_lembaga') ? 'selected' : '' }}>Yayasan / Lembaga</option>
                                            <option value="komunitas" {{ (old('jenis_penggalang', $selectedCategory ?? '') === 'komunitas') ? 'selected' : '' }}>Komunitas</option>
                                        </select>

                                        @error('jenis_penggalang')
                                            <small class="reg-error">{{ $message }}</small>
                                        @enderror
                                    </label>

                                    <label class="reg-field">
                                        <span class="reg-label">Tahun Berdiri <b>*</b></span>

                                        <select name="tahun_berdiri" required data-reg-required>
                                            <option value="">Pilih tahun</option>

                                            @for($tahun = date('Y'); $tahun >= 1990; $tahun--)
                                                <option value="{{ $tahun }}" {{ old('tahun_berdiri') == $tahun ? 'selected' : '' }}>
                                                    {{ $tahun }}
                                                </option>
                                            @endfor
                                        </select>

                                        @error('tahun_berdiri')
                                            <small class="reg-error">{{ $message }}</small>
                                        @enderror
                                    </label>
                                </div>

                                <label class="reg-field">
                                    <span class="reg-label">Nama Organisasi <b>*</b></span>

                                    <input
                                        type="text"
                                        name="nama_penggalang"
                                        value="{{ old('nama_penggalang') }}"
                                        placeholder="Contoh: Yayasan Peduli Sesama"
                                        data-reg-required
                                    >
                                    <p class="reg-hint">Tulis sesuai nama resmi di dokumen legalitas.</p>

                                    @error('nama_penggalang')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </label>

                                <label class="reg-field">
                                    <span class="reg-label">Alamat Kantor <b>*</b></span>

                                    <textarea
                                        name="alamat"
                                        rows="3"
                                        placeholder="Jalan, kelurahan, kecamatan, kota, provinsi"
                                        data-reg-required
                                    >{{ old('alamat') }}</textarea>

                                    @error('alamat')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </label>

                            </div>
                        </section>

                        {{-- 2. TENTANG --}}
                        <section class="reg-card" id="sec-tentang" data-reg-section>
                            <div class="reg-card-head">
                                <h2>Tentang Organisasi</h2>
                                <p>Ceritakan singkat supaya donatur mengenal dan percaya pada organisasi Anda.</p>
                            </div>

                            <div class="reg-stack">

                                <label class="reg-field">
                                    <span class="reg-label">Deskripsi <b>*</b></span>

                                    <textarea
                                        name="deskripsi"
                                        rows="5"
                                        class="is-tall"
                                        placeholder="Apa kegiatan organisasi dan siapa yang dibantu?"
                                        data-reg-required
                                    >{{ old('deskripsi') }}</textarea>

                                    @error('deskripsi')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </label>

                                <div class="reg-grid-2">
                                    <label class="reg-field">
                                        <span class="reg-label">Visi <b>*</b></span>

                                        <textarea name="visi" rows="3" placeholder="Tujuan jangka panjang organisasi" data-reg-required>{{ old('visi') }}</textarea>

                                        @error('visi')
                                            <small class="reg-error">{{ $message }}</small>
                                        @enderror
                                    </label>

                                    <label class="reg-field">
                                        <span class="reg-label">Misi <b>*</b></span>

                                        <textarea
                                            name="misi"
                                            rows="4"
                                            placeholder="Langkah-langkah untuk mencapai visi"
                                            data-reg-required
                                        >{{ old('misi') }}</textarea>

                                        @error('misi')
                                            <small class="reg-error">{{ $message }}</small>
                                        @enderror
                                    </label>
                                </div>

                            </div>
                        </section>

                        {{-- 3. DOKUMEN --}}
                        <section class="reg-card" id="sec-dokumen" data-reg-section>
                            <div class="reg-card-head">
                                <h2>Dokumen Legalitas</h2>
                                <p>
                                    Lampirkan minimal satu dokumen legalitas untuk meningkatkan
                                    kepercayaan donatur.
                                </p>
                            </div>

                            <div class="reg-doc-list">

                                @for($i = 1; $i <= 3; $i++)

                                    @if($i === 2)
                                        <details class="reg-more" {{ (old('nama_dokumen.1') || old('nama_dokumen.2')) ? 'open' : '' }}>
                                            <summary>
                                                Tambah dokumen lain (opsional)
                                                <i class="bi bi-chevron-down"></i>
                                            </summary>

                                            <div class="reg-doc-list reg-more-body">
                                    @endif

                                    <div class="reg-doc">
                                        <h3 class="reg-doc-title">
                                            Dokumen {{ $i }}

                                            @if($i === 1)
                                                <b>*</b>
                                            @else
                                                <em>Opsional</em>
                                            @endif
                                        </h3>

                                        <div class="reg-stack">

                                            <label class="reg-field">
                                                <span class="reg-label">Nama Dokumen {!! $i === 1 ? '<b>*</b>' : '' !!}</span>

                                                <input
                                                    type="text"
                                                    name="nama_dokumen[]"
                                                    value="{{ old('nama_dokumen.' . ($i - 1)) }}"
                                                    placeholder="Contoh: SK Kemenkumham, Akta, Izin Operasional"
                                                    @if($i === 1) data-reg-required @endif
                                                >

                                                @if($i === 1)
                                                    @error('nama_dokumen.0')
                                                        <small class="reg-error">{{ $message }}</small>
                                                    @enderror
                                                @endif
                                            </label>

                                            <div
                                                class="reg-doc-upload"
                                                @if($i === 1) data-reg-required-group @endif
                                            >
                                                <label class="reg-field">
                                                    <span class="reg-label">
                                                        Unggah File {!! $i === 1 ? '<b>*</b>' : '' !!}
                                                    </span>

                                                    <input type="file" name="dokumen_files[]" accept=".pdf,.jpg,.jpeg,.png">
                                                    <p class="reg-hint">PDF, JPG, atau PNG. Maksimal 5 MB.</p>
                                                </label>

                                                        @if($i === 1)
                                                    @error('dokumen_files.0')
                                                        <small class="reg-error">{{ $message }}</small>
                                                    @enderror
                                                @endif
                                            </div>

                                        </div>
                                    </div>

                                    @if($i === 3)
                                            </div>
                                        </details>
                                    @endif

                                @endfor

                            </div>
                        </section>

                        {{-- 4. KONTAK --}}
                        <section class="reg-card" id="sec-kontak" data-reg-section>
                            <div class="reg-card-head">
                                <h2>Kontak</h2>
                                <p>Dipakai tim OrangBaik.id untuk menghubungi organisasi Anda saat verifikasi.</p>
                            </div>

                            <div class="reg-grid-2">
                                <label class="reg-field">
                                    <span class="reg-label">Email <b>*</b></span>

                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $user->email ?? '') }}"
                                        placeholder="nama@organisasi.com"
                                        data-reg-required
                                    >

                                    @error('email')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </label>

                                <label class="reg-field">
                                    <span class="reg-label">Nomor Telepon / Hotline <b>*</b></span>

                                    <input
                                        type="text"
                                        name="no_telepon"
                                        value="{{ old('no_telepon', $user->nomor ?? '') }}"
                                        placeholder="Contoh: 081234567890"
                                        inputmode="tel"
                                        data-reg-required
                                    >

                                    @error('no_telepon')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </label>
                            </div>
                        </section>

                        {{-- 5. SOSIAL MEDIA --}}
                        <section class="reg-card" id="sec-sosial" data-reg-section>
                            <div class="reg-card-head">
                                <h2>Sosial Media <span class="reg-tag">Opsional</span></h2>
                                <p>Membantu donatur mengenal organisasi Anda lebih jauh.</p>
                            </div>

                            <details class="reg-more" {{ (old('instagram') || old('facebook') || old('youtube') || old('tiktok')) ? 'open' : '' }}>
                                <summary>
                                    Tambahkan akun sosial media
                                    <i class="bi bi-chevron-down"></i>
                                </summary>

                                <div class="reg-grid-2 reg-more-body">
                                    <label class="reg-field">
                                        <span class="reg-label">Instagram</span>
                                        <input type="text" name="instagram" value="{{ old('instagram') }}" placeholder="Username Instagram">
                                    </label>

                                    <label class="reg-field">
                                        <span class="reg-label">Facebook</span>
                                        <input type="text" name="facebook" value="{{ old('facebook') }}" placeholder="Username Facebook">
                                    </label>

                                    <label class="reg-field">
                                        <span class="reg-label">YouTube</span>
                                        <input type="text" name="youtube" value="{{ old('youtube') }}" placeholder="Username YouTube">
                                    </label>

                                    <label class="reg-field">
                                        <span class="reg-label">TikTok</span>
                                        <input type="text" name="tiktok" value="{{ old('tiktok') }}" placeholder="Username TikTok">
                                    </label>
                                </div>
                            </details>
                        </section>

                        {{-- AKSI --}}
                        <div class="reg-actions">
                            <a href="{{ route('profile.user') }}" class="reg-btn reg-btn-ghost verify-cancel-button">
                                Batal
                            </a>

                            <button type="submit" class="reg-btn reg-btn-primary verify-submit-button">
                                Kirim Verifikasi
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/penggalang-organisasi.js') }}"></script>
    <script src="{{ asset('js/penggalang-register.js') }}"></script>

</body>

</html>
