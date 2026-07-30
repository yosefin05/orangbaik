<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Donasi - OrangBaik.id</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        .card {
            background: white;
            padding: 40px 30px;
            border-radius: 16px;
            text-align: center;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        .icon { font-size: 60px; }
        h1 { margin: 15px 0 10px; }
        p { color: #4b5563; margin: 5px 0 20px; }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 24px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
        }
        .btn:hover { background: #1d4ed8; }
        .btn-bayar {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 14px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        .btn-bayar:hover { background: #c0392b; }
        .btn-bayar:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .loading {
            margin-top: 15px;
            color: #888;
            font-size: 14px;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #e74c3c;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="card">
        @if($status == 'sukses')
            <div class="icon">✅</div>
            <h1 style="color: #16a34a;">Donasi Berhasil!</h1>
            <p>Terima kasih atas donasi Anda. Semoga kebaikan ini membawa berkah.</p>
            <p><a href="/" class="btn">Kembali ke Beranda</a></p>

        @elseif($status == 'pending')
            <div class="icon">⏳</div>
            <h1 style="color: #f59e0b;">Selesaikan Pembayaran</h1>
            <p>Klik tombol di bawah untuk melanjutkan ke halaman pembayaran.</p>
            
            <button id="pay-button" class="btn-bayar">🛡 Bayar Sekarang</button>
            <div id="loading-text" class="loading" style="display:none;">
                <span class="spinner"></span> Memproses...
            </div>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                ⚡ Tersedia: Kartu Kredit, Virtual Account, E-Wallet, QRIS, dan lainnya.
            </p>

        @elseif($status == 'gagal')
            <div class="icon">❌</div>
            <h1 style="color: #dc2626;">Donasi Gagal</h1>
            <p>Pembayaran Anda tidak berhasil. Silakan coba lagi.</p>
            <p>
                <a href="javascript:history.back()" class="btn" style="background: #dc2626;">Coba Lagi</a>
                <a href="/" class="btn" style="background: #6b7280;">Beranda</a>
            </p>
        @endif
    </div>

    <!-- Midtrans Snap -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.clientKey') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const payButton = document.getElementById('pay-button');
            const loadingText = document.getElementById('loading-text');
            
            // Ambil snap_token dari session
            const snapToken = '{{ session('snap_token') }}';
            const donasiId = '{{ session('donasi_id') }}';

            if (payButton && snapToken) {
                payButton.addEventListener('click', function() {
                    payButton.disabled = true;
                    payButton.textContent = '⏳ Menghubungkan...';
                    loadingText.style.display = 'block';

                    snap.pay(snapToken, {
                        onSuccess: function(result) {
                            window.location.href = '{{ route("donasi.status", "sukses") }}';
                        },
                        onPending: function(result) {
                            // Tunggu webhook
                            window.location.href = '{{ route("donasi.status", "sukses") }}';
                        },
                        onError: function(result) {
                            window.location.href = '{{ route("donasi.status", "gagal") }}';
                        },
                        onClose: function() {
                            payButton.disabled = false;
                            payButton.textContent = '🛡 Bayar Sekarang';
                            loadingText.style.display = 'none';
                            alert('Anda menutup popup pembayaran.');
                        }
                    });
                });
            } else if (payButton && !snapToken) {
                payButton.disabled = true;
                payButton.textContent = '❌ Token tidak ditemukan';
            }
        });
    </script>
</body>
</html>