<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Verifikasi Penggalang Dana Organisasi - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penggalangdana-organisasi.css') }}">
</head>

<body>

    @include('components.header')

    @php
        $user = auth()->user();
        $status = $penggalang->status ?? null;
        $isPending = ($status === 'pending');
        $isRejected = ($status === 'rejected');
    @endphp

    <main class="verify-page">

        <section class="verify-hero">
            <div class="container">
                <div class="verify-heading">
                    <span class="verify-eyebrow">Edit Organisasi</span>
                    <h1>Edit Verifikasi Akun Penggalang Dana</h1>
                    <p>
                        Perbarui informasi organisasi untuk proses verifikasi penggalang dana.
                        Data yang diberikan akan digunakan untuk verifikasi akun dan pengelolaan campaign di
                        OrangBaik.id.
                    </p>
                </div>
            </div>
        </section>

        <section class="verify-section">
            <div class="container">

                {{-- STATUS BADGE & PERINGATAN --}}
                @if($penggalang)
                    <div class="verify-card" style="margin-bottom: var(--space-5);">
                        <div style="display: flex; align-items: center; gap: var(--space-4); flex-wrap: wrap;">
                            <span style="font-weight: var(--fw-semibold);">Status Penggalang:</span>
                            <span class="status-badge {{ $status }}">
                                {{ ucfirst($status ?? 'Belum terdaftar') }}
                            </span>
                            @if($isPending)
                                <span style="color: var(--muted); font-size: var(--fs-xxs);">
                                    ⏳ Sedang diverifikasi, Anda tidak dapat mengubah data sampai verifikasi selesai.
                                </span>
                            @endif
                        </div>

                        {{-- INFO NOTE: Hanya tampil jika status NOT rejected dan NOT pending --}}
                        @if(!$isPending && !$isRejected)
                            <div class="info-note">
                                <i class="bi bi-info-circle"></i>
                                Hanya perubahan pada <strong>dokumen legalitas</strong> yang akan mengembalikan status ke <strong>Pending</strong> dan perlu verifikasi ulang. Perubahan data lain (profil, kontak, sosial media) tidak mempengaruhi status.
                            </div>
                        @endif

                        {{-- Tampilkan pesan khusus untuk rejected --}}
                        @if($isRejected)
                            <div class="alert-warning" style="margin-top: var(--space-3);">
                                <strong>⚠️ Pengajuan ditolak.</strong> Silakan perbaiki data yang diperlukan dan kirim ulang.
                            </div>
                        @endif
                    </div>
                @endif

                <form class="verify-form {{ $isPending ? 'disabled-overlay' : '' }}"
                    action="{{ route('penggalang_dana.update', $penggalang->id) }}"
                    method="POST" enctype="multipart/form-data" novalidate>

                    @csrf
                    @method('PATCH')

                    @if($isPending)
                        <div style="background: var(--bg-soft); padding: var(--space-4); border-radius: var(--radius-sm); margin-bottom: var(--space-4);">
                            <p style="color: var(--muted);">
                                ⚠️ Akun Anda sedang dalam proses verifikasi. Untuk menjaga integritas data, perubahan tidak dapat dilakukan saat ini. Silakan tunggu hingga verifikasi selesai.
                            </p>
                        </div>
                    @endif

                    {{-- BANNER --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Banner Organisasi</h2>
                            <p>Upload banner organisasi yang akan ditampilkan pada profil penggalang dana.</p>
                        </div>

                        <div class="verify-cover-upload">
                            @if($penggalang->thumbnail)
                                <img id="thumbnailPreview" class="verify-cover-preview"
                                    src="{{ asset('storage/' . $penggalang->thumbnail) }}" alt="Banner Organisasi">
                            @else
                                <img id="thumbnailPreview" class="verify-cover-preview" src=""
                                    style="display:none;" alt="Banner Organisasi">
                            @endif

                            <div id="thumbnailPlaceholder" class="verify-cover-placeholder"
                                @if($penggalang->thumbnail) style="display:none;" @endif>
                                <i class="bi bi-image-fill"></i>
                                <strong>Upload Banner Organisasi</strong>
                                <span>Format JPG, PNG, atau WEBP</span>
                            </div>

                            <label class="verify-cover-button">
                                <input type="file" id="thumbnailInput" name="thumbnail" accept="image/*"
                                    {{ $isPending ? 'disabled' : '' }}>
                                <i class="bi bi-camera-fill"></i>
                                <span>{{ $penggalang->thumbnail ? 'Ganti Banner' : 'Pilih Banner' }}</span>
                            </label>
                        </div>
                    </section>

                    {{-- PROFIL --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Profil Penggalang Dana</h2>
                            <p>Perbarui data dasar organisasi sebagai penggalang dana.</p>
                        </div>

                        <div class="verify-profile-grid">

                            <div class="verify-avatar-upload">
                                <div class="verify-avatar-preview" id="fotoProfilPreview">
                                    @if($penggalang->foto_profil)
                                        <img src="{{ asset('storage/' . $penggalang->foto_profil) }}" alt="Logo Organisasi">
                                    @else
                                        <i class="bi bi-building-fill"></i>
                                    @endif
                                </div>

                                <label class="verify-upload-button">
                                    <input type="file" id="fotoProfilInput" name="foto_profil" accept="image/*"
                                        {{ $isPending ? 'disabled' : '' }}>
                                    <i class="bi bi-camera-fill"></i>
                                    <span>Ganti Logo</span>
                                </label>
                            </div>

                            <div class="verify-fields">
                                <label class="verify-field">
                                    <span>Jenis Penggalang Dana</span>
                                    <input type="text" value="Organisasi" readonly>
                                    <input type="hidden" name="jenis_penggalang" value="organisasi">
                                </label>

                                <label class="verify-field">
                                    <span>Nama Organisasi <b>*</b></span>
                                    <input type="text" name="nama_penggalang"
                                        value="{{ old('nama_penggalang', $penggalang->nama_penggalang) }}"
                                        placeholder="Masukkan nama organisasi" {{ $isPending ? 'disabled' : '' }}>
                                    @error('nama_penggalang')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </label>
                            </div>
                        </div>

                        <div class="verify-grid-2">
                            <label class="verify-field">
                                <span>Tahun Berdiri <b>*</b></span>
                                <select name="tahun_berdiri" required {{ $isPending ? 'disabled' : '' }}>
                                    <option value="">Pilih tahun berdiri</option>
                                    @for($tahun = date('Y'); $tahun >= 1990; $tahun--)
                                        <option value="{{ $tahun }}"
                                            {{ old('tahun_berdiri', $penggalang->tahun_berdiri) == $tahun ? 'selected' : '' }}>
                                            {{ $tahun }}
                                        </option>
                                    @endfor
                                </select>
                                @error('tahun_berdiri')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="verify-field">
                                <span>Email Organisasi <b>*</b></span>
                                <input type="email" name="email"
                                    value="{{ old('email', $penggalang->email ?? $user->email ?? '') }}"
                                    placeholder="Masukkan email organisasi" {{ $isPending ? 'disabled' : '' }}>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </label>
                        </div>

                        <label class="verify-field">
                            <span>Alamat Kantor <b>*</b></span>
                            <textarea name="alamat" rows="3"
                                placeholder="Masukkan alamat kantor organisasi" {{ $isPending ? 'disabled' : '' }}>{{ old('alamat', $penggalang->alamat) }}</textarea>
                            @error('alamat')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </label>
                    </section>

                    {{-- INFORMASI --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Informasi Organisasi</h2>
                            <p>Perbarui profil, visi, dan misi organisasi sebagai penggalang dana.</p>
                        </div>

                        <label class="verify-field">
                            <span>Deskripsi Penggalang Dana <b>*</b></span>
                            <textarea name="deskripsi" rows="6"
                                placeholder="Masukkan deskripsi organisasi" {{ $isPending ? 'disabled' : '' }}>{{ old('deskripsi', $penggalang->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </label>

                        <div class="verify-grid-2">
                            <label class="verify-field">
                                <span>Visi <b>*</b></span>
                                <input type="text" name="visi"
                                    value="{{ old('visi', $penggalang->visi) }}"
                                    placeholder="Masukkan visi organisasi" {{ $isPending ? 'disabled' : '' }}>
                                @error('visi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="verify-field">
                                <span>Misi <b>*</b></span>
                                <input type="text" name="misi"
                                    value="{{ old('misi', $penggalang->misi) }}"
                                    placeholder="Masukkan misi organisasi" {{ $isPending ? 'disabled' : '' }}>
                                @error('misi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </label>
                        </div>
                    </section>

                    {{-- DOKUMEN LEGALITAS --}}
                    <section class="verify-card {{ $isPending ? 'verify-card-disabled' : '' }}">
                        <div class="verify-card-header">
                            <h2>Dokumen Legalitas</h2>
                            <p>
                                @if(!$isPending && !$isRejected)
                                    <span style="color: var(--danger); font-weight: var(--fw-semibold);">
                                        ⚠️ Perubahan pada dokumen legalitas akan mengembalikan status ke <strong>Pending</strong> dan perlu verifikasi ulang.
                                    </span>
                                @elseif($isRejected)
                                    <span style="color: var(--danger); font-weight: var(--fw-semibold);">
                                        ⚠️ Perbaiki dokumen legalitas dan kirim ulang untuk verifikasi.
                                    </span>
                                @else
                                    <span style="color: var(--muted);">Tidak dapat diubah saat proses verifikasi.</span>
                                @endif
                            </p>
                        </div>

                        <div class="verify-document-list">

                            @for($i = 1; $i <= 4; $i++)
                                @php
                                    $index = $i - 1;
                                    $dokumen = $penggalang->penggalangDanaDokumen[$index] ?? null;
                                @endphp

                                <div class="verify-document-item">

                                    <div class="verify-document-title">
                                        <h3>
                                            Dokumen Legalitas {{ $i }}
                                            @if($i <= 2)
                                                <b>*</b>
                                            @else
                                                <em>Opsional</em>
                                            @endif
                                        </h3>
                                    </div>

                                    <div class="verify-grid-2">

                                        <label class="verify-field">
                                            <span>Nama Legalitas {{ $i <= 2 ? '*' : '' }}</span>
                                            <input
                                                type="text"
                                                name="nama_dokumen[]"
                                                value="{{ old('nama_dokumen.' . $index, $dokumen->nama_dokumen ?? '') }}"
                                                placeholder="Contoh: SK Kemenkumham, Akta, BAZNAS"
                                                {{ $isPending ? 'disabled' : '' }}>
                                        </label>

                                        <label class="verify-field">
                                            <span>Link Legalitas {{ $i <= 2 ? '*' : '' }}</span>
                                            <input
                                                type="url"
                                                name="file_dokumen[]"
                                                value="{{ old('file_dokumen.' . $index, $dokumen->file_dokumen ?? '') }}"
                                                placeholder="Masukkan link Google Drive dokumen"
                                                {{ $isPending ? 'disabled' : '' }}>
                                        </label>

                                    </div>

                                </div>
                            @endfor

                        </div>
                    </section>

                    {{-- KONTAK --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Kontak</h2>
                            <p>Informasi kontak untuk verifikasi dan komunikasi.</p>
                        </div>

                        <div class="verify-grid-2">

                            <label class="verify-field">
                                <span>Email <b>*</b></span>
                                <input type="email" name="email"
                                    value="{{ old('email', $penggalang->email) }}"
                                    placeholder="Masukkan Email Organisasi" {{ $isPending ? 'disabled' : '' }}>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </label>

                            <label class="verify-field">
                                <span>Nomor Hotline <b>*</b></span>
                                <input type="text" name="no_telepon"
                                    value="{{ old('no_telepon', $penggalang->no_telepon) }}"
                                    placeholder="Masukkan Nomor Hotline" {{ $isPending ? 'disabled' : '' }}>
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
                            <p>Opsional, untuk meningkatkan kepercayaan donatur.</p>
                        </div>

                        <div class="verify-grid-2">

                            <label class="verify-field">
                                <span>Instagram</span>
                                <input type="text" name="instagram"
                                    value="{{ old('instagram', $penggalang->instagram) }}"
                                    placeholder="Username Instagram" {{ $isPending ? 'disabled' : '' }}>
                            </label>

                            <label class="verify-field">
                                <span>Facebook</span>
                                <input type="text" name="facebook"
                                    value="{{ old('facebook', $penggalang->facebook) }}"
                                    placeholder="Username Facebook" {{ $isPending ? 'disabled' : '' }}>
                            </label>

                            <label class="verify-field">
                                <span>Youtube</span>
                                <input type="text" name="youtube"
                                    value="{{ old('youtube', $penggalang->youtube) }}"
                                    placeholder="Username Youtube" {{ $isPending ? 'disabled' : '' }}>
                            </label>

                            <label class="verify-field">
                                <span>TikTok</span>
                                <input type="text" name="tiktok"
                                    value="{{ old('tiktok', $penggalang->tiktok) }}"
                                    placeholder="Username TikTok" {{ $isPending ? 'disabled' : '' }}>
                            </label>

                        </div>
                    </section>

                    {{-- ACTION BUTTONS --}}
                    <div class="verify-actions">
                        <a href="{{ route('profile.user') }}" class="verify-cancel-button">Batal</a>
                        <button type="submit" class="verify-submit-button" {{ $isPending ? 'disabled' : '' }}>
                            {{ $isRejected ? 'Kirim Ulang' : 'Perbarui Verifikasi' }}
                        </button>
                    </div>

                </form>

                {{-- Tampilkan error global jika ada --}}
                @if ($errors->any())
                    <div style="background: var(--danger-light); padding: var(--space-4); border-radius: var(--radius-sm); color: var(--danger); margin-top: var(--space-5);">
                        <ul style="margin:0;padding-left:var(--space-4);">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>
        </section>

    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/penggalang-organisasi.js') }}"></script>

    <script>
        // Preview thumbnail
        document.getElementById('thumbnailInput').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('thumbnailPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
                document.getElementById('thumbnailPlaceholder').style.display = 'none';
            };
            reader.readAsDataURL(file);
        });

        // Preview foto profil
        document.getElementById('fotoProfilInput').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('fotoProfilPreview');
                preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
            };
            reader.readAsDataURL(file);
        });
    </script>

</body>

</html>