<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun Penggalang Dana Individu - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/verifikasi-penggalang.css') }}">
</head>
<body>

@php
    $userName = auth()->check() ? auth()->user()->name : 'Yosefin Kurniawati Tanto';
@endphp

<main class="verify-page">
    <div class="verify-container">

        <button class="verify-back" type="button" onclick="history.back()">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 18L9 12L15 6" />
            </svg>
            <span>Kembali</span>
        </button>

        <section class="verify-heading">
            <h1>Verifikasi Akun Penggalang Dana</h1>
            <p>
                Lengkapi informasi untuk proses pendaftaran penggalang dana. Data yang diberikan akan
                digunakan untuk verifikasi akun dan pengelolaan campaign di orangbaik.id
            </p>
        </section>

        <form action="#" method="POST" enctype="multipart/form-data" class="verify-form">
            @csrf

            {{-- PROFIL --}}
            <section class="form-section">
                <h2>Profil Penggalang Dana</h2>

                <div class="profile-form-grid">
                    <div class="avatar-upload">
                        <div class="avatar-preview">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 12C14.76 12 17 9.76 17 7C17 4.24 14.76 2 12 2C9.24 2 7 4.24 7 7C7 9.76 9.24 12 12 12Z"/>
                                <path d="M4 22C4.7 17.9 7.8 15.5 12 15.5C16.2 15.5 19.3 17.9 20 22H4Z"/>
                            </svg>
                        </div>

                        <label class="camera-button">
                            <input type="file" name="foto_profil" accept="image/*">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 8H8L10 5H14L16 8H20V19H4V8Z"/>
                                <path d="M12 17C14.2 17 16 15.2 16 13C16 10.8 14.2 9 12 9C9.8 9 8 10.8 8 13C8 15.2 9.8 17 12 17Z"/>
                            </svg>
                        </label>
                    </div>

                    <div class="profile-fields">
                        <label class="input-card select-card">
                            <span>Jenis Penggalang Dana</span>

                            <select name="jenis_penggalang">
                                <option value="individu" selected>Individu</option>
                                <option value="organisasi">Organisasi</option>
                            </select>

                            <i>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 9L12 15L18 9"/>
                                </svg>
                            </i>
                        </label>

                        <label class="input-card">
                            <span>Nama Lengkap</span>

                            <input 
                                type="text" 
                                name="nama_penggalang"
                                value="{{ $userName }}"
                                placeholder="Masukkan Nama Lengkap"
                            >

                            <i>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/>
                                    <path d="M13.5 6.5L17.5 10.5"/>
                                </svg>
                            </i>
                        </label>
                    </div>
                </div>

                <label class="input-card full">
                    <span>Alamat Domisili</span>

                    <textarea name="alamat" rows="2" placeholder="Masukkan alamat domisili lengkap"></textarea>

                    <i>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 21C12 21 19 15.5 19 9C19 5.1 15.9 2 12 2C8.1 2 5 5.1 5 9C5 15.5 12 21 12 21Z"/>
                            <path d="M12 12C13.7 12 15 10.7 15 9C15 7.3 13.7 6 12 6C10.3 6 9 7.3 9 9C9 10.7 10.3 12 12 12Z"/>
                        </svg>
                    </i>
                </label>
            </section>

            {{-- INFORMASI --}}
            <section class="form-section">
                <h2>Informasi Penggalang Dana</h2>

                <label class="input-card full textarea-card">
                    <span>Cerita / Profil Singkat Penggalang Dana</span>

                    <textarea 
                        name="deskripsi" 
                        rows="5" 
                        placeholder="Ceritakan siapa kamu, alasan menjadi penggalang dana, dan bentuk tanggung jawab kamu terhadap campaign yang akan dibuat."
                    ></textarea>

                    <i>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/>
                            <path d="M13.5 6.5L17.5 10.5"/>
                        </svg>
                    </i>
                </label>
            </section>

            {{-- LEGALITAS --}}
            <section class="form-section">
                <h2>Lengkapi Dokumen Legalitas</h2>

                <p class="section-desc">
                    Unggah dokumen identitas untuk memperkuat kredibilitas akun penggalang dana Anda
                    serta meningkatkan kepercayaan donatur terhadap campaign yang Anda jalankan.
                </p>

                <h3>Dokumen Identitas <span>*</span></h3>

                <div class="two-column">
                    <label class="input-card">
                        <span>Nama Dokumen</span>

                        <input 
                            type="text" 
                            name="nama_legalitas"
                            placeholder="Masukkan Nama Dokumen (cth: KTP)"
                        >

                        <i>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/>
                                <path d="M13.5 6.5L17.5 10.5"/>
                            </svg>
                        </i>
                    </label>

                    <label class="input-card">
                        <span>Link Dokumen</span>

                        <input 
                            type="url" 
                            name="link_legalitas"
                            placeholder="Masukkan Link Dokumen (cth: Link Drive)"
                        >

                        <i>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/>
                                <path d="M13.5 6.5L17.5 10.5"/>
                            </svg>
                        </i>
                    </label>
                </div>
            </section>

            {{-- KONTAK --}}
            <section class="form-section">
                <h2>Kontak & Sosial Media</h2>

                <p class="section-desc">
                    Informasi kontak dan media sosial yang lengkap membantu membangun transparansi
                    serta meningkatkan kredibilitas penggalangan dana Anda.
                </p>

                <h3>Kontak<span>*</span></h3>

                <div class="stack-fields">
                    <label class="input-card full">
                        <span>Masukkan Email</span>

                        <input 
                            type="email" 
                            name="email"
                            placeholder="Masukkan Email Anda"
                        >

                        <i>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/>
                                <path d="M13.5 6.5L17.5 10.5"/>
                            </svg>
                        </i>
                    </label>

                    <label class="input-card full">
                        <span>Masukkan Nomor Telepon</span>

                        <input 
                            type="text" 
                            name="no_telepon"
                            placeholder="Masukkan Nomor Telepon Anda"
                        >

                        <i>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/>
                                <path d="M13.5 6.5L17.5 10.5"/>
                            </svg>
                        </i>
                    </label>
                </div>

                <h3>Sosial Media <small>(Opsional)</small></h3>

                <div class="stack-fields">
                    <label class="input-card full">
                        <span>Masukkan Username Instagram</span>
                        <input type="text" name="instagram" placeholder="Masukkan Username Instagram Anda">
                        <i><svg viewBox="0 0 24 24"><path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/><path d="M13.5 6.5L17.5 10.5"/></svg></i>
                    </label>

                    <label class="input-card full">
                        <span>Masukkan Username Facebook</span>
                        <input type="text" name="facebook" placeholder="Masukkan Username Facebook Anda">
                        <i><svg viewBox="0 0 24 24"><path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/><path d="M13.5 6.5L17.5 10.5"/></svg></i>
                    </label>

                    <label class="input-card full">
                        <span>Masukkan Username Youtube</span>
                        <input type="text" name="youtube" placeholder="Masukkan Username Youtube Anda">
                        <i><svg viewBox="0 0 24 24"><path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/><path d="M13.5 6.5L17.5 10.5"/></svg></i>
                    </label>

                    <label class="input-card full">
                        <span>Masukkan Username Tiktok</span>
                        <input type="text" name="tiktok" placeholder="Masukkan Username Tiktok Anda">
                        <i><svg viewBox="0 0 24 24"><path d="M4 20H8L18.5 9.5L14.5 5.5L4 16V20Z"/><path d="M13.5 6.5L17.5 10.5"/></svg></i>
                    </label>
                </div>
            </section>

        </form>

    </div>
</main>

@include('components.footer')

</body>
</html>