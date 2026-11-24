<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Instruksi Pembayaran - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-instruksi.css') }}?v=16">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="pay-body">

    @php
        $flipPaymentUrl =
            $pembayaran->gateway_response['payment_url']
            ?? $pembayaran->gateway_response['link_url']
            ?? null;
        $hasSnapPay = !empty($pembayaran->snap_token) && $pembayaran->transaction_status === 'pending';

        // presentasi saja: kelompokkan status ke 3 varian tampilan
        $statusKey = match ($pembayaran->transaction_status) {
            'settlement' => 'settlement',
            'pending'    => 'pending',
            default      => 'failed',
        };
        $statusLabel = match ($statusKey) {
            'settlement' => 'Berhasil',
            'pending'    => 'Menunggu Pembayaran',
            default      => 'Gagal / Kedaluwarsa',
        };
        $statusNote = match ($statusKey) {
            'settlement' => 'Terima kasih, donasimu sudah kami terima.',
            'pending'    => 'Selesaikan pembayaran agar donasimu terverifikasi.',
            default      => 'Pembayaran tidak dapat dilanjutkan. Silakan buat donasi baru.',
        };
        $statusIcon = match ($statusKey) {
            'settlement' => 'bi-check-circle-fill',
            'pending'    => 'bi-clock-fill',
            default      => 'bi-x-circle-fill',
        };
        $methodIcon = match ($pembayaran->payment_type) {
            'va'       => 'bi-bank2',
            'transfer' => 'bi-bank',
            default    => 'bi-wallet2',
        };

        $nominalFormatted = number_format($donasi->nominal, 0, ',', '.');
        $nominalRaw = number_format($donasi->nominal, 0, '', '');
        $nomorRekening = $pembayaran->paymentChannel->account_number ?? $pembayaran->gateway_response['account_number'] ?? null;
        $namaMetode = $pembayaran->paymentChannel->name ?? $pembayaran->payment_type;
    @endphp

    {{-- TOPBAR (sama dengan halaman donasi-bayar) --}}
    <header class="payment-topbar" id="payment-topbar">
        <div class="payment-topbar-inner">
            <button class="payment-topbar-back" type="button" onclick="history.back()" aria-label="Kembali">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="payment-topbar-text">
                <h1 class="payment-topbar-title">Instruksi Pembayaran</h1>
            </div>
        </div>
    </header>

    <main class="pay-page">
        <div class="pay-container">

            @if (session('success') || session('error'))
                <div class="pay-alerts">
                    @if (session('success'))
                        <div class="alert alert--success">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert--danger">
                            <i class="bi bi-x-circle-fill"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif
                </div>
            @endif

            <div class="pay-layout">

                {{-- ============ HERO: STATUS + NOMINAL ============ --}}
                <section class="pay-hero pay-hero--{{ $statusKey }}">

                    <div class="hero-top">
                        <span class="hero-pill"><i class="bi {{ $statusIcon }}"></i> {{ $statusLabel }}</span>
                        <span class="hero-order">{{ $pembayaran->order_id }}</span>
                    </div>

                    <span class="hero-label">Total yang perlu dibayar</span>
                    <div class="hero-amount-row">
                        <span class="hero-amount" id="nominal-transfer">Rp {{ $nominalFormatted }}</span>
                        <button class="copy-chip" type="button" id="copy-nominal-btn" data-copy="{{ $nominalRaw }}" aria-label="Salin nominal">
                            <i class="bi bi-clipboard"></i> <span>Salin</span>
                        </button>
                    </div>
                    <p class="hero-hint">{{ $statusNote }}</p>

                    <div class="hero-campaign">
                        <i class="bi bi-heart-fill"></i>
                        <div>
                            <small>Donasi untuk</small>
                            <strong>{{ $donasi->campaign->judul ?? 'Campaign' }}</strong>
                        </div>
                    </div>

                    {{-- CTA desktop (mobile/tablet pakai bar bawah) --}}
                    @if ($hasSnapPay)
                        <button type="button" id="pay-snap-btn" class="cta-main">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>Bayar Sekarang</span>
                        </button>
                    @elseif ($flipPaymentUrl)
                        <a href="{{ $flipPaymentUrl }}" target="_blank" rel="noopener noreferrer" class="cta-main">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span>Lanjutkan ke Halaman Pembayaran</span>
                        </a>
                    @endif
                </section>

                {{-- ============ KONTEN ============ --}}
                <div class="pay-content">

                    {{-- METODE + REKENING --}}
                    <section class="pay-block">
                        <div class="method-line">
                            <span class="method-icon"><i class="bi {{ $methodIcon }}"></i></span>
                            <div class="method-text">
                                <span class="method-label">Metode pembayaran</span>
                                <strong class="method-name">{{ $namaMetode }}</strong>
                            </div>
                        </div>

                        @if ($nomorRekening)
                            <div class="account">
                                <span class="account-label">
                                    {{ $pembayaran->payment_type === 'va' ? 'Nomor Virtual Account' : 'Nomor Rekening' }}
                                </span>
                                <div class="account-row">
                                    <span id="rekening-val" class="account-number">{{ $nomorRekening }}</span>
                                    <button class="copy-chip" type="button" id="copy-rekening-btn" onclick="copyRekening()">
                                        <i class="bi bi-clipboard"></i> <span>Salin</span>
                                    </button>
                                </div>
                                <span class="account-name">a.n. {{ $pembayaran->paymentChannel->account_name ?? 'OrangBaik' }}</span>
                            </div>
                        @else
                            {{-- elemen ini tetap ada agar JS copyRekening tidak error --}}
                            <span id="rekening-val" hidden>-</span>
                        @endif
                    </section>

                    {{-- CARA BAYAR --}}
                    <details class="pay-block steps-block" open>
                        <summary>
                            <h2 class="block-title"><i class="bi bi-list-check"></i> Cara Pembayaran</h2>
                            <i class="bi bi-chevron-down toggle-chevron"></i>
                        </summary>
                        <ol class="steps">
                            @if ($pembayaran->payment_type === 'va')
                                <li>Buka aplikasi m-banking, internet banking, atau ATM.</li>
                                <li>Pilih <strong>Transfer</strong> &gt; <strong>Virtual Account</strong>.</li>
                                <li>Masukkan nomor Virtual Account di atas, lalu periksa nominalnya.</li>
                                <li>Konfirmasi pembayaran. Status donasi terverifikasi otomatis.</li>
                            @elseif (!empty($pembayaran->snap_token))
                                <li>Klik tombol <strong>Bayar Sekarang</strong>.</li>
                                <li>Pilih metode pembayaran (GoPay, ShopeePay, QRIS, Virtual Account, dll).</li>
                                <li>Selesaikan pembayaran sesuai instruksi di layar. Status donasi terverifikasi otomatis.</li>
                            @else
                                <li>Transfer sejumlah <strong>Rp {{ $nominalFormatted }}</strong> ke rekening di atas.</li>
                                <li>Simpan bukti transfer (struk atau screenshot).</li>
                                <li>Unggah bukti transfer pada formulir di bawah.</li>
                                <li>Tim OrangBaik memverifikasi dalam 1x24 jam.</li>
                            @endif
                        </ol>
                    </details>

                    {{-- UPLOAD BUKTI TRANSFER (KHUSUS TRANSFER MANUAL) --}}
                    @if ($pembayaran->payment_type === 'transfer' || $pembayaran->isManualTransfer())
                        <section class="pay-block">
                            <h2 class="block-title"><i class="bi bi-cloud-arrow-up-fill"></i> Upload Bukti Transfer</h2>

                            @if ($pembayaran->bukti_transfer)
                                <div class="upload-existing">
                                    <span class="upload-existing-label">
                                        <i class="bi bi-check-circle-fill"></i> Bukti transfer sudah diunggah
                                    </span>
                                    <a href="{{ route('donasi.bayar.bukti_transfer', ['pembayaran' => $pembayaran->id, 'token' => request()->query('token')]) }}" target="_blank" rel="noopener noreferrer">
                                        <img src="{{ route('donasi.bayar.bukti_transfer', ['pembayaran' => $pembayaran->id, 'token' => request()->query('token')]) }}" alt="Bukti Transfer" class="upload-preview">
                                    </a>
                                    @if ($pembayaran->transaction_status === 'pending')
                                        <p class="upload-hint">Ingin mengganti? Pilih file baru di bawah.</p>
                                    @endif
                                </div>
                            @endif

                            @if ($pembayaran->transaction_status === 'pending')
                                <form action="{{ route('donasi.bayar.upload_bukti', ['pembayaran' => $pembayaran->id, 'token' => request()->query('token')]) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ request()->query('token') }}">
                                    <label class="file-drop" for="bukti-input">
                                        <i class="bi bi-image"></i>
                                        <span class="file-drop-text">Pilih foto bukti transfer</span>
                                        <small class="upload-hint">JPG, PNG, WEBP &middot; maks. 5MB</small>
                                        <input id="bukti-input" type="file" name="bukti_transfer" accept="image/jpeg,image/png,image/jpg,image/webp" required>
                                    </label>
                                    <button type="submit" class="btn-upload">
                                        <i class="bi bi-send-fill"></i>
                                        {{ $pembayaran->bukti_transfer ? 'Upload Ulang Bukti Transfer' : 'Kirim Bukti Transfer' }}
                                    </button>
                                </form>
                            @elseif ($pembayaran->transaction_status === 'settlement')
                                <div class="alert alert--success alert--flush">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Pembayaran sudah diverifikasi. Terima kasih atas donasimu!</span>
                                </div>
                            @endif
                        </section>
                    @endif

                </div>
            </div>

            {{-- spacer biar konten terakhir ga ketutup sticky bar mobile --}}
            @if ($hasSnapPay || $flipPaymentUrl)
                <div class="sticky-cta-spacer"></div>
            @endif

        </div>
    </main>

    {{-- STICKY CTA (khusus mobile & tablet) --}}
    @if ($hasSnapPay)
        <div class="sticky-cta">
            <div class="sticky-cta-inner">
                <div class="sticky-cta-amount">
                    <small>Total</small>
                    <strong>Rp {{ $nominalFormatted }}</strong>
                </div>
                <button type="button" id="pay-snap-btn-mobile" class="cta-main cta-main--sticky">
                    <i class="bi bi-lightning-charge-fill"></i> Bayar Sekarang
                </button>
            </div>
        </div>
    @elseif ($flipPaymentUrl)
        <div class="sticky-cta">
            <div class="sticky-cta-inner">
                <div class="sticky-cta-amount">
                    <small>Total</small>
                    <strong>Rp {{ $nominalFormatted }}</strong>
                </div>
                <a href="{{ $flipPaymentUrl }}" target="_blank" rel="noopener noreferrer" class="cta-main cta-main--sticky">
                    <i class="bi bi-box-arrow-up-right"></i> Lanjutkan Bayar
                </a>
            </div>
        </div>
    @endif

    @if ($hasSnapPay)
        @php
            $midtransGateway = $pembayaran->paymentChannel?->gateway;
            $isMidtransProd = (bool) ($midtransGateway?->getConfigValue('is_production', false))
                && app()->environment('production');
            $midtransClientKey = $midtransGateway?->getConfigValue('client_key') ?? '';
        @endphp
        @if (!empty($midtransClientKey))
            <script src="{{ $isMidtransProd ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
                data-client-key="{{ $midtransClientKey }}"></script>
            <script>
                function payWithSnap() {
                    snap.pay('{{ $pembayaran->snap_token }}', {
                        onSuccess: function() { window.location.href = '{{ route("donasi.status", "sukses") }}'; },
                        onPending: function() { window.location.href = '{{ route("donasi.status", "pending") }}'; },
                        onError: function() { window.location.href = '{{ route("donasi.status", "gagal") }}'; },
                        onClose: function() {}
                    });
                }
                document.getElementById('pay-snap-btn')?.addEventListener('click', payWithSnap);
                document.getElementById('pay-snap-btn-mobile')?.addEventListener('click', payWithSnap);
            </script>
        @endif
    @endif

    <script>
        function copyToClipboard(text, btn) {
            const label = btn?.querySelector('span');
            const finish = () => {
                if (!btn || !label) return;
                const original = label.textContent;
                btn.classList.add('is-copied');
                label.textContent = 'Disalin!';
                setTimeout(() => {
                    btn.classList.remove('is-copied');
                    label.textContent = original;
                }, 1800);
            };

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(finish);
            } else {
                const temp = document.createElement('textarea');
                temp.value = text;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                finish();
            }
        }

        function copyRekening() {
            const rek = document.getElementById('rekening-val').textContent.trim();
            copyToClipboard(rek, document.getElementById('copy-rekening-btn'));
        }

        document.getElementById('copy-nominal-btn')?.addEventListener('click', function() {
            copyToClipboard(this.dataset.copy, this);
        });

        // Bayangan topbar saat halaman di-scroll
        (function () {
            const bar = document.getElementById('payment-topbar');
            if (!bar) return;
            const onScroll = () => bar.classList.toggle('is-scrolled', window.scrollY > 4);
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        })();

        // Preview nama file yang dipilih di file-drop
        document.getElementById('bukti-input')?.addEventListener('change', function() {
            const textEl = this.closest('.file-drop')?.querySelector('.file-drop-text');
            if (textEl && this.files?.[0]) {
                textEl.textContent = this.files[0].name;
            }
        });
    </script>

</body>

</html>