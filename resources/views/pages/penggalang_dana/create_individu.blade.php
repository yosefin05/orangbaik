<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Penggalang Dana Individu - Orang Baik</title>

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
                <span class="reg-eyebrow">Verifikasi Akun</span>

                <h1>Daftar Sebagai Penggalang Dana Individu</h1>

                <p>
                    Isi data diri Anda di bawah ini. Data dipakai untuk verifikasi akun dan
                    pengelolaan campaign di OrangBaik.id. Tanda <strong>*</strong> berarti wajib diisi.
                </p>

                <div class="reg-prepare">
                    <p class="reg-prepare-title">Siapkan dulu supaya cepat selesai:</p>
                    <ul>
                        <li><i class="bi bi-check-circle-fill"></i> Foto profil</li>
                        <li><i class="bi bi-check-circle-fill"></i> Foto/scan KTP atau Paspor</li>
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
                                <span class="reg-step-label">Profil</span>
                            </a>
                        </li>
                        <li>
                            <a href="#sec-tentang" data-reg-step="sec-tentang">
                                <span class="reg-step-num"><b>2</b><i class="bi bi-check-lg"></i></span>
                                <span class="reg-step-label">Tentang Anda</span>
                            </a>
                        </li>
                        <li>
                            <a href="#sec-dokumen" data-reg-step="sec-dokumen">
                                <span class="reg-step-num"><b>3</b><i class="bi bi-check-lg"></i></span>
                                <span class="reg-step-label">Dokumen identitas</span>
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
                        action="{{ route('penggalang_dana.individu.store') }}"
                        method="POST"
                        enctype="multipart/form-data"
                        novalidate
                    >
                        @csrf

                        {{-- 1. PROFIL --}}
                        <section class="reg-card" id="sec-profil" data-reg-section>
                            <div class="reg-card-head">
                                <h2>Profil</h2>
                                <p>Data dasar yang akan tampil di halaman profil penggalang dana.</p>
                            </div>

                            <div class="reg-stack">

                                {{-- Foto --}}
                                <div class="reg-field">
                                    <span class="reg-label">Foto Profil <em>(opsional)</em></span>

                                    <div class="reg-avatar">
                                        <div class="reg-avatar-preview verify-avatar-preview" id="avatarPreview">
                                            @if(old('foto_profil'))
                                                <img src="{{ old('foto_profil') }}" alt="Foto profil">
                                            @else
                                                <i class="bi bi-person-fill"></i>
                                            @endif
                                        </div>

                                        <div class="reg-avatar-actions">
                                            <label class="reg-upload-btn verify-upload-button">
                                                <input type="file" name="foto_profil" id="fotoProfilInput" accept="image/*">
                                                <i class="bi bi-camera-fill"></i>
                                                <span>Upload Foto</span>
                                            </label>
                                            <p class="reg-hint">Pakai foto wajah yang jelas.</p>
                                        </div>
                                    </div>

                                    @error('foto_profil')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Jenis penggalang: tetap "individu" (tidak bisa diubah) --}}
                                <div class="reg-field">
                                    <span class="reg-label">Jenis Penggalang Dana</span>
                                    <span class="reg-static"><i class="bi bi-person-fill"></i> Individu</span>
                                    <input type="hidden" name="jenis_penggalang" value="individu">
                                </div>

                                <label class="reg-field">
                                    <span class="reg-label">Nama Lengkap <b>*</b></span>

                                    <input
                                        type="text"
                                        name="nama_penggalang"
                                        value="{{ old('nama_penggalang', $user->name) }}"
                                        placeholder="Nama sesuai KTP"
                                        data-reg-required
                                    >

                                    @error('nama_penggalang')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </label>

                                <label class="reg-field">
                                    <span class="reg-label">Alamat Domisili <b>*</b></span>

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
                                <h2>Tentang Anda</h2>
                                <p>Ceritakan singkat siapa Anda dan tujuan Anda menggalang dana.</p>
                            </div>

                            <div class="reg-stack">

                                <label class="reg-field">
                                    <span class="reg-label">Deskripsi <b>*</b></span>

                                    <textarea
                                        name="deskripsi"
                                        rows="5"
                                        class="is-tall"
                                        placeholder="Ceritakan siapa Anda dan apa tujuan Anda"
                                        data-reg-required
                                    >{{ old('deskripsi') }}</textarea>

                                    @error('deskripsi')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </label>

                                <div class="reg-grid-2">
                                    <label class="reg-field">
                                        <span class="reg-label">Visi <em>(opsional)</em></span>

                                        <textarea name="visi" rows="3" placeholder="Tujuan jangka panjang Anda">{{ old('visi') }}</textarea>

                                        @error('visi')
                                            <small class="reg-error">{{ $message }}</small>
                                        @enderror
                                    </label>

                                    <label class="reg-field">
                                        <span class="reg-label">Misi <em>(opsional)</em></span>

                                        <textarea name="misi" rows="4" placeholder="Langkah untuk mencapainya, satu poin per baris">{{ old('misi') }}</textarea>

                                        @error('misi')
                                            <small class="reg-error">{{ $message }}</small>
                                        @enderror
                                    </label>
                                </div>

                            </div>
                        </section>

                        {{-- 3. DOKUMEN IDENTITAS --}}
                        <section class="reg-card" id="sec-dokumen" data-reg-section>
                            <div class="reg-card-head">
                                <h2>Dokumen Identitas</h2>
                                <p>Unggah KTP atau Paspor untuk memastikan identitas Anda.</p>
                            </div>

                            <p class="reg-note">
                                <i class="bi bi-shield-lock-fill"></i>
                                <span>
                                    Dokumen disimpan di penyimpanan privat yang aman dan
                                    <strong>tidak akan pernah dipublikasikan</strong>.
                                </span>
                            </p>

                            <div class="reg-stack">

                                <label class="reg-field">
                                    <span class="reg-label">Nama Dokumen <b>*</b></span>

                                    <input
                                        type="text"
                                        name="nama_dokumen[]"
                                        value="KTP Identitas"
                                        placeholder="Contoh: KTP"
                                        required
                                        data-reg-required
                                    >

                                    @error('nama_dokumen.0')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </label>

                                <div class="reg-doc-upload" data-reg-required-group>
                                    <label class="reg-field">
                                        <span class="reg-label">Unggah File <b>*</b></span>

                                        <input type="file" name="dokumen_files[]" accept=".pdf,.jpg,.jpeg,.png">
                                        <p class="reg-hint">PDF, JPG, atau PNG. Maksimal 5 MB.</p>
                                    </label>

                                    <div class="reg-or">atau</div>

                                    <label class="reg-field">
                                        <span class="reg-label">Tempel Tautan</span>

                                        <input type="url" name="file_dokumen[]" placeholder="https://drive.google.com/...">
                                        <p class="reg-hint">Boleh pakai tautan Google Drive atau penyimpanan online lain.</p>
                                    </label>

                                    @error('dokumen_files.0')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                    @error('file_dokumen.0')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </div>

                            </div>
                        </section>

                        {{-- 4. KONTAK --}}
                        <section class="reg-card" id="sec-kontak" data-reg-section>
                            <div class="reg-card-head">
                                <h2>Kontak</h2>
                                <p>Dipakai tim OrangBaik.id untuk menghubungi Anda saat verifikasi.</p>
                            </div>

                            <div class="reg-grid-2">
                                <label class="reg-field">
                                    <span class="reg-label">Email <b>*</b></span>

                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $user->email) }}"
                                        placeholder="nama@email.com"
                                        data-reg-required
                                    >

                                    @error('email')
                                        <small class="reg-error">{{ $message }}</small>
                                    @enderror
                                </label>

                                <label class="reg-field">
                                    <span class="reg-label">Nomor Telepon / WhatsApp <b>*</b></span>

                                    <input
                                        type="text"
                                        name="no_telepon"
                                        value="{{ old('no_telepon', $user->nomor) }}"
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
                                <p>Membantu donatur mengenal Anda lebih jauh.</p>
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
    <script src="{{ asset('js/penggalang-individu.js') }}"></script>
    <script src="{{ asset('js/penggalang-register.js') }}"></script>

</body>

</html>
