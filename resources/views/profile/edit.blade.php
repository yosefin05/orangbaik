<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Profile - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ubah-profile.css') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @include('components.header')

    @php
        $user = auth()->user();
    @endphp

    <main class="edit-profile-page">

        <div class="edit-profile-container">
            <section class="profile-heading">

                <h1>Profil</h1>

                <p>
                    Informasi profil dan pengaturan yang Anda kelola pada halaman ini akan digunakan
                    secara terintegrasi di seluruh layanan dan fitur yang tersedia di
                    <span>orangbaik.id</span>
                </p>

            </section>

            @if(session('success'))
                <div class="success-alert">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                class="edit-profile-form">

                @csrf
                @method('PATCH')

                {{-- ========================= --}}
                {{-- INFO PROFIL --}}
                {{-- ========================= --}}

                <section class="form-section">

                    <h2>Info Profil</h2>

                    <div class="profile-info-grid">

                        <div class="avatar-upload">

                            <div class="avatar-preview" id="avatarPreview">

                                @if($user->foto_profil)

                                    <img id="previewImage" src="{{ asset('storage/' . $user->foto_profil) }}"
                                        alt="Foto Profil">

                                @else

                                    <i class="bi bi-person-fill avatar-default-icon"></i>

                                @endif

                            </div>

                            <label class="camera-button">

                                <input type="file" name="foto_profil" id="avatarInput" accept="image/*">

                                <i class="bi bi-camera-fill"></i>

                            </label>

                            @error('foto_profil')
                                <small class="error-text">
                                    {{ $message }}
                                </small>
                            @enderror

                        </div>

                        <div class="profile-fields">

                            <label class="input-card">

                                <span>Nama Lengkap</span>

                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                    placeholder="Masukkan Nama Lengkap">

                                <span class="field-icon">
                                    <i class="bi bi-pencil-fill"></i>
                                </span>

                                @error('name')
                                    <small class="error-text">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </label>

                            <label class="input-card">

                                <span>Jenis Kelamin</span>

                                <select name="jenis_kelamin">

                                    <option value="">
                                        Pilih Gender Anda
                                    </option>

                                    <option value="L" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'L')>

                                        Laki-laki
                                    </option>

                                    <option value="P" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'P')>

                                        Perempuan

                                    </option>

                                </select>

                                <span class="field-icon">
                                    <i class="bi bi-check-lg"></i>
                                </span>

                                @error('jenis_kelamin')
                                    <small class="error-text">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </label>

                        </div>

                    </div>

                </section>
                {{-- ========================= --}}
                {{-- NOMOR HP DAN EMAIL --}}
                {{-- ========================= --}}

                <section class="form-section">

                    <h2>Nomor HP dan Email</h2>

                    <div class="contact-block">

                        <h3>Nomor HP</h3>

                        <div class="verified-card">

                            <div class="verified-main">

                                <input type="text" name="nomor" value="{{ old('nomor', $user->nomor) }}"
                                    placeholder="Masukkan Nomor Telepon">

                                @if($user->nomor)
                                    <span class="verified-badge">
                                        <b><i class="bi bi-check-circle-fill"></i></b>
                                        Nomor terisi
                                    </span>
                                @endif

                            </div>

                        </div>

                        @error('nomor')
                            <small class="error-text">
                                {{ $message }}
                            </small>
                        @enderror

                        <p class="helper-text">
                            Nomor HP yang Anda perbarui akan digunakan sebagai informasi
                            kontak pada seluruh aktivitas akun Anda.
                        </p>

                    </div>

                    <div class="contact-block">

                        <h3>Email</h3>

                        <div class="verified-card">

                            <div class="verified-main">

                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                    placeholder="Masukkan Email">

                                @if($user->email)
                                    <span class="verified-badge {{ $user->hasVerifiedEmail() ? '' : 'unverified-badge' }}">
                                        <b><i
                                                class="bi {{ $user->hasVerifiedEmail() ? 'bi-patch-check-fill' : 'bi-exclamation-circle-fill' }}"></i></b>
                                        {{ $user->hasVerifiedEmail() ? 'Sudah diverifikasi' : 'Belum diverifikasi' }}
                                    </span>
                                @endif

                            </div>

                        </div>

                        @error('email')
                            <small class="error-text">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                </section>


                {{-- ========================= --}}
                {{-- KEAMANAN AKUN --}}
                {{-- ========================= --}}

                <section class="form-section">

                    <h2>Keamanan Akun</h2>

                    <div class="security-fields">

                        <label class="input-card">

                            <span>Password Baru</span>

                            <input type="password" name="password"
                                placeholder="Kosongkan jika tidak ingin mengganti password">

                            <span class="field-icon">
                                <i class="bi bi-pencil-fill"></i>
                            </span>

                            @error('password')
                                <small class="error-text">
                                    {{ $message }}
                                </small>
                            @enderror

                        </label>

                        <label class="input-card">

                            <span>Konfirmasi Password Baru</span>

                            <input type="password" name="password_confirmation" placeholder="Ulangi Password Baru">

                            <span class="field-icon">
                                <i class="bi bi-pencil-fill"></i>
                            </span>

                        </label>

                    </div>

                    <p class="helper-text">
                        Dengan memperbarui kata sandi secara berkala,
                        Anda dapat menjaga keamanan akun dengan lebih baik
                        serta memastikan seluruh data tetap terlindungi.
                    </p>

                </section>
                {{-- ========================= --}}
                {{-- TOMBOL SIMPAN --}}
                {{-- ========================= --}}

                <div class="form-action">
                    <button type="submit" class="save-button">
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>
    </main>
    @include('components.footer')

    <script>

        const avatarInput = document.getElementById('avatarInput');
        const avatarPreview = document.getElementById('avatarPreview');

        avatarInput.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function (e) {

                avatarPreview.innerHTML = `
                <img
                    id="previewImage"
                    src="${e.target.result}"
                    alt="Preview Foto Profil">
            `;

            }

            reader.readAsDataURL(file);

        });

    </script>

</body>

</html>