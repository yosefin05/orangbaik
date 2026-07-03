<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Penggalang Dana Individu - OrangBaik.id</title>

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

        {{-- HERO --}}
        <section class="verify-hero">
            <div class="container verify-hero-inner">

                <div class="verify-heading">
                    <span class="verify-eyebrow">Edit Profil</span>

                    <h1>Edit Penggalang Dana Individu</h1>

                    <p>
                        Perbarui informasi penggalang dana kamu.
                    </p>
                </div>

            </div>
        </section>

        {{-- FORM --}}
        <section class="verify-section">
            <div class="container">

                <form action="{{ route('penggalang_dana.update', $penggalang) }}" method="POST"
                    enctype="multipart/form-data" class="verify-form">

                    @csrf
                    @method('PATCH')

                    {{-- PROFIL --}}
                    <section class="verify-card">

                        <div class="verify-card-header">
                            <h2>Profil Penggalang Dana</h2>
                            <p>Data dasar penggalang dana.</p>
                        </div>

                        <div class="verify-profile-grid">

                            {{-- AVATAR --}}
                            <div class="verify-avatar-upload">

                                <div class="verify-avatar-preview" id="avatarPreview">

                                    @if($penggalang->foto_profil)
                                        <img src="{{ asset('storage/' . $penggalang->foto_profil) }}">
                                    @else
                                        <i class="bi bi-person-fill"></i>
                                    @endif

                                </div>

                                <label class="verify-upload-button">
                                    <input type="file" name="foto_profil" id="fotoProfilInput" accept="image/*">

                                    <i class="bi bi-camera-fill"></i>
                                    <span>Ganti Foto</span>
                                </label>

                            </div>

                            {{-- FIELD --}}
                            <div class="verify-fields">

                                <label class="verify-field">
                                    <span>Jenis Penggalang</span>
                                    <input type="text" value="Individu" readonly>
                                </label>

                                <label class="verify-field">
                                    <span>Nama Lengkap</span>
                                    <input type="text" name="nama_penggalang"
                                        value="{{ old('nama_penggalang', $penggalang->nama_penggalang) }}">
                                </label>

                            </div>

                        </div>

                        <label class="verify-field">
                            <span>Alamat Domisili</span>
                            <textarea name="alamat" rows="3">{{ old('alamat', $penggalang->alamat) }}</textarea>
                        </label>

                    </section>

                    {{-- INFORMASI --}}
                    <section class="verify-card">

                        <div class="verify-card-header">
                            <h2>Informasi Penggalang</h2>
                        </div>

                        <label class="verify-field">
                            <span>Deskripsi</span>
                            <textarea name="deskripsi"
                                rows="6">{{ old('deskripsi', $penggalang->deskripsi) }}</textarea>
                        </label>

                        <div class="verify-grid-2">

                            <label class="verify-field">
                                <span>Visi</span>
                                <input type="text" name="visi" value="{{ old('visi', $penggalang->visi) }}">
                            </label>

                            <label class="verify-field">
                                <span>Misi</span>
                                <input type="text" name="misi" value="{{ old('misi', $penggalang->misi) }}">
                            </label>

                        </div>

                    </section>

                    {{-- DOKUMEN --}}
                    <section class="verify-card">

                        <div class="verify-card-header">
                            <h2>Dokumen Identitas</h2>
                        </div>

                        <div class="verify-grid-2">

                            @php
                                $dokumen = $penggalang->penggalangDanaDokumen[0] ?? null;
                            @endphp

                            <label class="verify-field">
                                <span>Nama Dokumen</span>
                                <input type="text" name="nama_dokumen[]"
                                    value="{{ old('nama_dokumen.0', $dokumen->nama_dokumen ?? '') }}">
                            </label>

                            <label class="verify-field">
                                <span>Link Dokumen</span>
                                <input type="url" name="file_dokumen[]"
                                    value="{{ old('file_dokumen.0', $dokumen->file_dokumen ?? '') }}">
                            </label>

                        </div>

                    </section>

                    {{-- KONTAK --}}
                    <section class="verify-card">

                        <div class="verify-card-header">
                            <h2>Kontak</h2>
                        </div>

                        <div class="verify-grid-2">

                            <label class="verify-field">
                                <span>Email</span>
                                <input type="email" name="email" value="{{ old('email', $penggalang->email) }}">
                            </label>

                            <label class="verify-field">
                                <span>Nomor Telepon</span>
                                <input type="text" name="no_telepon"
                                    value="{{ old('no_telepon', $penggalang->no_telepon) }}">
                            </label>

                        </div>

                    </section>

                    {{-- SOSIAL MEDIA --}}
                    <section class="verify-card">

                        <div class="verify-card-header">
                            <h2>Sosial Media</h2>
                        </div>

                        <div class="verify-grid-2">
                            <label class="verify-field">
                                <input type="text" name="instagram"
                                    value="{{ old('instagram', $penggalang->instagram) }}" placeholder="Instagram">
                            </label>
                            <label class="verify-field">
                                <input type="text" name="facebook" value="{{ old('facebook', $penggalang->facebook) }}"
                                    placeholder="Facebook">
                            </label>
                            <label class="verify-field">
                                <input type="text" name="youtube" value="{{ old('youtube', $penggalang->youtube) }}"
                                    placeholder="Youtube">
                            </label>
                            <label class="verify-field">
                                <input type="text" name="tiktok" value="{{ old('tiktok', $penggalang->tiktok) }}"
                                    placeholder="TikTok">
                            </label>
                        </div>

                    </section>

                    {{-- ACTION --}}
                    <div class="verify-actions">

                        <a href="{{ route('profile.user') }}" class="verify-cancel-button">
                            Batal
                        </a>

                        <button type="submit" class="verify-submit-button">
                            Simpan Perubahan
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
            <img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
        `;
            };

            reader.readAsDataURL(file);
        });
    </script>

</body>

</html>