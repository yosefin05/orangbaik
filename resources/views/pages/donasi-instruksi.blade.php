<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Instruksi Pembayaran - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-instruksi.css') }}?v=5">
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
            'pending'    => 'Selesaikan pembayaran agar donasimu langsung terverifikasi.',
            default      => 'Pembayaran tidak dapat dilanjutkan. Silakan buat donasi baru.',
        };
        $statusIcon = match ($statusKey) {
            'settlement' => 'bi-check-circle-fill',
            'pending'    => 'bi-clock-fill',
            default      => 'bi-x-circle-fill',
        };

        $nominalFormatted = number_format($donasi->nominal, 0, ',', '.');
        $nominalRaw = number_format($donasi->nominal, 0, '', '');
        $nomorRekening = $pembayaran->paymentChannel->account_number ?? $pembayaran->gateway_response['account_number'] ?? null;
        $namaMetode = $pembayaran->paymentChannel->name ?? $pembayaran->payment_type;
        $inisialMetode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $namaMetode, 0, 3));
    @endphp

    {{-- TOPBAR --}}
    <header class="pay-header">
        <div class="pay-header-inner">
            <button class="pay-back" type="button" onclick="history.back()" aria-label="Kembali">
                <svg viewBox="0 0 24 24" aria-hidden="true" width="20" height="20">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
            </button>
            <div class="pay-header-text">
                <span class="pay-header-eyebrow">Instruksi Pembayaran</span>
                <span class="pay-header-brand">OrangBaik.id</span>
            </div>
        </div>
    </header>

    <main class="pay-page">
        <div class="pay-container">

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

            <div class="pay-layout">

                {{-- ============ RINGKASAN (hero gradient) ============ --}}
                <aside class="pay-side">
                    <section class="pay-summary pay-summary--{{ $statusKey }}">

                        <span class="summary-status">
                            <i class="bi {{ $statusIcon }}"></i> {{ $statusLabel }}
                        </span>

                        <span class="summary-label">Total yang perlu dibayar</span>
                        <div class="summary-amount-row">
                            <span class="summary-amount" id="nominal-transfer">Rp {{ $nominalFormatted }}</span>
                            <button class="summary-copy" type="button" id="copy-nominal-btn" data-copy="{{ $nominalRaw }}" aria-label="Salin nominal">
                                <i class="bi bi-clipboard"></i> <span>Salin</span>
                            </button>
                        </div>
                        <p class="summary-hint">{{ $statusNote }}</p>

                        <dl class="summary-meta">
                            <div>
                                <dt>Donasi untuk</dt>
                                <dd>{{ $donasi->campaign->judul ?? 'Campaign' }}</dd>
                            </div>
                            <div>
                                <dt>Order ID</dt>
                                <dd><code>{{ $pembayaran->order_id }}</code></dd>
                            </div>
                        </dl>

                        {{-- CTA desktop (mobile pakai sticky bar) --}}
                        @if ($hasSnapPay)
                            <button type="button" id="pay-snap-btn" class="cta-main">
                                <i class="bi bi-lightning-charge-fill"></i>
                                <span>Bayar Sekarang</span>
                            </button>
                            <p class="cta-sub">GoPay, QRIS, Virtual Account &amp; lainnya</p>
                        @elseif ($flipPaymentUrl)
                            <a href="{{ $flipPaymentUrl }}" target="_blank" rel="noopener noreferrer" class="cta-main">
                                <i class="bi bi-box-arrow-up-right"></i>
                                <span>Lanjutkan ke Halaman Pembayaran</span>
                            </a>
                            <p class="cta-sub">Kamu akan diarahkan ke halaman pembayaran Flip</p>
                        @endif
                    </section>

                    <p class="pay-trust">
                        <i class="bi bi-shield-lock-fill"></i>
                        Transaksi aman &amp; terenkripsi standar perbankan
                    </p>
                </aside>

                {{-- ============ DETAIL & LANGKAH ============ --}}
                <div class="pay-main">

                    {{-- METODE + REKENING --}}
                    <section class="pay-card">
                        <div class="method-row">
                            <span class="method-avatar">{{ $inisialMetode }}</span>
                            <div class="method-text">
                                <span class="method-label">Metode pembayaran</span>
                                <strong class="method-name">
                                    {{ $namaMetode }}
                                    @if ($pembayaran->paymentChannel?->gateway)
                                        <small>· {{ $pembayaran->paymentChannel->gateway->name }}</small>
                                    @endif
                                </strong>
                            </div>
                        </div>

                        @if ($nomorRekening)
                            <div class="account-box">
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

                    {{-- LANGKAH PEMBAYARAN --}}
                    <details class="pay-card steps-card" open>
                        <summary class="card-title card-title--toggle">
                            <span><i class="bi bi-list-check"></i> Cara Menyelesaikan Pembayaran</span>
                            <i class="bi bi-chevron-down toggle-chevron"></i>
                        </summary>
                        <ol class="steps">
                            @if ($pembayaran->payment_type === 'va')
                                <li>Buka aplikasi Mobile Banking / Internet Banking / ATM bank Anda.</li>
                                <li>Pilih menu <strong>Transfer / Pembayaran</strong> &gt; <strong>Virtual Account</strong>.</li>
                                <li>Masukkan nomor Virtual Account di atas.</li>
                                <li>Periksa kembali nominal dan nama penerima, lalu konfirmasi pembayaran.</li>
                                <li>Status donasi akan terverifikasi secara otomatis setelah pembayaran berhasil.</li>
                            @elseif (!empty($pembayaran->snap_token))
                                <li>Klik tombol <strong>Bayar Sekarang</strong> untuk membuka jendela pembayaran Midtrans.</li>
                                <li>Pilih metode pembayaran yang Anda inginkan (GoPay, ShopeePay, QRIS, Virtual Account, dll.).</li>
                                <li>Selesaikan pembayaran sesuai instruksi pada layar Midtrans.</li>
                                <li>Status donasi akan terverifikasi secara otomatis setelah pembayaran berhasil.</li>
                            @else
                                <li>Transfer dana sejumlah <strong>Rp {{ $nominalFormatted }}</strong> ke nomor rekening di atas.</li>
                                <li>Simpan bukti transfer berupa struk / screenshot m-banking.</li>
                                <li>Unggah foto/tangkapan layar bukti transfer pada formulir di bawah ini.</li>
                                <li>Tim OrangBaik akan memverifikasi pembayaran Anda dalam waktu 1x24 jam.</li>
                            @endif
                        </ol>
                    </details>

                    {{-- UPLOAD BUKTI TRANSFER (KHUSUS TRANSFER MANUAL) --}}
                    @if ($pembayaran->payment_type === 'transfer' || $pembayaran->isManualTransfer())
                        <section class="pay-card">
                            <h2 class="card-title"><i class="bi bi-cloud-arrow-up-fill"></i> Upload Bukti Transfer</h2>

                            @if ($pembayaran->bukti_transfer)
                                <div class="upload-existing">
                                    <span class="upload-existing-label">
                                        <i class="bi bi-check-circle-fill"></i> Bukti transfer sudah diunggah
                                    </span>
                                    <a href="{{ route('donasi.bayar.bukti_transfer', ['pembayaran' => $pembayaran->id, 'token' => request()->query('token')]) }}" target="_blank" rel="noopener noreferrer">
                                        <img src="{{ route('donasi.bayar.bukti_transfer', ['pembayaran' => $pembayaran->id, 'token' => request()->query('token')]) }}" alt="Bukti Transfer" class="upload-preview">
                                    </a>
                                    @if ($pembayaran->transaction_status === 'pending')
                                        <p class="upload-hint">Ingin mengganti bukti transfer? Pilih file baru di bawah dan klik upload ulang.</p>
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
                                        <span class="file-drop-text">Klik untuk pilih foto bukti transfer</span>
                                        <small class="upload-hint">Format: JPG, PNG, WEBP (Maksimal 5MB)</small>
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
                                    <span>Pembayaran telah diverifikasi oleh admin. Terima kasih atas donasi Anda!</span>
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

    {{-- STICKY CTA (khusus mobile) --}}
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
            $isMidtransProd = config('payment.midtrans.is_production', config('midtrans.isProduction', false));
            $midtransClientKey = config('payment.midtrans.client_key', config('midtrans.clientKey', ''));
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