<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Ditolak - OrangBaik.id</title>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/penggalang/rejected.css') }}">
</head>

<body>
    @include('components.header')
    <main class="page-wrapper">
        <section class="rejected-section">
            <div class="container">
                <div class="rejected-card">
                    <div class="rejected-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <span class="rejected-badge">
                        Pengajuan Ditolak
                    </span>
                    <h1>
                        Pengajuan Anda Belum Dapat Disetujui
                    </h1>
                    <p class="rejected-desc">
                        Pengajuan telah ditinjau oleh tim OrangBaik.id. Saat ini masih terdapat beberapa data yang perlu
                        diperbaiki sebelum akun penggalang dana dapat disetujui.
                    </p>
                    <div class="rejected-box">
                        <h3>
                            <i class="bi bi-chat-left-text-fill"></i>
                            Catatan Admin
                        </h3>
                        <div class="reject-reason-card">
                            <h3>Alasan Penolakan</h3>
                            <div class="reject-reason">
                                {!! nl2br(e($penggalang->catatan_verifikasi)) !!}
                            </div>
                        </div>
                    </div>
                    <div class="tips-box">
                        <h3>
                            <i class="bi bi-lightbulb-fill"></i>
                            Yang Perlu Dilakukan
                        </h3>
                        <ul>
                            <li>Perbaiki data sesuai catatan admin.</li>
                            <li>Pastikan seluruh dokumen dapat diakses.</li>
                            <li>Lengkapi informasi yang masih kurang.</li>
                            <li>Ajukan kembali setelah semua diperbaiki.</li>
                        </ul>
                    </div>
                    <div class="rejected-actions">
                        <a href="{{ route('penggalang_dana.edit', [
    'id' => $penggalang->id,
    'mode' => 'revisi'
]) }}" class="btn-primary">
                            Perbaiki Pengajuan
                        </a>
                        <a href="{{ route('profile.user') }}" class="btn-secondary">
                            Kembali ke Profil
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    @include('components.footer')
    <script src="{{ asset('js/header.js') }}"></script>
</body>

</html>