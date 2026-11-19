<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Bukti Donasi - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kwitansi.css') }}?v=3">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @php
        // ---------- STATUS (logika sama seperti sebelumnya) ----------
        $statusKey = 'menunggu';
        if ($donasi->pembayaran) {
            $trx = $donasi->pembayaran->transaction_status;
            if (in_array($trx, ['settlement', 'capture'])) {
                $statusKey = 'selesai';
            } elseif ($trx == 'pending') {
                $statusKey = 'menunggu';
            } elseif (in_array($trx, ['deny', 'cancel', 'expire', 'failure'])) {
                $statusKey = 'gagal';
            }
        }
        $statusLabel = match ($statusKey) {
            'selesai' => 'Donasi Berhasil',
            'gagal'   => 'Pembayaran Gagal',
            default   => 'Menunggu Pembayaran',
        };
        $statusIcon = match ($statusKey) {
            'selesai' => 'bi-check-lg',
            'gagal'   => 'bi-x-lg',
            default   => 'bi-clock',
        };

        // ---------- DONATUR ----------
        $isAnon = $donasi->is_anonim || $donasi->is_anonymous;
        $namaDonatur = $isAnon ? 'Hamba Allah' : ($donasi->nama_donatur ?: ($donasi->user->name ?? 'Donatur'));
        $namaSapaan = $isAnon ? 'Sahabat Baik' : \Illuminate\Support\Str::words($namaDonatur, 2, '');
        $emailDonatur = $isAnon ? '-' : ($donasi->email ?: ($donasi->user->email ?? '-'));

        // ---------- PEMBAYARAN ----------
        $metode = $donasi->pembayaran?->paymentChannel?->name
            ?? ucfirst(str_replace('_', ' ', $donasi->pembayaran->payment_type ?? '-'));

        $tanggalBayar = null;
        if ($donasi->pembayaran?->paid_at) {
            $tanggalBayar = \Carbon\Carbon::parse($donasi->pembayaran->paid_at);
        } elseif ($donasi->pembayaran && $donasi->pembayaran->transaction_time) {
            $tanggalBayar = \Carbon\Carbon::parse($donasi->pembayaran->transaction_time);
        }

        $backUrl = url()->previous() && !str_contains(url()->previous(), 'kwitansi')
            ? url()->previous()
            : route('riwayat.donasi');
    @endphp

    {{-- TOPBAR (sama dengan halaman donasi-bayar & instruksi; tidak ikut print) --}}
    <header class="payment-topbar no-print" id="payment-topbar">
        <div class="payment-topbar-inner">
            <a class="payment-topbar-back" href="{{ $backUrl }}" aria-label="Kembali">
                <i class="bi bi-chevron-left"></i>
            </a>
            <div class="payment-topbar-text">
                <h1 class="payment-topbar-title">Bukti Donasi</h1>
            </div>
            <button type="button" class="topbar-print" onclick="window.print()">
                <i class="bi bi-download"></i> <span>Simpan</span>
            </button>
        </div>
    </header>

    <main class="kw-page">
        <div class="kw-paper is-{{ $statusKey }}" id="kwitansiDoc">

            {{-- ============ HERO ============ --}}
            <section class="kw-hero">
                <div class="kw-brand">
                    <img src="{{ asset('favicon.ico') }}" alt="OrangBaik.id">
                    OrangBaik.id
                </div>

                <div class="kw-status-icon"><i class="bi {{ $statusIcon }}"></i></div>
                <p class="kw-status">{{ $statusLabel }}</p>

                <span class="kw-amount-label">Total Donasi</span>
                <div class="kw-amount">Rp{{ number_format($donasi->nominal, 0, ',', '.') }}</div>
                <p class="kw-amount-note">Tanpa biaya layanan</p>
            </section>

            {{-- ============ DETAIL ============ --}}
            <div class="kw-content">

                {{-- CAMPAIGN --}}
                <section class="kw-block">
                    <h2 class="kw-block-title">Donasi untuk</h2>
                    <div class="kw-campaign">
                        @if($donasi->campaign && $donasi->campaign->thumbnail)
                            <img src="{{ asset('storage/' . $donasi->campaign->thumbnail) }}"
                                alt="{{ $donasi->campaign->judul }}">
                        @else
                            <span class="kw-campaign-icon"><i class="bi bi-heart-fill"></i></span>
                        @endif
                        <div>
                            <h2>{{ $donasi->campaign->judul ?? 'Campaign tidak ditemukan' }}</h2>
                            @if($donasi->campaign && $donasi->campaign->penggalangDana)
                                <p>Oleh {{ $donasi->campaign->penggalangDana->nama_penggalang }}</p>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- DONATUR --}}
                <section class="kw-block">
                    <h2 class="kw-block-title">Donatur</h2>
                    <dl class="kw-list">
                        <div>
                            <dt>Nama</dt>
                            <dd>{{ $isAnon ? 'Hamba Allah (Anonim)' : $namaDonatur }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>{{ $emailDonatur }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- PEMBAYARAN --}}
                <section class="kw-block">
                    <h2 class="kw-block-title">Pembayaran</h2>
                    <dl class="kw-list">
                        <div>
                            <dt>Nomor Donasi</dt>
                            <dd>{{ $donasi->pembayaran->order_id ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt>Metode</dt>
                            <dd>{{ $metode }}</dd>
                        </div>
                        <div>
                            <dt>Tanggal Donasi</dt>
                            <dd>{{ $donasi->created_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                        </div>
                        <div>
                            <dt>Tanggal Bayar</dt>
                            <dd>{{ $tanggalBayar ? $tanggalBayar->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- TERIMA KASIH & DOA --}}
                <section class="kw-thanks">
                    <div class="kw-thanks-icon"><i class="bi bi-heart-fill"></i></div>

                    @if($statusKey === 'selesai')
                        <h2>Terima kasih, {{ $namaSapaan }}!</h2>
                        <p>Kebaikanmu hari ini menjadi harapan baru bagi mereka yang membutuhkan. Kamu tidak sendiri, banyak orang baik yang berjalan bersamamu di OrangBaik.id.</p>
                        <p class="kw-doa">&ldquo;Semoga Allah membalas kebaikanmu dengan berlipat ganda, melapangkan rezekimu, menjaga kesehatan dan kebahagiaan keluargamu, serta menjadikan setiap langkahmu penuh keberkahan. Aamiin.&rdquo;</p>
                    @elseif($statusKey === 'gagal')
                        <h2>Niat baikmu tetap berharga, {{ $namaSapaan }}</h2>
                        <p>Pembayaran ini belum berhasil, tapi kepedulianmu sangat kami hargai. Kamu bisa berdonasi kembali kapan saja.</p>
                        <p class="kw-doa">&ldquo;Semoga niat baikmu dicatat sebagai kebaikan dan dimudahkan jalannya. Aamiin.&rdquo;</p>
                    @else
                        <h2>Sedikit lagi, {{ $namaSapaan }}</h2>
                        <p>Selesaikan pembayaranmu agar kebaikan ini sampai. Terima kasih sudah mau peduli dan berbagi.</p>
                        <p class="kw-doa">&ldquo;Semoga Allah memudahkan urusanmu dan membalas niat baikmu dengan kebaikan yang lebih besar. Aamiin.&rdquo;</p>
                    @endif

                    <a href="{{ url('/donasi') }}" class="kw-cta no-print">
                        Lanjutkan Berbagi Kebaikan <i class="bi bi-arrow-right"></i>
                    </a>
                </section>
            </div>

            {{-- ============ FOOTER ============ --}}
            <footer class="kw-footer">
                @if($statusKey === 'selesai')
                    <div class="kw-verified"><i class="bi bi-patch-check-fill"></i> Terverifikasi</div><br>
                @endif
                Bukti donasi resmi dari <strong>OrangBaik.id</strong><br>
                Diterbitkan {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </footer>

        </div>
    </main>

    <script>
        // Bayangan topbar saat halaman di-scroll
        (function () {
            const bar = document.getElementById('payment-topbar');
            if (!bar) return;
            const onScroll = () => bar.classList.toggle('is-scrolled', window.scrollY > 4);
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        })();
    </script>

</body>

</html>