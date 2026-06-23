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
        <div class="verify-container">

            <button type="button" class="back-button" onclick="history.back()">
                <span class="back-icon">‹</span>
                <span>Kembali</span>
            </button>

            <section class="verify-heading">
                <h1>Verifikasi Akun Penggalang Dana</h1>
                <p>
                    Lengkapi informasi untuk proses pendaftaran penggalang dana.
                    Data yang diberikan akan digunakan untuk verifikasi akun dan
                    pengelolaan campaign di orangbaik.id
                </p>
            </section>

            <section class="cover-card">
                <img src="{{ asset('assets/banner-dq.png') }}" alt="Banner Penggalang Dana">
                <button type="button" class="camera-button" aria-label="Ubah banner">⌾</button>
            </section>

            <form class="verify-form">

                {{-- PROFIL --}}
                <section class="form-section">
                    <h2>Profil Penggalang Dana</h2>

                    <div class="profile-grid">
                        <div class="avatar-box">
                            <div class="avatar-placeholder">
                                <div class="avatar-head"></div>
                                <div class="avatar-body"></div>
                            </div>

                            <button type="button" class="avatar-camera" aria-label="Ubah foto profil">⌾</button>
                        </div>

                        <div class="profile-fields">
                            <div class="field">
                                <label>Jenis Penggalang Dana</label>
                                <input type="text" value="Organisasi" readonly>
                                <span class="field-icon check">✓</span>
                            </div>

                            <div class="field">
                                <label>Nama Organisasi atau Individu</label>
                                <input type="text" value="Dompet Al-Qur’an Indonesia" readonly>
                                <span class="field-icon edit">✎</span>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label>Tahun Berdiri</label>
                        <input type="text" value="2011" readonly>
                        <span class="field-icon calendar">▣</span>
                    </div>

                    <div class="field">
                        <label>Alamat Kantor</label>
                        <textarea readonly>Ruko Citra City Blok R No. 28, Sarirogo, Sidoarjo, Jawa Timur, 61234 - Sidoarjo, Kab. Sidoarjo, Jawa Timur</textarea>
                        <span class="field-icon location">⌖</span>
                    </div>
                </section>

                {{-- INFORMASI --}}
                <section class="form-section">
                    <h2>Informasi Penggalang Dana</h2>

                    <div class="field textarea-field">
                        <label>Deskripsi Penggalang Dana</label>
                        <textarea readonly>Dompet Al-Qur’an Indonesia (DQ) adalah Lembaga Amil Zakat dan Nazhir Wakaf resmi yang berada di bawah naungan Kementerian Agama RI dan Badan Wakaf Indonesia (BWI). DQ telah teraudit dengan predikat Wajar Tanpa Pengecualian (WTP) sebagai bentuk komitmen terhadap transparansi dan akuntabilitas.</textarea>
                        <span class="field-icon edit">✎</span>
                    </div>

                    <div class="field">
                        <label>Visi</label>
                        <input type="text" placeholder="Masukkan Visi Anda">
                        <span class="field-icon edit">✎</span>
                    </div>

                    <div class="field">
                        <label>Misi</label>
                        <input type="text" placeholder="Masukkan Misi Anda">
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
                                <input type="text" placeholder="Masukkan Nama Legalitas (cth:Baznas)">
                                <span class="field-icon edit">✎</span>
                            </div>

                            <div class="field">
                                <label>Link Legalitas</label>
                                <input type="text" placeholder="Masukkan Link Legalitas (cth: Link Drive)">
                                <span class="field-icon edit">✎</span>
                            </div>
                        </div>
                    </div>

                    <div class="document-group">
                        <h3>Dokumen Legalitas 2<span>*</span></h3>
                        <div class="document-grid">
                            <div class="field">
                                <label>Nama Legalitas</label>
                                <input type="text" placeholder="Masukkan Nama Legalitas (cth:Baznas)">
                                <span class="field-icon edit">✎</span>
                            </div>

                            <div class="field">
                                <label>Link Legalitas</label>
                                <input type="text" placeholder="Masukkan Link Legalitas (cth: Link Drive)">
                                <span class="field-icon edit">✎</span>
                            </div>
                        </div>
                    </div>

                    <div class="document-group">
                        <h3>Dokumen Legalitas 3 <em>(Opsional)</em></h3>
                        <div class="document-grid">
                            <div class="field">
                                <label>Nama Legalitas</label>
                                <input type="text" placeholder="Masukkan Nama Legalitas (cth:Baznas)">
                                <span class="field-icon edit">✎</span>
                            </div>

                            <div class="field">
                                <label>Link Legalitas</label>
                                <input type="text" placeholder="Masukkan Link Legalitas (cth: Link Drive)">
                                <span class="field-icon edit">✎</span>
                            </div>
                        </div>
                    </div>

                    <div class="document-group">
                        <h3>Dokumen Legalitas 4 <em>(Opsional)</em></h3>
                        <div class="document-grid">
                            <div class="field">
                                <label>Nama Legalitas</label>
                                <input type="text" placeholder="Masukkan Nama Legalitas (cth:Baznas)">
                                <span class="field-icon edit">✎</span>
                            </div>

                            <div class="field">
                                <label>Link Legalitas</label>
                                <input type="text" placeholder="Masukkan Link Legalitas (cth: Link Drive)">
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
                            <input type="email" placeholder="Masukkan Email Anda">
                            <span class="field-icon edit">✎</span>
                        </div>

                        <div class="field">
                            <label>Masukkan Nomor Hotline</label>
                            <input type="text" placeholder="Masukkan Nomor Hotline Anda">
                            <span class="field-icon edit">✎</span>
                        </div>
                    </div>

                    <div class="contact-group">
                        <h3>Sosial Media <em>(Opsional)</em></h3>

                        <div class="field">
                            <label>Masukkan Username Instagram</label>
                            <input type="text" placeholder="Masukkan Username Instagram Anda">
                            <span class="field-icon edit">✎</span>
                        </div>

                        <div class="field">
                            <label>Masukkan Username Facebook</label>
                            <input type="text" placeholder="Masukkan Username Facebook Anda">
                            <span class="field-icon edit">✎</span>
                        </div>

                        <div class="field">
                            <label>Masukkan Username Youtube</label>
                            <input type="text" placeholder="Masukkan Username Youtube Anda">
                            <span class="field-icon edit">✎</span>
                        </div>

                        <div class="field">
                            <label>Masukkan Username Tiktok</label>
                            <input type="text" placeholder="Masukkan Username Tiktok Anda">
                            <span class="field-icon edit">✎</span>
                        </div>
                    </div>
                </section>

            </form>
        </div>
    </main>

    @include('components.footer')

</body>
</html>