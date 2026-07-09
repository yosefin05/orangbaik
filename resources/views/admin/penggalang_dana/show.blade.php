@extends('layouts.admin')

@section('page-title', 'Detail Penggalang Dana')

@section('content')

    {{-- Profil Penggalang Dana --}}
    <section class="ob-card ob-card-lg profile-card">

        @if($penggalangDana->foto_profil)
            <img src="{{ asset('storage/' . $penggalangDana->foto_profil) }}" alt="{{ $penggalangDana->nama_penggalang }}"
                class="profile-photo">
        @else
            <div class="profile-photo table-avatar-placeholder">
                {{ strtoupper(substr($penggalangDana->nama_penggalang, 0, 1)) }}
            </div>
        @endif

        <div class="profile-info">
            <h2>{{ $penggalangDana->nama_penggalang }}</h2>

            <p class="profile-type">
                {{ ucfirst($penggalangDana->jenis_penggalang) }}
            </p>

            @if($penggalangDana->status === 'pending')
                <span class="badge badge-yellow">
                    Pending
                </span>
                @if($penggalangDana->revision_count > 0)
                    <span class="badge badge-info ms-2">
                        🔄 Revisi ke-{{ $penggalangDana->revision_count }}
                    </span>
                @endif
            @elseif($penggalangDana->status === 'approved')
                <span class="badge badge-green">
                    Approved
                </span>
            @else
                <span class="badge badge-red">
                    Rejected
                </span>
            @endif
        </div>
    </section>

    {{-- Informasi Utama --}}
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Informasi Penggalang Dana</h2>
                <p class="card-subtitle">
                    Detail data utama penggalang dana.
                </p>
            </div>

            <a href="{{ route('admin.penggalang_dana.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <table class="data-table data-table-kv">
            <tbody>
                <tr>
                    <th>User</th>
                    <td>{{ $penggalangDana->user->name ?? 'User tidak ditemukan' }}</td>
                </tr>

                <tr>
                    <th>Email</th>
                    <td>{{ $penggalangDana->email }}</td>
                </tr>

                <tr>
                    <th>Nomor Telepon</th>
                    <td>{{ $penggalangDana->no_telepon }}</td>
                </tr>

                <tr>
                    <th>Alamat</th>
                    <td>{{ $penggalangDana->alamat }}</td>
                </tr>

                <tr>
                    <th>Tanggal Daftar</th>
                    <td>{{ $penggalangDana->created_at->format('d M Y H:i') }}</td>
                </tr>
            </tbody>
        </table>

    </section>

    {{-- Deskripsi --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Deskripsi</h2>
                <p class="card-subtitle">
                    Penjelasan singkat tentang penggalang dana.
                </p>
            </div>
        </div>

        <p class="card-text">
            {{ $penggalangDana->deskripsi }}
        </p>

    </section>

    {{-- Visi & Misi --}}
    <div class="info-grid">

        <section class="ob-card ob-card-lg">
            <div class="card-topbar">
                <div>
                    <h2>Visi</h2>
                    <p class="card-subtitle">
                        Tujuan utama penggalang dana.
                    </p>
                </div>
            </div>

            <p class="card-text">
                {{ $penggalangDana->visi }}
            </p>
        </section>

        <section class="ob-card ob-card-lg">
            <div class="card-topbar">
                <div>
                    <h2>Misi</h2>
                    <p class="card-subtitle">
                        Langkah dan rencana penggalang dana.
                    </p>
                </div>
            </div>

            <p class="card-text">
                {{ $penggalangDana->misi }}
            </p>
        </section>

    </div>

    {{-- Media Sosial --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Media Sosial</h2>
                <p class="card-subtitle">
                    Akun media sosial penggalang dana.
                </p>
            </div>
        </div>

        <table class="data-table data-table-kv">
            <tbody>
                <tr>
                    <th>Instagram</th>
                    <td>{{ $penggalangDana->instagram ?: '-' }}</td>
                </tr>

                <tr>
                    <th>Facebook</th>
                    <td>{{ $penggalangDana->facebook ?: '-' }}</td>
                </tr>

                <tr>
                    <th>Youtube</th>
                    <td>{{ $penggalangDana->youtube ?: '-' }}</td>
                </tr>

                <tr>
                    <th>Tiktok</th>
                    <td>{{ $penggalangDana->tiktok ?: '-' }}</td>
                </tr>
            </tbody>
        </table>

    </section>

    {{-- Dokumen Verifikasi --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Dokumen Verifikasi</h2>
                <p class="card-subtitle">
                    Dokumen pendukung untuk proses verifikasi.
                </p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Nama Dokumen</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($penggalangDana->penggalangDanaDokumen as $dokumen)

                        <tr>
                            <td>
                                <p class="cell-title">
                                    {{ $dokumen->nama_dokumen }}
                                </p>
                            </td>

                            <td>
                                <a href="{{ $dokumen->file_dokumen }}" target="_blank" class="action-link link-blue">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Lihat Dokumen</span>
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="2" class="empty-state">
                                Tidak ada dokumen.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

    </section>

    {{-- Riwayat Verifikasi --}}
    @if($penggalangDana->verified_by || $penggalangDana->verified_at)

        <section class="ob-card ob-card-lg">

            <div class="card-topbar">
                <div>
                    <h2>Riwayat Verifikasi</h2>
                    <p class="card-subtitle">
                        Informasi admin yang melakukan verifikasi.
                    </p>
                </div>
            </div>

            <table class="data-table data-table-kv">
                <tbody>
                    <tr>
                        <th>Diverifikasi Oleh</th>
                        <td>{{ optional($penggalangDana->verifier)->name ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Tanggal Verifikasi</th>
                        <td>{{ optional($penggalangDana->verified_at)->format('d M Y H:i') ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

        </section>

    @endif

    {{-- ACTION ADMIN --}}
    {{-- ACTION BUTTONS --}}
    <div class="form-actions">

        @if($penggalangDana->status !== 'approved')
            <form action="{{ route('admin.penggalang_dana.approve', $penggalangDana) }}" method="POST">
                @csrf
                @method('PATCH')

                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Setujui
                </button>
            </form>
        @endif

        @if($penggalangDana->status !== 'rejected')
            <button type="button" class="btn-danger" onclick="openRejectModal()">

                <i class="bi bi-x-circle"></i>
                Tolak

            </button>
        @endif

    </div>

    <div id="rejectModal" class="reject-modal">
        <div class="reject-modal-content">
            <form action="{{ route('admin.penggalang_dana.reject', $penggalangDana) }}" method="POST"
                onsubmit="return gabungkanAlasan(event)">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h3>Konfirmasi Penolakan</h3>
                    <button type="button" class="close-btn" onclick="closeRejectModal()">
                        &times;
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-desc">
                        Pilih alasan penolakan pengajuan.
                    </p>
                    <label class="checkbox-item">
                        <input class="alasan-check" type="checkbox"
                            value="Dokumen legalitas belum lengkap atau kurang jelas.">
                        <span>Dokumen legalitas belum lengkap / kurang jelas</span>
                    </label>
                    <label class="checkbox-item">
                        <input class="alasan-check" type="checkbox" value="Link atau URL dokumen tidak dapat dibuka.">
                        <span>Link / URL dokumen tidak dapat dibuka</span>
                    </label>
                    <label class="checkbox-item">
                        <input class="alasan-check" type="checkbox" value="Data penggalang dana tidak sesuai.">
                        <span>Data penggalang dana tidak sesuai</span>
                    </label>
                    <label class="checkbox-item">
                        <input class="alasan-check" type="checkbox" value="Informasi profil belum lengkap.">
                        <span>Informasi profil belum lengkap</span>
                    </label>
                    <label class="checkbox-item">
                        <input class="alasan-check" type="checkbox" value="Foto profil atau thumbnail tidak sesuai.">
                        <span>Foto profil / thumbnail tidak sesuai</span>
                    </label>
                    <label class="checkbox-item">
                        <input class="alasan-check" type="checkbox" value="Konten melanggar syarat dan ketentuan platform.">
                        <span>Melanggar syarat dan ketentuan platform</span>
                    </label>
                    <hr>
                    <label class="checkbox-item">
                        <input type="checkbox" id="lainnya">
                        <span>Lainnya</span>
                    </label>

                    <textarea id="lainnyaText" class="form-control" rows="4" placeholder="Tuliskan alasan lainnya..."
                        style="display:none;margin-top:12px;"></textarea>
                    <input type="hidden" name="catatan_verifikasi" id="catatan_verifikasi">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeRejectModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn-danger">
                        Tolak Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const modal = document.getElementById("rejectModal");
        const lainnya = document.getElementById("lainnya");
        const lainnyaText = document.getElementById("lainnyaText");

        function openRejectModal() {
            modal.classList.add("show");
        }

        function closeRejectModal() {
            modal.classList.remove("show");
        }

        window.onclick = function (e) {
            if (e.target == modal) {
                closeRejectModal();
            }
        }
        lainnya.addEventListener("change", function () {
            lainnyaText.style.display = this.checked ? "block" : "none";
            if (!this.checked) {
                lainnyaText.value = "";
            }
        });

        function gabungkanAlasan(e) {
            let hasil = [];
            document.querySelectorAll(".alasan-check:checked").forEach(function (item) {
                hasil.push("• " + item.value);
            });
            if (lainnya.checked && lainnyaText.value.trim() !== "") {
                hasil.push("• " + lainnyaText.value.trim());
            }
            if (hasil.length == 0) {
                alert("Pilih minimal satu alasan.");
                e.preventDefault();
                return false;
            }
            document.getElementById("catatan_verifikasi").value = hasil.join("\n");
            return true;
        }
    </script>
@endsection