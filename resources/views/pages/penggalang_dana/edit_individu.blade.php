<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Penggalang Dana Individu - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penggalang-register.css') }}">\n    <link rel="stylesheet" href="{{ asset('css/verify-penggalang.css') }}">
</head>

<body>

    @include('components.header')

    @php
        $user = auth()->user();
        $status = $penggalang->status ?? null;
        $isPending = ($status === 'pending');
        $isRejected = ($status === 'rejected');
        $isApproved = ($status === 'approved');
    @endphp

    <main class="reg-page verify-page">

        <section class="verify-hero">
            <div class="container verify-hero-inner">
                <div class="verify-heading">
                    <span class="verify-eyebrow">Edit Profil</span>
                    <h1>Edit Penggalang Dana Individu</h1>
                    <p>Perbarui informasi penggalang dana kamu.</p>
                </div>
            </div>
        </section>

        <section class="verify-section">
            <div class="container">

                {{-- STATUS BADGE & PERINGATAN --}}
                @if($penggalang)
                    <div class="verify-card status-card">
                        <div class="status-row">
                            <span class="status-label">Status Penggalang:</span>
                            <span class="status-badge {{ $status }}">
                                {{ ucfirst($status ?? 'Belum terdaftar') }}
                            </span>
                            @if($isPending)
                            <span class="status-note">
                                    ⏳ Sedang diverifikasi, Anda tidak dapat mengubah data sampai verifikasi selesai.
                                </span>
                            @endif
                        </div>

                        {{-- Tampilkan peringatan hanya jika status Approved --}}
                        @if($isApproved)
                            <div class="info-note">
                                <i class="bi bi-info-circle"></i>
                                Hanya perubahan pada <strong>dokumen identitas</strong> yang akan mengembalikan status ke <strong>Pending</strong> dan perlu verifikasi ulang. Perubahan data lain (profil, kontak, sosial media) tidak mempengaruhi status.
                            </div>
                        @endif

                        {{-- Jika status Rejected, tampilkan pesan --}}
                        @if($isRejected)
                            <div class="alert-warning" style="margin-top: var(--space-3);">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>Pengajuan ditolak.</strong> Silakan perbaiki data yang diperlukan dan kirim ulang.
                            </div>
                        @endif
                    </div>
                @endif

                <form id="deleteDocumentsForm" method="POST"
                    action="{{ route('penggalang_dana.dokumen.destroy', $penggalang->slug ?? $penggalang->id) }}">
                    @csrf
                    @method('DELETE')
                </form>

                <form action="{{ route('penggalang_dana.update', $penggalang->slug) }}" method="POST"
                    enctype="multipart/form-data" class="reg-form verify-form {{ $isPending ? 'disabled-overlay' : '' }}">

                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="jenis_penggalang" value="individu">

                    @if($isPending)
                        <div class="pending-notice">
                            <p>⚠️ Akun Anda sedang dalam proses verifikasi. Untuk menjaga integritas data, perubahan tidak dapat dilakukan saat ini. Silakan tunggu hingga verifikasi selesai.</p>
                        </div>
                    @endif

                    {{-- PROFIL --}}
                    <section class="reg-card verify-card">
                        <div class="reg-card-head verify-card-header">
                            <h2>Profil Penggalang Dana</h2>
                            <p>Data dasar penggalang dana.</p>
                        </div>

                        <div class="verify-profile-grid">

                            <div class="verify-avatar-upload">
                                <div class="verify-avatar-preview" id="avatarPreview">
                                    @if($penggalang->foto_profil)
                                        <img src="{{ asset('storage/' . $penggalang->foto_profil) }}">
                                    @else
                                        <i class="bi bi-person-fill"></i>
                                    @endif
                                </div>
                                <label class="verify-upload-button">
                                    <input type="file" name="foto_profil" id="fotoProfilInput" accept="image/*" {{ $isPending ? 'disabled' : '' }}>
                                    @if($penggalang->foto_profil)
                                        <label><input type="checkbox" name="remove_foto_profil" value="1" {{ $isPending ? 'disabled' : '' }}> Hapus foto</label>
                                    @endif
                                    <i class="bi bi-camera-fill"></i>
                                    <span>Ganti Foto</span>
                                </label>
                            </div>

                            <div class="verify-fields">
                                <label class="reg-field verify-field">
                                    <span>Jenis Penggalang</span>
                                    <input type="text" value="Individu" readonly>
                                </label>
                                <label class="reg-field verify-field">
                                    <span>Nama Lengkap</span>
                                    <input type="text" name="nama_penggalang"
                                        value="{{ old('nama_penggalang', $penggalang->nama_penggalang) }}"
                                        {{ $isPending ? 'disabled' : '' }}>
                                    @error('nama_penggalang')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </label>
                            </div>
                        </div>

                        <label class="reg-field verify-field">
                            <span>Alamat Domisili</span>
                            <textarea name="alamat" rows="3" {{ $isPending ? 'disabled' : '' }}>{{ old('alamat', $penggalang->alamat) }}</textarea>
                            @error('alamat')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </label>
                    </section>

                    {{-- INFORMASI --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Informasi Penggalang</h2>
                        </div>

                        <label class="reg-field verify-field">
                            <span>Deskripsi</span>
                            <textarea name="deskripsi" rows="6" {{ $isPending ? 'disabled' : '' }}>{{ old('deskripsi', $penggalang->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </label>

                        <div class="reg-grid-2 verify-grid-2">
                            <label class="reg-field verify-field">
                                <span>Visi</span>
                                <textarea name="visi" rows="3" {{ $isPending ? 'disabled' : '' }}>{{ old('visi', $penggalang->visi) }}</textarea>
                                @error('visi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </label>
                            <label class="reg-field verify-field">
                                <span>Misi</span>
                                <textarea name="misi" rows="4" {{ $isPending ? 'disabled' : '' }}>{{ old('misi', $penggalang->misi) }}</textarea>
                                @error('misi')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </label>
                        </div>
                    </section>

                    {{-- DOKUMEN IDENTITAS --}}
                    <section class="verify-card {{ $isPending ? 'verify-card-disabled' : '' }}">
                        <div class="verify-card-header">
                            <h2>Dokumen Identitas</h2>
                            <p>
                                @if($isApproved)
                                    <span class="doc-warning">⚠️ Perubahan pada dokumen akan mengembalikan status ke <strong>Pending</strong> dan perlu verifikasi ulang.</span>
                                @elseif($isRejected)
                                    <span class="doc-warning">⚠️ Perbaiki dokumen dan ajukan ulang melalui halaman penolakan, atau edit data ini dan ubah dokumen untuk verifikasi ulang.</span>
                                @else
                                    <span class="text-muted">Tidak dapat diubah saat proses verifikasi.</span>
                                @endif
                            </p>
                        </div>

                        @php
                            $dokumen = $penggalang->penggalangDanaDokumen[0] ?? null;
                        @endphp

                        @if($dokumen && !$isPending && (int) auth()->id() === (int) $penggalang->user_id)
                            <label class="field-help" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <input type="checkbox" name="dokumen_ids[]" value="{{ $dokumen->id }}" form="deleteDocumentsForm">
                                Pilih dokumen untuk dihapus
                            </label>
                        @endif

                        <div class="reg-grid-2 verify-grid-2">
                            <label class="reg-field verify-field">
                                <span>Nama Dokumen <b>*</b></span>
                                <input type="hidden" name="dokumen_ids[]" value="{{ $dokumen?->id }}">
                            <input type="text" name="nama_dokumen[]"
                                    value="{{ old('nama_dokumen.0', $dokumen ? 'Dokumen Identitas' : '') }}"
                                    {{ $isPending ? 'disabled' : '' }}
                                    placeholder="Contoh: KTP, NPWP" required>
                            </label>
                            <label class="reg-field verify-field">
                                <span>Unggah Dokumen Baru</span>
                                <input type="file" name="dokumen_files[]" accept=".pdf,.jpg,.jpeg,.png"
                                    {{ $isPending ? 'disabled' : '' }}>
                                <small class="field-help">PDF, JPG, atau PNG. Maksimal 5 MB.</small>
                            </label>
                        </div>
                        @if($dokumen)
                            <p class="field-help"><i class="bi bi-lock-fill"></i> Dokumen tersimpan dan hanya dapat diperiksa admin. Untuk mengganti dokumen, unggah file baru secara langsung.</p>
                        @endif
                        @if($dokumen && !$isPending && (int) auth()->id() === (int) $penggalang->user_id)
                            <div class="reg-actions verify-actions" style="justify-content:flex-end;">
                                <button type="submit" form="deleteDocumentsForm" class="reg-btn reg-btn-ghost verify-cancel-button"
                                    style="border:1px solid #dc3545;color:#dc3545;background:#fff;"
                                    onclick="return confirm('Hapus dokumen identitas ini? Tindakan ini tidak dapat dibatalkan.');">
                                    <i class="bi bi-trash"></i> Hapus dokumen
                                </button>
                            </div>
                        @endif
                    </section>

                    {{-- KONTAK --}}
                    <section class="verify-card">
                        <div class="verify-card-header">
                            <h2>Kontak</h2>
                        </div>
                        <div class="reg-grid-2 verify-grid-2">
                            <label class="reg-field verify-field">
                                <span>Email</span>
                                <input type="email" name="email" value="{{ old('email', $penggalang->email) }}" {{ $isPending ? 'disabled' : '' }}>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </label>
                            <label class="reg-field verify-field">
                                <span>Nomor Telepon</span>
                                <input type="text" name="no_telepon"
                                    value="{{ old('no_telepon', $penggalang->no_telepon) }}" {{ $isPending ? 'disabled' : '' }}>
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
                        </div>
                        <div class="reg-grid-2 verify-grid-2">
                            <label class="reg-field verify-field">
                                <input type="text" name="instagram" value="{{ old('instagram', ltrim((string) $penggalang->instagram, '@')) }}" placeholder="Username Instagram" {{ $isPending ? 'disabled' : '' }}>
                            </label>
                            <label class="reg-field verify-field">
                                <input type="text" name="facebook" value="{{ old('facebook', $penggalang->facebook) }}" placeholder="Facebook" {{ $isPending ? 'disabled' : '' }}>
                            </label>
                            <label class="reg-field verify-field">
                                <input type="text" name="youtube" value="{{ old('youtube', $penggalang->youtube) }}" placeholder="Youtube" {{ $isPending ? 'disabled' : '' }}>
                            </label>
                            <label class="reg-field verify-field">
                                <input type="text" name="tiktok" value="{{ old('tiktok', ltrim((string) $penggalang->tiktok, '@')) }}" placeholder="Username TikTok" {{ $isPending ? 'disabled' : '' }}>
                            </label>
                        </div>
                    </section>

                        {{-- ACTION --}}
                        <div class="verify-actions">
                            <a href="{{ route('profile.user') }}" class="verify-cancel-button">Batal</a>
                            <button type="submit" class="reg-btn reg-btn-primary verify-submit-button" {{ $isPending ? 'disabled' : '' }}>
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
