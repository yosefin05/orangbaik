<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Penggalang Dana Individu - OrangBaik.id</title>

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
                <div class="verify-heading">
                    <span class="verify-eyebrow">Verifikasi Akun</span>

                    <h1>Verifikasi Penggalang Dana Individu</h1>

                    <p>
                        Lengkapi informasi berikut untuk proses pendaftaran penggalang dana.
                        Data yang diberikan akan digunakan untuk verifikasi akun dan pengelolaan campaign.
                    </p>
                </div>

            </div>
        </section>

        <section class="verify-section">
            <div class="container">
                <x-alert-error />
                <form action="{{ route('penggalang_dana.individu.store') }}" method="POST" enctype="multipart/form-data"
                    class="verify-form">
                    @csrf
                    {{-- PROFIL --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Profil Penggalang Dana</h2>
                            <p>
                                Masukkan data dasar penggalang dana yang akan ditampilkan pada profil.
                            </p>
                        </div>

                        <div class="verify-profile-grid">

                            <div class="verify-avatar-upload">

                                <div class="verify-avatar-preview" id="avatarPreview">

                                    @if(old('foto_profil'))
                                        <img src="{{ old('foto_profil') }}">
                                    @else
                                        <i class="bi bi-person-fill"></i>
                                    @endif

                                </div>

                                <label class="verify-upload-button">

                                    <input type="file" name="foto_profil" id="fotoProfilInput" accept="image/*"
                                        required>

                                    <i class="bi bi-camera-fill"></i>
                                    <span>Upload Foto</span>

                                </label>

                                @error('foto_profil')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </div>

                            <div class="verify-fields">

                                <label class="verify-field">

                                    <span>Jenis Penggalang Dana</span>

                                    <input type="text" value="Individu" readonly>

                                    <input type="hidden" name="jenis_penggalang" value="individu">

                                </label>

                                <label class="verify-field">

                                    <span>Nama Lengkap</span>

                                    <input type="text" name="nama_penggalang"
                                        value="{{ old('nama_penggalang', $user->name) }}"
                                        placeholder="Masukkan Nama Lengkap">

                                    @error('nama_penggalang')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                </label>

                            </div>

                        </div>

                        <label class="verify-field">

                            <span>Alamat Domisili</span>

                            <textarea name="alamat" rows="3"
                                placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>

                            @error('alamat')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </label>

                    </section>
                    {{-- INFORMASI PENGGALANG --}}
                    <section class="verify-card">

                        <div class="verify-card-header">
                            <h2>Informasi Penggalang Dana</h2>
                            <p>
                                Ceritakan profil singkat dan tujuan kamu menjadi penggalang dana.
                            </p>
                        </div>

                        <label class="verify-field">

                            <span>Deskripsi</span>

                            <textarea name="deskripsi" rows="6"
                                placeholder="Ceritakan siapa kamu dan tujuanmu">{{ old('deskripsi') }}</textarea>

                            @error('deskripsi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </label>

                        <div class="verify-grid-2">

                            <label class="verify-field">

                                <span>Visi</span>

                                <input type="text" name="visi" value="{{ old('visi') }}" placeholder="Visi kamu">

                                @error('visi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </label>

                            <label class="verify-field">

                                <span>Misi</span>

                                <input type="text" name="misi" value="{{ old('misi') }}" placeholder="Misi kamu">

                                @error('misi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </label>

                        </div>

                    </section>
                    {{-- DOKUMEN --}}
                    <section class="verify-card">

                        <div class="verify-card-header">
                            <h2>Dokumen Identitas</h2>
                            <p>
                                Unggah dokumen untuk verifikasi identitas.
                            </p>
                        </div>

                        <div class="verify-grid-2">

                            <label class="verify-field">

                                <span>Nama Dokumen <b>*</b></span>

                                <input type="text" name="nama_dokumen[]" placeholder="Contoh: KTP" required>

                                @error('nama_dokumen.0')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </label>

                            <label class="verify-field">

                                <span>Link / File Dokumen <b>*</b></span>

                                <input type="url" name="file_dokumen[]" placeholder="Link Google Drive atau URL"
                                    required>

                                @error('file_dokumen.0')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </label>

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

                                <input type="email" name="email" value="{{ old('email') }}"
                                    placeholder="Masukkan Email Organisasi">

                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                            </label>

                            <label class="verify-field">

                                <span>Nomor Hotline <b>*</b></span>

                                <input type="text" name="no_telepon" value="{{ old('no_telepon') }}"
                                    placeholder="Masukkan Nomor Hotline">

                                @error('no_telepon')
                                    <small class="text-danger">{{ $message }}</small>
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
    <script>

        document.getElementById('fotoProfilInput').addEventListener('change', function () {

            const file = this.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function (e) {

                document.getElementById('avatarPreview').innerHTML = `
            <img
                src="${e.target.result}"
                style="
                    width:100%;
                    height:100%;
                    object-fit:cover;
                    border-radius:50%;
                ">
        `;

            };

            reader.readAsDataURL(file);

        });

    </script>

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/penggalang-individu.js') }}"></script>

</body>

</html>