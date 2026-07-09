<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Ditolak - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rejected.css') }}">
</head>

<body>

@include('components.header')

@php
    $catatanVerifikasi = $penggalang->catatan_verifikasi ?? 'Belum ada catatan tambahan dari admin.';
@endphp

<main class="rejected-page">

    <section class="rejected-section">
        <div class="container">

            <article class="rejected-shell">

                <div class="rejected-status-card">
                    <div class="rejected-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>

                    <span class="rejected-badge">
                        Pengajuan Ditolak
                    </span>

                    <h1>
                        Pengajuan Belum Dapat Disetujui
                    </h1>

                    <p>
                        Tim OrangBaik.id telah meninjau pengajuan Anda. Ada beberapa data
                        yang perlu diperbaiki sebelum akun penggalang dana dapat disetujui.
                    </p>
                </div>

                <div class="rejected-detail-card">

                    <div class="rejected-detail-header">
                        <span>
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </span>

                        <div>
                            <h2>Detail Revisi Pengajuan</h2>
                            <p>Perbaiki data berdasarkan catatan admin di bawah ini.</p>
                        </div>
                    </div>

                    <div class="rejected-block">
                        <div class="rejected-block-title">
                            <i class="bi bi-chat-left-text-fill"></i>
                            <h3>Catatan Admin</h3>
                        </div>

                        <div class="rejected-reason">
                            {!! nl2br(e($catatanVerifikasi)) !!}
                        </div>
                    </div>

                    <div class="rejected-block">
                        <div class="rejected-block-title">
                            <i class="bi bi-lightbulb-fill"></i>
                            <h3>Yang Perlu Dilakukan</h3>
                        </div>

                        <ul class="rejected-tips">
                            <li>Perbaiki data sesuai catatan admin.</li>
                            <li>Pastikan seluruh dokumen dapat diakses dan terbaca jelas.</li>
                            <li>Lengkapi informasi yang masih kurang.</li>
                            <li>Ajukan kembali setelah semua data diperbaiki.</li>
                        </ul>
                    </div>

                    <div class="rejected-actions">
                        <a
                            href="{{ route('penggalang_dana.edit', [
                                'id' => $penggalang->id,
                                'mode' => 'revisi'
                            ]) }}"
                            class="rejected-primary-button">
                            <i class="bi bi-pencil-square"></i>
                            <span>Perbaiki Pengajuan</span>
                        </a>

                        <a href="{{ route('profile.user') }}" class="rejected-secondary-button">
                            <i class="bi bi-arrow-left"></i>
                            <span>Kembali ke Profil</span>
                        </a>
                    </div>

                </div>

            </article>

        </div>
    </section>

</main>

@include('components.footer')

<script src="{{ asset('js/header.js') }}"></script>

</body>
</html>