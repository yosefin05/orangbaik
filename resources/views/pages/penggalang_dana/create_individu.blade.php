<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Penggalang Dana - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penggalang-individu.css') }}">
</head>

<body>

@include('components.header')

@php
    $user = auth()->user();
@endphp

<main class="verify-page">

    <section class="verify-hero">
        <div class="container verify-hero-inner">

            <a href="{{ route('profile.user') }}" class="verify-back">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Profil</span>
            </a>

            <div class="verify-heading">
                <span class="verify-eyebrow">Verifikasi Akun</span>

                <h1>Verifikasi Akun Penggalang Dana</h1>

                <p>
                    Lengkapi informasi berikut untuk proses pendaftaran penggalang dana.
                    Data yang diberikan akan digunakan untuk verifikasi akun dan pengelolaan campaign.
                </p>
            </div>

        </div>
    </section>

    <section class="verify-section">
        <div class="container">

            <form action="#" method="POST" enctype="multipart/form-data" class="verify-form">
                @csrf

                {{-- PROFIL --}}
                <section class="verify-card">
                    <div class="verify-card-header">
                        <h2>Profil Penggalang Dana</h2>
                        <p>Masukkan data dasar penggalang dana yang akan ditampilkan pada profil.</p>
                    </div>

                    <div class="verify-profile-grid">

                        <div class="verify-avatar-upload">
                            <div class="verify-avatar-preview" id="avatarPreview">
                                <i class="bi bi-person-fill"></i>
                            </div>

                            <label class="verify-upload-button">
                                <input type="file" name="foto_profil" id="fotoProfilInput" accept="image/*">
                                <i class="bi bi-camera-fill"></i>
                                <span>Upload Foto</span>
                            </label>
                        </div>

                        <div class="verify-fields">
                            <label class="verify-field">
                                <span>Jenis Penggalang Dana</span>

                                <select name="jenis_penggalang">
                                    <option value="individu" selected>Individu</option>
                                    <option value="organisasi">Organisasi</option>
                                </select>
                            </label>

                            <label class="verify-field">
                                <span>Nama Lengkap</span>

                                <input
                                    type="text"
                                    name="nama_penggalang"
                                    value="{{ old('nama_penggalang', $user->name ?? '') }}"
                                    placeholder="Masukkan nama lengkap">
                            </label>
                        </div>

                    </div>

                    <label class="verify-field">
                        <span>Alamat Domisili</span>

                        <textarea
                            name="alamat"
                            rows="3"
                            placeholder="Masukkan alamat domisili lengkap">{{ old('alamat') }}</textarea>
                    </label>
                </section>

                {{-- INFORMASI --}}
                <section class="verify-card">
                    <div class="verify-card-header">
                        <h2>Informasi Penggalang Dana</h2>
                        <p>Ceritakan profil singkat dan alasan kamu menjadi penggalang dana.</p>
                    </div>

                    <label class="verify-field">
                        <span>Cerita / Profil Singkat Penggalang Dana</span>

                        <textarea
                            name="deskripsi"
                            rows="6"
                            placeholder="Ceritakan siapa kamu, alasan menjadi penggalang dana, dan bentuk tanggung jawab kamu terhadap campaign yang akan dibuat.">{{ old('deskripsi') }}</textarea>
                    </label>
                </section>

                {{-- LEGALITAS --}}
                <section class="verify-card">
                    <div class="verify-card-header">
                        <h2>Dokumen Legalitas</h2>
                        <p>Unggah atau lampirkan dokumen identitas untuk memperkuat kredibilitas akun.</p>
                    </div>

                    <div class="verify-grid-2">
                        <label class="verify-field">
                            <span>Nama Dokumen <b>*</b></span>

                            <input
                                type="text"
                                name="nama_legalitas"
                                value="{{ old('nama_legalitas') }}"
                                placeholder="Contoh: KTP">
                        </label>

                        <label class="verify-field">
                            <span>Link Dokumen <b>*</b></span>

                            <input
                                type="url"
                                name="link_legalitas"
                                value="{{ old('link_legalitas') }}"
                                placeholder="Masukkan link Drive dokumen">
                        </label>
                    </div>
                </section>

                {{-- KONTAK --}}
                <section class="verify-card">
                    <div class="verify-card-header">
                        <h2>Kontak & Sosial Media</h2>
                        <p>Informasi kontak membantu membangun transparansi dan kepercayaan donatur.</p>
                    </div>

                    <div class="verify-grid-2">
                        <label class="verify-field">
                            <span>Email <b>*</b></span>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', $user->email ?? '') }}"
                                placeholder="Masukkan email">
                        </label>

                        <label class="verify-field">
                            <span>Nomor Telepon <b>*</b></span>

                            <input
                                type="text"
                                name="no_telepon"
                                value="{{ old('no_telepon') }}"
                                placeholder="Masukkan nomor telepon">
                        </label>
                    </div>

                    <div class="verify-subtitle">
                        <h3>Sosial Media</h3>
                        <span>Opsional</span>
                    </div>

                    <div class="verify-grid-2">
                        <label class="verify-field">
                            <span>Instagram</span>
                            <input type="text" name="instagram" value="{{ old('instagram') }}" placeholder="Username Instagram">
                        </label>

                        <label class="verify-field">
                            <span>Facebook</span>
                            <input type="text" name="facebook" value="{{ old('facebook') }}" placeholder="Username Facebook">
                        </label>

                        <label class="verify-field">
                            <span>Youtube</span>
                            <input type="text" name="youtube" value="{{ old('youtube') }}" placeholder="Username Youtube">
                        </label>

                        <label class="verify-field">
                            <span>Tiktok</span>
                            <input type="text" name="tiktok" value="{{ old('tiktok') }}" placeholder="Username Tiktok">
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
<script src="{{ asset('js/penggalang-individu.js') }}"></script>

</body>
</html>