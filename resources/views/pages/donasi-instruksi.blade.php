<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruksi Pembayaran - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            padding: 2rem 1rem;
            margin: 0;
        }
        .instruction-container {
            max-width: 620px;
            margin: 0 auto;
        }
        .header-nav {
            margin-bottom: 1.5rem;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .back-btn:hover { color: #0f172a; }

        .card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 1.75rem;
            margin-bottom: 1.5rem;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-settlement { background: #dcfce7; color: #166534; }
        .status-failed { background: #fee2e2; color: #991b1b; }

        .amount-box {
            background: #f0fdf4;
            border: 1.5px dashed #86efac;
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
            margin: 1.25rem 0;
        }
        .amount-label {
            font-size: 0.8125rem;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }
        .amount-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: #15803d;
            margin: 0.25rem 0 0;
        }

        .bank-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            margin: 1.25rem 0;
        }
        .bank-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.625rem 0;
            border-bottom: 1px solid #edf2f7;
            font-size: 0.875rem;
        }
        .bank-row:last-child { border-bottom: none; }
        .bank-label { color: #64748b; }
        .bank-val { font-weight: 600; color: #0f172a; text-align: right; }

        .copy-btn {
            background: #e2e8f0;
            border: none;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.75rem;
            cursor: pointer;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 6px;
        }
        .copy-btn:hover { background: #cbd5e1; }

        .upload-section {
            border-top: 1px solid #e2e8f0;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
        }
        .file-input-wrapper {
            margin-top: 0.75rem;
        }
        .btn-upload {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            margin-top: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-upload:hover { background: #1d4ed8; }

        .alert {
            padding: 0.875rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }

        .steps-list {
            margin: 1rem 0 0;
            padding-left: 1.25rem;
            color: #475569;
            font-size: 0.875rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="instruction-container">

    <div class="header-nav">
        <a href="{{ route('donasi') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Campaign
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-x-circle-fill"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <span style="font-size:0.8125rem; color:#64748b;">Order ID: <code>{{ $pembayaran->order_id }}</code></span>
                <h2 style="font-size:1.25rem; margin:0.25rem 0 0; font-weight:700;">Instruksi Pembayaran</h2>
            </div>
            <span class="status-badge status-{{ $pembayaran->transaction_status }}">
                @if ($pembayaran->transaction_status === 'settlement')
                    <i class="bi bi-check-circle"></i> Berhasil
                @elseif ($pembayaran->transaction_status === 'pending')
                    <i class="bi bi-hourglass-split"></i> Menunggu Pembayaran
                @else
                    <i class="bi bi-x-circle"></i> Gagal / Kedaluwarsa
                @endif
            </span>
        </div>

        <div class="amount-box">
            <div class="amount-label">Jumlah yang Harus Ditransfer</div>
            <div class="amount-value" id="nominal-transfer">Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</div>
            <small style="color:#166534; font-size:0.75rem; display:block; margin-top:4px;">
                Mohon transfer tepat sampai digit terakhir
            </small>
        </div>

        {{-- Detail Rekening / VA --}}
        <div class="bank-info">
            <div class="bank-row">
                <span class="bank-label">Campaign</span>
                <span class="bank-val">{{ $donasi->campaign->judul ?? 'Donasi' }}</span>
            </div>
            <div class="bank-row">
                <span class="bank-label">Metode Pembayaran</span>
                <span class="bank-val">
                    {{ $pembayaran->paymentChannel->name ?? $pembayaran->payment_type }}
                    @if ($pembayaran->paymentChannel?->gateway)
                        <small style="color:#64748b;">({{ $pembayaran->paymentChannel->gateway->name }})</small>
                    @endif
                </span>
            </div>
            <div class="bank-row">
                <span class="bank-label">
                    {{ $pembayaran->payment_type === 'va' ? 'Nomor Virtual Account' : 'Nomor Rekening' }}
                </span>
                <span class="bank-val">
                    <span id="rekening-val">
                        {{ $pembayaran->paymentChannel->account_number ?? $pembayaran->gateway_response['account_number'] ?? '-' }}
                    </span>
                    @if ($pembayaran->paymentChannel?->account_number || isset($pembayaran->gateway_response['account_number']))
                        <button class="copy-btn" type="button" onclick="copyRekening()">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    @endif
                </span>
            </div>
            <div class="bank-row">
                <span class="bank-label">Atas Nama</span>
                <span class="bank-val">{{ $pembayaran->paymentChannel->account_name ?? 'OrangBaik' }}</span>
            </div>
            @if (!empty($pembayaran->gateway_response['link_url']))
                <div class="bank-row" style="background:#eff6ff; padding:0.75rem 1rem; border-radius:8px; margin-top:0.5rem;">
                    <span class="bank-label" style="color:#1e40af; font-weight:600;">Halaman Pembayaran Flip:</span>
                    <span class="bank-val">
                        <a href="{{ $pembayaran->gateway_response['link_url'] }}" target="_blank" class="copy-btn" style="background:#2563eb; color:#fff; text-decoration:none; padding:6px 12px; font-weight:600;">
                            <i class="bi bi-box-arrow-up-right"></i> Buka Halaman Flip
                        </a>
                    </span>
                </div>
            @endif
        </div>

        {{-- Langkah Pembayaran --}}
        <div>
            <h4 style="font-size:0.9375rem; font-weight:600; margin:0;">Petunjuk Pembayaran:</h4>
            <ol class="steps-list">
                @if ($pembayaran->payment_type === 'va')
                    <li>Buka aplikasi Mobile Banking / Internet Banking / ATM bank Anda.</li>
                    <li>Pilih menu <strong>Transfer / Pembayaran</strong> &gt; <strong>Virtual Account</strong>.</li>
                    <li>Masukkan nomor Virtual Account di atas.</li>
                    <li>Periksa kembali nominal dan nama penerima, lalu konfirmasi pembayaran.</li>
                    <li>Status donasi akan terverifikasi secara otomatis setelah pembayaran berhasil.</li>
                @else
                    <li>Transfer dana sejumlah <strong>Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</strong> ke nomor rekening di atas.</li>
                    <li>Simpan bukti transfer berupa struk / screenshot m-banking.</li>
                    <li>Unggah foto/tangkapan layar bukti transfer pada formulir di bawah ini.</li>
                    <li>Tim OrangBaik akan memverifikasi pembayaran Anda dalam waktu 1x24 jam.</li>
                @endif
            </ol>
        </div>

        {{-- Bagian Upload Bukti Transfer (Khusus Transfer Manual) --}}
        @if ($pembayaran->payment_type === 'transfer' || $pembayaran->isManualTransfer())
            <div class="upload-section">
                <h4 style="font-size:1rem; font-weight:700; margin:0 0 0.5rem;">Upload Bukti Transfer</h4>

                @if ($pembayaran->bukti_transfer)
                    <div style="background:#f1f5f9; padding:1rem; border-radius:10px; margin-bottom:1rem;">
                        <span style="font-size:0.8125rem; color:#475569; display:block; margin-bottom:0.5rem;">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i> Bukti transfer sudah diunggah:
                        </span>
                        <a href="{{ asset('storage/' . $pembayaran->bukti_transfer) }}" target="_blank">
                            <img src="{{ asset('storage/' . $pembayaran->bukti_transfer) }}" alt="Bukti Transfer"
                                style="max-height:160px; border-radius:8px; border:1px solid #cbd5e1; background:#fff;">
                        </a>
                        @if ($pembayaran->transaction_status === 'pending')
                            <p style="font-size:0.8125rem; color:#64748b; margin:0.5rem 0 0;">
                                Ingin mengganti bukti transfer? Pilih file baru di bawah dan klik upload ulang.
                            </p>
                        @endif
                    </div>
                @endif

                @if ($pembayaran->transaction_status === 'pending')
                    <form action="{{ route('donasi.bayar.upload_bukti', $pembayaran->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="file-input-wrapper">
                            <input type="file" name="bukti_transfer" accept="image/jpeg,image/png,image/jpg,image/webp" required
                                style="font-size:0.875rem; width:100%;">
                            <small style="color:#64748b; font-size:0.75rem; display:block; margin-top:4px;">
                                Format: JPG, PNG, WEBP (Maksimal 5MB)
                            </small>
                        </div>
                        <button type="submit" class="btn-upload">
                            <i class="bi bi-cloud-arrow-up"></i>
                            {{ $pembayaran->bukti_transfer ? 'Upload Ulang Bukti Transfer' : 'Kirim Bukti Transfer' }}
                        </button>
                    </form>
                @elseif ($pembayaran->transaction_status === 'settlement')
                    <div class="alert alert-success" style="margin-top:0.75rem;">
                        <i class="bi bi-check-circle-fill"></i> Pembayaran telah diverifikasi oleh admin. Terima kasih atas donasi Anda!
                    </div>
                @endif
            </div>
        @endif

    </div>

</div>

<script>
function copyRekening() {
    const rek = document.getElementById('rekening-val').textContent.trim();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(rek).then(() => {
            alert('Nomor rekening berhasil disalin: ' + rek);
        });
    } else {
        const temp = document.createElement('textarea');
        temp.value = rek;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        alert('Nomor rekening berhasil disalin: ' + rek);
    }
}
</script>

</body>
</html>
