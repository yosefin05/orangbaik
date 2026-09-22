<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Donasi - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-status.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<main class="status-page">

    {{-- Dekorasi background --}}
    <div class="status-blob status-blob--1"></div>
    <div class="status-blob status-blob--2"></div>

    <div class="ob-card status-card status-card--{{ $status }}">

        {{-- STEP TRACKER --}}
        <ol class="status-stepper">
            <li class="status-step status-step--done">
                <span class="status-step-dot"><i class="bi bi-check-lg"></i></span>
                <span class="status-step-label">Nominal</span>
            </li>
            <li class="status-step
                {{ $status == 'sukses' ? 'status-step--done' : '' }}
                {{ $status == 'pending' ? 'status-step--active' : '' }}
                {{ $status == 'gagal' ? 'status-step--failed' : '' }}">
                <span class="status-step-dot">
                    @if($status == 'sukses')
                        <i class="bi bi-check-lg"></i>
                    @elseif($status == 'gagal')
                        <i class="bi bi-x-lg"></i>
                    @else
                        <i class="bi bi-hourglass-split"></i>
                    @endif
                </span>
                <span class="status-step-label">Bayar</span>
            </li>
            <li class="status-step {{ $status == 'sukses' ? 'status-step--done' : '' }}">
                <span class="status-step-dot"><i class="bi bi-flag-fill"></i></span>
                <span class="status-step-label">Selesai</span>
            </li>
        </ol>

        @if($status == 'sukses')
            <div class="status-icon-badge status-icon-badge--success">
                <span class="status-icon-ring"></span>
                <i class="bi bi-check-lg"></i>
            </div>

            <h1 class="status-title">Donasi Berhasil! 🎉</h1>
            <p class="status-desc">Terima kasih banyak atas kebaikan Anda. Donasi ini akan langsung disalurkan dan membawa manfaat nyata bagi yang membutuhkan.</p>

            <a href="/" class="status-btn status-btn--primary">
                <i class="bi bi-house-door-fill"></i> Kembali ke Beranda
            </a>

            <button type="button" class="status-btn status-btn--ghost" id="shareBtn">
                <i class="bi bi-share-fill"></i> Ajak Teman Berdonasi Juga
            </button>

        @elseif($status == 'pending')
            <div class="status-icon-badge status-icon-badge--warning">
                <span class="status-icon-ring status-icon-ring--pulse"></span>
                <i class="bi bi-hourglass-split"></i>
            </div>

            <h1 class="status-title">Tinggal Selangkah Lagi!</h1>
            <p class="status-desc">Pembayaran Anda sedang diproses. Selesaikan pembayaran di aplikasi/metode yang Anda pilih agar donasi segera sampai ke penerima manfaat.</p>

            @if ($pembayaran)
                <a
                    href="{{ route('donasi.bayar.instruksi', [
                        'pembayaran' => $pembayaran->id,
                        'token' => request('token'),
                    ]) }}"
                    class="status-btn status-btn--primary status-btn--glow"
                >
                    <i class="bi bi-lightning-charge-fill"></i> Lanjutkan Pembayaran
                </a>
            @elseif (auth()->check())
                <a href="{{ route('riwayat.donasi') }}" class="status-btn status-btn--primary status-btn--glow">
                    <i class="bi bi-clock-history"></i> Lihat Riwayat Donasi
                </a>
            @else
                <a href="{{ route('login') }}" class="status-btn status-btn--primary status-btn--glow">
                    <i class="bi bi-box-arrow-in-right"></i> Login untuk Melihat Riwayat
                </a>
            @endif

            <p class="status-note">
                <i class="bi bi-shield-check"></i>
                Status akan diperbarui otomatis begitu pembayaran Anda terkonfirmasi.
            </p>

        @elseif($status == 'gagal')
            <div class="status-icon-badge status-icon-badge--danger">
                <span class="status-icon-ring"></span>
                <i class="bi bi-x-lg"></i>
            </div>

            <h1 class="status-title">Yah, Pembayaran Belum Berhasil</h1>
            <p class="status-desc">Tenang, niat baik Anda belum hilang! Coba lagi sekarang — biasanya cuma butuh beberapa detik untuk menyelesaikan donasi Anda.</p>

            <div class="status-btn-group">
                <a href="javascript:history.back()" class="status-btn status-btn--primary status-btn--glow">
                    <i class="bi bi-arrow-repeat"></i> Coba Lagi Sekarang
                </a>
                <a href="/" class="status-btn status-btn--ghost">
                    Kembali ke Beranda
                </a>
            </div>
        @endif

        <div class="status-trust">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Transaksi aman & terenkripsi standar perbankan</span>
        </div>

    </div>
</main>

<script>
    const shareBtn = document.getElementById('shareBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', async function () {
            const shareData = {
                title: 'OrangBaik.id',
                text: 'Aku baru aja berdonasi lewat OrangBaik.id, yuk sama-sama berbuat baik!',
                url: window.location.origin,
            };
            try {
                if (navigator.share) {
                    await navigator.share(shareData);
                } else {
                    await navigator.clipboard.writeText(shareData.url);
                    shareBtn.innerHTML = '<i class="bi bi-check2"></i> Link Disalin!';
                    setTimeout(function () {
                        shareBtn.innerHTML = '<i class="bi bi-share-fill"></i> Ajak Teman Berdonasi Juga';
                    }, 2000);
                }
            } catch (e) {
                // user membatalkan share, biarkan saja
            }
        });
    }
</script>

</body>
</html>