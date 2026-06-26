<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Penggalang Dana - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penggalangdana-organisasi.css') }}">
</head>

<body>
    <main class="verify-page">
        <form class="verify-form" action="{{ route('penggalang_dana.organisasi.store') }}" method="POST"
            enctype="multipart/form-data" novalidate>
            @csrf
            <div class="verify-container">

                <a href="{{ route('home') }}" class="back-button">
                    <span class="back-icon">‹</span>
                    <span>Kembali</span>
                </a>

                <section class="verify-heading">
                    <h1>Verifikasi Akun Penggalang Dana</h1>
                    <p>
                        Lengkapi informasi untuk proses pendaftaran penggalang dana.
                        Data yang diberikan akan digunakan untuk verifikasi akun dan
                        pengelolaan campaign di orangbaik.id
                    </p>
                </section>

                <section class="cover-card">
                    <img id="thumbnail-preview" src="" alt="Banner Penggalang Dana" style="
                        width:100%;
                        height:100%;
                        object-fit:cover;
                        display:none;
                    ">
                    <div id="banner-placeholder">
                        Upload Banner Organisasi
                    </div>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*" hidden>
                    <button type="button" class="camera-button" onclick="document.getElementById('thumbnail').click()">
                        ⌾
                    </button>
                </section>

                <script>
                    document.getElementById('thumbnail').addEventListener('change', function () {
                        const file = this.files[0];
                        if (!file) return;
                        document.getElementById('thumbnail-preview').src = URL.createObjectURL(file);
                        document.getElementById('thumbnail-preview').style.display = 'block';
                        document.getElementById('banner-placeholder').style.display = 'none';
                    });
                </script>

                {{-- PROFIL --}}
                <section class="form-section">
                    <h2>Profil Penggalang Dana</h2>

                    <div class="profile-grid">
                        <div class="avatar-box">
                            <img id="foto-preview" src="" alt="Foto Profil" class="avatar-preview"
                                style="display:none;">
                            <div id="avatar-placeholder" class="avatar-placeholder">
                                <div class="avatar-head"></div>
                                <div class="avatar-body"></div>
                            </div>
                            <input type="file" id="foto_profil" name="foto_profil" accept="image/*" hidden>
                            <button type="button" class="avatar-camera"
                                onclick="document.getElementById('foto_profil').click()">
                                ⌾
                            </button>
                        </div>

                        <div class="profile-fields">
                            <div class="field">
                                <label>Jenis Penggalang Dana</label>
                                <input type="text" value="Organisasi" readonly>
                                <input type="hidden" name="jenis_penggalang" value="organisasi">
                                <span class="field-icon check">✓</span>
                            </div>

                            <div class="field">
                                <label>Nama Organisasi atau Individu</label>
                                <input type="text" name="nama_penggalang" value="{{ old('nama_penggalang') }}"
                                    placeholder="Masukkan Nama Organisasi">
                            </div>
                        </div>
                    </div>

                    <script>
                        document.getElementById('foto_profil').addEventListener('change', function () {
                            const file = this.files[0];
                            if (!file) return;
                            document.getElementById('foto-preview').src = URL.createObjectURL(file);
                            document.getElementById('foto-preview').style.display = 'block';
                            document.getElementById('avatar-placeholder').style.display = 'none';
                        });
                    </script>

                    <div class="field">
                        <label>Tahun Berdiri</label>
                        <select name="tahun_berdiri" required>
                            <option value="">Pilih Tahun Berdiri</option>
                            @for($tahun = date('Y'); $tahun >= 1990; $tahun--)
                                <option value="{{ $tahun }}" {{ old('tahun_berdiri') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endfor
                        </select>
                        <span class="field-icon calendar">▣</span>
                    </div>

                    <div class="field">
                        <label>Alamat Kantor</label>
                        <textarea name="alamat" placeholder="Masukkan Alamat Kantor">{{ old('alamat') }}</textarea>
                        <span class="field-icon location">⌖</span>
                    </div>
                </section>

                {{-- INFORMASI --}}
                <section class="form-section">
                    <h2>Informasi Penggalang Dana</h2>

                    <div class="field textarea-field">
                        <label>Deskripsi Penggalang Dana</label>
                        <textarea name="deskripsi" placeholder="Masukkan Deskripsi">{{ old('deskripsi') }}</textarea>
                        <span class="field-icon edit">✎</span>
                    </div>

                    <div class="field">
                        <label>Visi</label>
                        <input type="text" name="visi" value="{{ old('visi') }}">
                    </div>

                    <div class="field">
                        <label>Misi</label>
                        <input type="text" name="misi" value="{{ old('misi') }}">
                        <span class="field-icon edit">✎</span>
                    </div>
                </section>

                {{-- DOKUMEN --}}
                <section class="form-section">
                    <h2>Lengkapi Dokumen Legalitas</h2>
                    <p class="section-desc">
                        Unggah dokumen legalitas untuk memperkuat kredibilitas akun penggalang dana Anda
                        serta meningkatkan kepercayaan donatur terhadap campaign yang Anda jalankan.
                    </p>

                    <div class="document-group">
                        <h3>Dokumen Legalitas 1<span>*</span></h3>
                        <div class="document-grid">
                            <div class="field">
                                <label>Nama Legalitas</label>
                                <input type="text" name="nama_dokumen[]"
                                    placeholder="Masukkan Nama Legalitas. Contoh: BAZNAS">
                            </div>

                            <div class="field">
                                <label>Link Legalitas</label>
                                <input type="url" name="file_dokumen[]"
                                    placeholder="Masukkan Link Legalitas. Contoh: Google Drive">
                                <span class="field-icon edit">✎</span>
                            </div>
                        </div>
                    </div>

                    <div class="document-group">
                        <h3>Dokumen Legalitas 2<span>*</span></h3>
                        <div class="document-grid">
                            <div class="field">
                                <label>Nama Legalitas</label>
                                <input type="text" name="nama_dokumen[]"
                                    placeholder="Masukkan Nama Legalitas. Contoh: BAZNAS">
                                <span class="field-icon edit">✎</span>
                            </div>

                            <div class="field">
                                <label>Link Legalitas</label>
                                <input type="url" name="file_dokumen[]"
                                    placeholder="Masukkan Link Legalitas. Contoh: Google Drive">
                                <span class="field-icon edit">✎</span>
                            </div>
                        </div>
                    </div>

                    <div class="document-group">
                        <h3>Dokumen Legalitas 3 <em>(Opsional)</em></h3>
                        <div class="document-grid">
                            <div class="field">
                                <label>Nama Legalitas</label>
                                <input type="text" name="nama_dokumen[]"
                                    placeholder="Masukkan Nama Legalitas. Contoh: BAZNAS">
                                <span class="field-icon edit">✎</span>
                            </div>

                            <div class="field">
                                <label>Link Legalitas</label>
                                <input type="url" name="file_dokumen[]"
                                    placeholder="Masukkan Link Legalitas. Contoh: Google Drive">
                                <span class="field-icon edit">✎</span>
                            </div>
                        </div>
                    </div>

                    <div class="document-group">
                        <h3>Dokumen Legalitas 4 <em>(Opsional)</em></h3>
                        <div class="document-grid">
                            <div class="field">
                                <label>Nama Legalitas</label>
                                <input type="text" name="nama_dokumen[]"
                                    placeholder="Masukkan Nama Legalitas. Contoh: BAZNAS">
                                <span class="field-icon edit">✎</span>
                            </div>

                            <div class="field">
                                <label>Link Legalitas</label>
                                <input type="url" name="file_dokumen[]"
                                    placeholder="Masukkan Link Legalitas. Contoh: Google Drive">
                                <span class="field-icon edit">✎</span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- KONTAK --}}
                <section class="form-section">
                    <h2>Kontak & Sosial Media</h2>
                    <p class="section-desc">
                        Informasi kontak dan media sosial yang lengkap membantu membangun transparansi serta
                        meningkatkan kredibilitas penggalangan dana Anda.
                    </p>

                    <div class="contact-group">
                        <h3>Kontak<span>*</span></h3>

                        <div class="field">
                            <label>Masukkan Email</label>
                            <input type="email" name="email" value="{{ old('email') }}">
                            <span class="field-icon edit">✎</span>
                        </div>

                        <div class="field">
                            <label>Masukkan Nomor Hotline</label>
                            <input type="text" name="no_telepon" value="{{ old('no_telepon') }}">
                            <span class="field-icon edit">✎</span>
                        </div>
                    </div>

                    <div class="contact-group">
                        <h3>Sosial Media <em>(Opsional)</em></h3>

                        <div class="field">
                            <label>Masukkan Username Instagram</label>
                            <input type="text" name="instagram" value="{{ old('instagram') }}"
                                placeholder="Masukkan Username Instagram Anda">
                            <span class="field-icon edit">✎</span>
                        </div>

                        <div class="field">
                            <label>Masukkan Username Facebook</label>
                            <input type="text" name="facebook" value="{{ old('facebook') }}"
                                placeholder="Masukkan Username Facebook Anda">
                            <span class="field-icon edit">✎</span>
                        </div>

                        <div class="field">
                            <label>Masukkan Username Youtube</label>
                            <input type="text" name="youtube" value="{{ old('youtube') }}"
                                placeholder="Masukkan Username Youtube Anda">
                            <span class="field-icon edit">✎</span>
                        </div>

                        <div class="field">
                            <label>Masukkan Username Tiktok</label>
                            <input type="text" name="tiktok" value="{{ old('tiktok') }}"
                                placeholder="Masukkan Username Tiktok Anda">
                            <span class="field-icon edit">✎</span>
                        </div>
                    </div>
                </section>

                <div class="form-actions">
                    <button type="submit" class="submit-button">
                        Kirim Verifikasi
                    </button>
                </div>
            </div>
        </form>
    </main>
    @include('components.footer')

</body>

</html>