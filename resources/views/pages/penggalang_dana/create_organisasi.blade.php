<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Penggalang Dana Organisasi - Orang Baik</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/verify-penggalang.css') }}">
</head>

<body>

    @include('components.header')

    @php
        $user = auth()->user();
    @endphp

    <main class="verify-page">

        <section class="verify-hero">
            <div class="container">
                <div class="verify-heading">
                    <span class="verify-eyebrow">Verifikasi Organisasi</span>

                    <h1>Verifikasi Akun Penggalang Dana</h1>

                    <p>
                        Lengkapi informasi organisasi untuk proses pendaftaran penggalang dana.
                        Data yang diberikan akan digunakan untuk verifikasi akun dan pengelolaan campaign di
                        OrangBaik.id.
                    </p>
                </div>
            </div>
        </section>

        <section class="verify-section">
            <div class="container">
                <x-alert-error />
                <form class="verify-form" action="{{ route('penggalang_dana.organisasi.store') }}" method="POST"
                    enctype="multipart/form-data" novalidate>
                    @csrf

                    {{-- BANNER --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Banner Organisasi</h2>
                            <p>Upload banner organisasi yang akan ditampilkan pada profil penggalang dana.</p>
                        </div>

                        <div class="verify-cover-upload">
                            <img id="thumbnailPreview" class="verify-cover-preview" src=""
                                alt="Preview banner organisasi">

                            <div class="verify-cover-placeholder" id="thumbnailPlaceholder">
                                <i class="bi bi-image-fill"></i>
                                <strong>Upload Banner Organisasi</strong>
                                <span>Format JPG, PNG, atau WEBP</span>
                            </div>

                            <label class="verify-cover-button">
                                <input type="file" id="thumbnailInput" name="thumbnail" accept="image/*">
                                <i class="bi bi-camera-fill"></i>
                                <span>Pilih Banner</span>
                            </label>
                        </div>
                        @error('thumbnail')
                            <small class="text-danger"
                                style="color:#ef4444; font-size:12px; margin-top:6px; display:block;">{{ $message }}</small>
                        @enderror
                    </section>

                    {{-- PROFIL --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Profil Penggalang Dana</h2>
                            <p>Masukkan data dasar organisasi sebagai penggalang dana.</p>
                        </div>

                        <div class="verify-profile-grid">

                            <div class="verify-avatar-upload">
                                <div class="verify-avatar-preview" id="fotoProfilPreview">
                                    <i class="bi bi-building-fill"></i>
                                </div>

                                <label class="verify-upload-button">
                                    <input type="file" id="fotoProfilInput" name="foto_profil" accept="image/*">
                                    <i class="bi bi-camera-fill"></i>
                                    <span>Upload Logo</span>
                                </label>
                                @error('foto_profil')
                                    <small class="text-danger"
                                        style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="verify-fields">
                                <label class="verify-field">
                                    <span>Kategori Penggalang Dana <b>*</b></span>
                                    <select name="jenis_penggalang" id="jenisPenggalangSelect" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; font-size:14px; background:#fff; font-family:inherit;">
                                        <option value="organisasi" {{ (old('jenis_penggalang', $selectedCategory ?? 'organisasi') === 'organisasi') ? 'selected' : '' }}>Organisasi</option>
                                        <option value="yayasan_lembaga" {{ (old('jenis_penggalang', $selectedCategory ?? '') === 'yayasan_lembaga') ? 'selected' : '' }}>Yayasan / Lembaga</option>
                                        <option value="komunitas" {{ (old('jenis_penggalang', $selectedCategory ?? '') === 'komunitas') ? 'selected' : '' }}>Komunitas</option>
                                    </select>
                                    @error('jenis_penggalang')
                                        <small class="text-danger" style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</small>
                                    @enderror
                                </label>

                                <label class="verify-field">
                                    <span>Nama Organisasi <b>*</b></span>

                                    <input type="text" name="nama_penggalang" value="{{ old('nama_penggalang') }}"
                                        placeholder="Masukkan nama organisasi">
                                    @error('nama_penggalang')
                                        <small class="text-danger"
                                            style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</small>
                                    @enderror
                                </label>
                            </div>

                        </div>

                        <div class="verify-grid-2">
                            <label class="verify-field">
                                <span>Tahun Berdiri <b>*</b></span>

                                <select name="tahun_berdiri" required>
                                    <option value="">Pilih tahun berdiri</option>

                                    @for($tahun = date('Y'); $tahun >= 1990; $tahun--)
                                        <option value="{{ $tahun }}" {{ old('tahun_berdiri') == $tahun ? 'selected' : '' }}>
                                            {{ $tahun }}
                                        </option>
                                    @endfor
                                </select>
                                @error('tahun_berdiri')
                                    <small class="text-danger"
                                        style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</small>
                                @enderror
                            </label>
                        </div>

                        <label class="verify-field">
                            <span>Alamat Kantor <b>*</b></span>

                            <textarea name="alamat" rows="3"
                                placeholder="Masukkan alamat kantor organisasi">{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <small class="text-danger"
                                    style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</small>
                            @enderror
                        </label>
                    </section>

                    {{-- INFORMASI --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Informasi Organisasi</h2>
                            <p>Ceritakan profil, visi, dan misi organisasi sebagai penggalang dana.</p>
                        </div>

                        <label class="verify-field">
                            <span>Deskripsi Penggalang Dana <b>*</b></span>

                            <textarea name="deskripsi" rows="6"
                                placeholder="Masukkan deskripsi organisasi">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <small class="text-danger"
                                    style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</small>
                            @enderror
                        </label>

                        <div class="verify-grid-2 verify-vision-mission">
                            <label class="verify-field">
                                <span>Visi <b>*</b></span>
                                <input type="text" name="visi" value="{{ old('visi') }}"
                                    placeholder="Masukkan visi organisasi">
                                @error('visi')
                                    <small class="text-danger" style="color:#ef4444; font-size:12px; margin-top:4px;">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </label>

                            <label class="verify-field">
                                <span>Misi <b>*</b></span>
                                <textarea name="misi" rows="6"
                                    placeholder="Masukkan misi organisasi">{{ old('misi') }}</textarea>
                                @error('misi')
                                    <small class="text-danger" style="color:#ef4444; font-size:12px; margin-top:4px;">
                                        {{ $message }}
                                    </small>
                                @enderror
                            </label>
                        </div>
                    </section>

                    {{-- DOKUMEN --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Dokumen Legalitas</h2>
                            <p>
                                Lampirkan dokumen legalitas untuk memperkuat kredibilitas organisasi
                                dan meningkatkan kepercayaan donatur.
                            </p>
                        </div>

                        <div class="verify-document-list">

                            @for($i = 1; $i <= 3; $i++)
                                <div class="verify-document-item">
                                    <div class="verify-document-title">
                                        <h3>
                                            Dokumen Legalitas {{ $i }}

                                            @if($i <= 1)
                                                <b>*</b>
                                            @else
                                                <em>Opsional</em>
                                            @endif
                                        </h3>
                                    </div>

                                    <div class="verify-grid-2">
                                        <label class="verify-field">
                                            <span>Nama Legalitas {{ $i <= 1 ? '*' : '' }}</span>

                                            <input type="text" name="nama_dokumen[]"
                                                value="{{ old('nama_dokumen.' . ($i - 1)) }}"
                                                placeholder="Contoh: SK Kemenkumham, Akta, Izin Operasional">
                                            @if($i === 1)
                                                @error('nama_dokumen.0')
                                                    <small class="text-danger"
                                                        style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</small>
                                                @enderror
                                            @endif
                                        </label>

                                        <div class="verify-field">
                                            <span>Unggah Dokumen (PDF, JPG, PNG maks 5MB) {{ $i <= 1 ? '*' : '' }}</span>
                                            <input type="file" name="dokumen_files[]" accept=".pdf,.jpg,.jpeg,.png"
                                                style="padding: 8px 10px; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; width: 100%; font-size: 13px;">
                                            <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">Atau sertakan tautan online / Google Drive:</div>
                                            <input type="url" name="file_dokumen[]"
                                                value="{{ old('file_dokumen.' . ($i - 1)) }}"
                                                placeholder="https://drive.google.com/..."
                                                style="margin-top: 2px;">
                                            @if($i === 1)
                                                @error('dokumen_files.0')
                                                    <small class="text-danger" style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</small>
                                                @enderror
                                                @error('file_dokumen.0')
                                                    <small class="text-danger" style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</small>
                                                @enderror
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endfor

                        </div>
                    </section>

                    {{-- KONTAK --}}
                    <section class="verify-card">

                        <div class="verify-card-header">
                            <h2>Kontak</h2>
                            <p>
                                Informasi kontak digunakan untuk proses verifikasi akun
                                dan memudahkan tim OrangBaik.id menghubungi organisasi Anda.
                            </p>
                        </div>

                        <div class="verify-grid-2">

                            <label class="verify-field">

                                <span>Email <b>*</b></span>

                                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                                    placeholder="Masukkan Email Organisasi">

                                @error('email')
                                    <small class="text-danger"
                                        style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</small>
                                @enderror

                            </label>

                            <label class="verify-field">

                                <span>Nomor Hotline <b>*</b></span>

                                <input type="text" name="no_telepon" value="{{ old('no_telepon', $user->nomor ?? '') }}"
                                    placeholder="Masukkan Nomor Hotline">

                                @error('no_telepon')
                                    <small class="text-danger"
                                        style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</small>
                                @enderror

                            </label>

                        </div>

                    </section>
                    {{-- SOSIAL MEDIA --}}
                    <section class="verify-card">

                        <div class="verify-card-header">
                            <h2>Sosial Media</h2>
                            <p>
                                Bagian ini bersifat opsional. Tambahkan media sosial agar
                                donatur lebih mudah mengenal organisasi Anda.
                            </p>
                        </div>

                        <div class="verify-grid-2">

                            <label class="verify-field">

                                <span>Instagram</span>

                                <input type="text" name="instagram" value="{{ old('instagram') }}"
                                    placeholder="Username Instagram">

                            </label>

                            <label class="verify-field">

                                <span>Facebook</span>

                                <input type="text" name="facebook" value="{{ old('facebook') }}"
                                    placeholder="Username Facebook">

                            </label>

                            <label class="verify-field">

                                <span>Youtube</span>

                                <input type="text" name="youtube" value="{{ old('youtube') }}"
                                    placeholder="Username Youtube">

                            </label>

                            <label class="verify-field">

                                <span>TikTok</span>

                                <input type="text" name="tiktok" value="{{ old('tiktok') }}"
                                    placeholder="Username TikTok">

                            </label>

                        </div>

                    </section>
                    <div class="verify-actions">

                        <a href="{{ route('profile.user') }}" class="verify-cancel-button">
                            Batal
                        </a>

                        <button type="submit" class="verify-submit-button">
                            Kirim Verifikasi
                        </button>

                    </div>

                </form>
            </div>
        </section>
    </main>
    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/penggalang-organisasi.js') }}"></script>

</body>

</html>