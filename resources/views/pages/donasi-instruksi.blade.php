<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Instruksi Pembayaran - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-instruksi.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    {{-- HERO BANNER --}}
    <div class="instruksi-hero">
        <div class="instruksi-hero-inner">
            <button class="back-button back-button--light" type="button" onclick="history.back()">
                <svg viewBox="0 0 24 24" aria-hidden="true" width="18" height="18">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </svg>
                <span>Kembali</span>
            </button>
            <span class="hero-eyebrow"><i class="bi bi-heart-fill"></i> Donasi untuk</span>
            <h1 class="hero-campaign">{{ $donasi->campaign->judul ?? 'Campaign' }}</h1>
        </div>
    </div>

    <main class="instruksi-page">
        <div class="instruksi-container">

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

            {{-- TICKET CARD --}}
            <div class="ticket-card">

                {{-- ATAS: nominal jadi fokus utama --}}
                <div class="ticket-top">
                    <div class="ticket-top-row">
                        <span class="order-meta">Order ID <code>{{ $pembayaran->order_id }}</code></span>
                        <span class="status-pill status-pill--{{ $pembayaran->transaction_status }}">
                            <i class="status-dot"></i>
                            @if ($pembayaran->transaction_status === 'settlement')
                                Berhasil
                            @elseif ($pembayaran->transaction_status === 'pending')
                                Menunggu Pembayaran
                            @else
                                Gagal / Kedaluwarsa
                            @endif
                        </span>
                    </div>

                    <div class="amount-hero">
                        <span class="amount-hero-label">Total yang perlu dibayar</span>
                        <span class="amount-hero-value" id="nominal-transfer">Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</span>
                        <span class="amount-hero-hint">Transfer tepat sampai digit terakhir ya, biar kebaikanmu langsung terverifikasi 💚</span>
                    </div>

                    {{-- CTA UTAMA --}}
                    @php
                        $flipPaymentUrl =
                            $pembayaran->gateway_response['payment_url']
                            ?? $pembayaran->gateway_response['link_url']
                            ?? null;
                        $hasSnapPay = !empty($pembayaran->snap_token) && $pembayaran->transaction_status === 'pending';
                    @endphp

                    @if ($hasSnapPay)
                        <button type="button" id="pay-snap-btn" class="cta-main">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>Bayar Sekarang</span>
                        </button>
                        <p class="cta-sub"><i class="bi bi-shield-check"></i> GoPay, QRIS, Virtual Account & lainnya — 1 klik</p>
                    @elseif ($flipPaymentUrl)
                        <a href="{{ $flipPaymentUrl }}" target="_blank" rel="noopener noreferrer" class="cta-main">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span>Lanjutkan ke Halaman Pembayaran</span>
                        </a>
                        <p class="cta-sub"><i class="bi bi-shield-check"></i> Kamu akan diarahkan ke halaman pembayaran Flip</p>
                    @endif
                </div>

                {{-- PERFORASI ala tiket --}}
                <div class="ticket-perforation"><span></span></div>

                {{-- BAWAH: detail & instruksi --}}
                <div class="ticket-bottom">

                    <div class="detail-list">
                        <div class="detail-row">
                            <span class="detail-label">Metode Pembayaran</span>
                            <span class="detail-val">
                                {{ $pembayaran->paymentChannel->name ?? $pembayaran->payment_type }}
                                @if ($pembayaran->paymentChannel?->gateway)
                                    <small class="detail-sub">({{ $pembayaran->paymentChannel->gateway->name }})</small>
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                {{ $pembayaran->payment_type === 'va' ? 'Nomor Virtual Account' : 'Nomor Rekening' }}
                            </span>
                            <span class="detail-val">
                                <span id="rekening-val" class="detail-mono">
                                    {{ $pembayaran->paymentChannel->account_number ?? $pembayaran->gateway_response['account_number'] ?? '-' }}
                                </span>
                                @if ($pembayaran->paymentChannel?->account_number || isset($pembayaran->gateway_response['account_number']))
                                    <button class="copy-chip" type="button" id="copy-rekening-btn" onclick="copyRekening()">
                                        <i class="bi bi-clipboard"></i> <span>Salin</span>
                                    </button>
                                @endif
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Atas Nama</span>
                            <span class="detail-val">{{ $pembayaran->paymentChannel->account_name ?? 'OrangBaik' }}</span>
                        </div>
                    </div>

                    {{-- LANGKAH PEMBAYARAN --}}
                    <div class="steps-section">
                        <h2 class="section-title"><i class="bi bi-list-check"></i> Cara Menyelesaikan Pembayaran</h2>
                        <ol class="steps-timeline">
                            @if ($pembayaran->payment_type === 'va')
                                <li>Buka aplikasi Mobile Banking / Internet Banking / ATM bank Anda.</li>
                                <li>Pilih menu <strong>Transfer / Pembayaran</strong> &gt; <strong>Virtual Account</strong>.</li>
                                <li>Masukkan nomor Virtual Account di atas.</li>
                                <li>Periksa kembali nominal dan nama penerima, lalu konfirmasi pembayaran.</li>
                                <li>Status donasi akan terverifikasi secara otomatis setelah pembayaran berhasil.</li>
                            @elseif (!empty($pembayaran->snap_token))
                                <li>Klik tombol <strong>Bayar Sekarang</strong> di atas untuk membuka jendela pembayaran Midtrans.</li>
                                <li>Pilih metode pembayaran yang Anda inginkan (GoPay, ShopeePay, QRIS, Virtual Account, dll.).</li>
                                <li>Selesaikan pembayaran sesuai instruksi pada layar Midtrans.</li>
                                <li>Status donasi akan terverifikasi secara otomatis setelah pembayaran berhasil.</li>
                            @else
                                <li>Transfer dana sejumlah <strong>Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</strong> ke nomor rekening di atas.</li>
                                <li>Simpan bukti transfer berupa struk / screenshot m-banking.</li>
                                <li>Unggah foto/tangkapan layar bukti transfer pada formulir di bawah ini.</li>
                                <li>Tim OrangBaik akan memverifikasi pembayaran Anda dalam waktu 1x24 jam.</li>
                            @endif
                        </ol>
                    </div>

                    {{-- UPLOAD BUKTI TRANSFER (KHUSUS TRANSFER MANUAL) --}}
                    @if ($pembayaran->payment_type === 'transfer' || $pembayaran->isManualTransfer())
                        <div class="upload-section">
                            <h2 class="section-title"><i class="bi bi-cloud-arrow-up-fill"></i> Upload Bukti Transfer</h2>

                            @if ($pembayaran->bukti_transfer)
                                <div class="upload-existing">
                                    <span class="upload-existing-label">
                                        <i class="bi bi-check-circle-fill"></i> Bukti transfer sudah diunggah
                                    </span>
                                    <a href="{{ asset('storage/' . $pembayaran->bukti_transfer) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $pembayaran->bukti_transfer) }}" alt="Bukti Transfer" class="upload-preview">
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
                                <div class="alert alert--success">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Pembayaran telah diverifikasi oleh admin. Terima kasih atas donasi Anda!</span>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>

            <div class="instruksi-trust">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Transaksi aman & terenkripsi standar perbankan</span>
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
                <div class="sticky-cta-amount">Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</div>
                <button type="button" id="pay-snap-btn-mobile" class="cta-main cta-main--sticky">
                    <i class="bi bi-lightning-charge-fill"></i> Bayar Sekarang
                </button>
            </div>
        </div>
    @elseif ($flipPaymentUrl)
        <div class="sticky-cta">
            <div class="sticky-cta-inner">
                <div class="sticky-cta-amount">Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</div>
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
        function copyRekening() {
            const rek = document.getElementById('rekening-val').textContent.trim();
            const btn = document.getElementById('copy-rekening-btn');
            const label = btn?.querySelector('span');
            const finish = () => {
                if (!btn || !label) return;
                const original = label.textContent;
                btn.classList.add('copy-chip--done');
                label.textContent = 'Disalin!';
                setTimeout(() => {
                    btn.classList.remove('copy-chip--done');
                    label.textContent = original;
                }, 1800);
            };

            if (navigator.clipboard) {
                navigator.clipboard.writeText(rek).then(finish);
            } else {
                const temp = document.createElement('textarea');
                temp.value = rek;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                finish();
            }
        }

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