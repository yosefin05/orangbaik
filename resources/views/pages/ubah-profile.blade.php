<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Profile - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ubah-profile.css') }}">

    {{-- ICON LIBRARY --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

@php
    $userName = auth()->user()->name ?? 'Amib Ija A H';
    $userEmail = auth()->user()->email ?? 'ijaamib@gmail.com';
@endphp

<main class="edit-profile-page">
    <div class="edit-profile-container">

        <button class="edit-back" type="button" onclick="history.back()">
            <i class="bi bi-chevron-left"></i>
            <span>Kembali</span>
        </button>

        <section class="profile-heading">
            <h1>Profil</h1>
            <p>
                Informasi profil dan pengaturan yang Anda kelola pada halaman ini akan digunakan
                secara terintegrasi di seluruh layanan dan fitur yang tersedia di
                <span>orangbaik.id</span>
            </p>
        </section>

        <form action="#" method="POST" enctype="multipart/form-data" class="edit-profile-form">
            @csrf

            {{-- INFO PROFIL --}}
            <section class="form-section">
                <h2>Info Profil</h2>

                <div class="profile-info-grid">
                    <div class="avatar-upload">
                        <div class="avatar-preview" id="avatarPreview">
                            <i class="bi bi-person-fill avatar-default-icon"></i>
                        </div>

                        <label class="camera-button">
                            <input type="file" name="foto_profile" id="avatarInput" accept="image/*">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                    </div>

                    <div class="profile-fields">
                        <label class="input-card">
                            <span>Nama Lengkap</span>

                            <input 
                                type="text" 
                                name="nama_lengkap" 
                                value="{{ $userName }}"
                                placeholder="Masukkan Nama Lengkap"
                            >

                            <span class="field-icon">
                                <i class="bi bi-pencil-fill"></i>
                            </span>
                        </label>

                        <label class="input-card select-card">
                            <span>Jenis Kelamin</span>

                            <select name="jenis_kelamin">
                                <option value="">Pilih Gender Anda</option>
                                <option value="laki-laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>

                            <span class="field-icon">
                                <i class="bi bi-check-lg"></i>
                            </span>
                        </label>
                    </div>
                </div>
            </section>

            {{-- NOMOR HP DAN EMAIL --}}
            <section class="form-section">
                <h2>Nomor HP dan Email</h2>

                <div class="contact-block">
                    <h3>Nomor HP</h3>

                    <div class="verified-card">
                        <div class="verified-main">
                            <input 
                                type="text" 
                                name="nomor_hp" 
                                placeholder="Masukkan Nomor Telepon"
                            >

                            <span class="verified-badge">
                                <b>
                                    <i class="bi bi-patch-check-fill"></i>
                                </b>
                                Sudah diverifikasi
                            </span>
                        </div>

                        <button type="button">Ubah</button>
                    </div>

                    <p class="helper-text">
                        Nomor HP yang Anda perbarui akan digunakan sebagai informasi kontak pada seluruh
                        bukti pembayaran elektronik yang diterbitkan setelah perubahan berhasil disimpan.
                    </p>
                </div>

                <div class="contact-block">
                    <h3>Email</h3>

                    <div class="verified-card">
                        <div class="verified-main">
                            <input 
                                type="email" 
                                name="email" 
                                value="{{ $userEmail }}"
                                placeholder="Masukkan Email"
                            >

                            <span class="verified-badge">
                                <b>
                                    <i class="bi bi-patch-check-fill"></i>
                                </b>
                                Sudah diverifikasi
                            </span>
                        </div>

                        <button type="button">Ubah</button>
                    </div>
                </div>
            </section>

            {{-- KEAMANAN AKUN --}}
            <section class="form-section">
                <h2>Keamanan Akun</h2>

                <div class="security-fields">
                    <label class="input-card">
                        <span>Ganti Password</span>

                        <input 
                            type="password" 
                            name="password_baru"
                            placeholder="Masukkan Password Baru"
                        >

                        <span class="field-icon">
                            <i class="bi bi-pencil-fill"></i>
                        </span>
                    </label>

                    <label class="input-card">
                        <span>Konfirmasi Password Baru</span>

                        <input 
                            type="password" 
                            name="konfirmasi_password_baru"
                            placeholder="Masukkan Konfirmasi Password Baru"
                        >

                        <span class="field-icon">
                            <i class="bi bi-pencil-fill"></i>
                        </span>
                    </label>
                </div>

                <p class="helper-text">
                    Dengan memperbarui kata sandi secara berkala, Anda dapat menjaga keamanan akun
                    dengan lebih baik serta memastikan seluruh data dan aktivitas Anda di orangbaik.id
                    tetap terlindungi.
                </p>
            </section>

        </form>

    </div>
</main>

@include('components.footer')

<script src="{{ asset('js/ubah-profile.js') }}"></script>

</body>
</html>