<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Kwitansi {{ $donasi->pembayaran->order_id ?? 'OB-XXXX' }} - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kwitansi.css') }}">
</head>

<body>

    <div class="kwitansi-page">

        {{-- TOOLBAR (tidak ikut print) --}}
        <div class="kwitansi-toolbar no-print">
            <a href="{{ route('riwayat.donasi') }}" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                Kembali
            </a>

            <button type="button" class="btn-print" onclick="window.print()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                Cetak / Unduh PDF
            </button>
        </div>

        {{-- KWITANSI --}}
        <div class="kwitansi-wrapper">
            <div class="kwitansi-doc" id="kwitansiDoc">

                {{-- HEADER --}}
                <div class="kwitansi-header">
                    <div class="kwitansi-brand">
                        <img src="{{ asset('favicon.ico') }}" alt="OrangBaik.id" class="kwitansi-logo">
                        <div>
                            <h1>OrangBaik.id</h1>
                            <p>Platform Donasi Terpercaya</p>
                        </div>
                    </div>

                    <div class="kwitansi-title-block">
                        <div class="kwitansi-title">E-KWITANSI DONASI</div>
                        <div class="kwitansi-invoice">{{ $donasi->pembayaran->order_id ?? 'OB-XXXX' }}</div>

                        @php
                            $statusKey = 'menunggu';
                            $statusLabel = 'Menunggu';
                            if ($donasi->pembayaran) {
                                $trx = $donasi->pembayaran->transaction_status;
                                if (in_array($trx, ['settlement', 'capture'])) {
                                    $statusKey = 'selesai';
                                    $statusLabel = 'Selesai';
                                } elseif ($trx == 'pending') {
                                    $statusKey = 'menunggu';
                                    $statusLabel = 'Menunggu';
                                } elseif (in_array($trx, ['deny', 'cancel', 'expire', 'failure'])) {
                                    $statusKey = 'gagal';
                                    $statusLabel = 'Gagal';
                                }
                            }
                        @endphp

                        <span class="kwitansi-status status-{{ $statusKey }}">{{ $statusLabel }}</span>
                    </div>
                </div>

                <div class="kwitansi-divider"></div>

                {{-- NOMINAL --}}
                <div class="kwitansi-nominal-section">
                    <p class="kwitansi-nominal-label">Total Donasi</p>
                    <div class="kwitansi-nominal">Rp{{ number_format($donasi->nominal, 0, ',', '.') }}</div>
                </div>

                <div class="kwitansi-divider"></div>

                {{-- INFO DONATUR & PEMBAYARAN --}}
                <div class="kwitansi-info-grid">

                    <div class="kwitansi-section">
                        <h3>Informasi Donatur</h3>
                        <table class="kwitansi-table">
                            <tr>
                                <td>Nama</td>
                                <td>:</td>
                                <td>
                                    @if($donasi->is_anonim)
                                        <em>Hamba Allah</em>
                                    @else
                                        {{ $donasi->user->name ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>:</td>
                                <td>{{ $donasi->is_anonim ? '-' : ($donasi->user->email ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Donasi</td>
                                <td>:</td>
                                <td>{{ $donasi->created_at->translatedFormat('d F Y, H:i') }} WIB</td>
                            </tr>
                        </table>
                    </div>

                    <div class="kwitansi-section">
                        <h3>Informasi Pembayaran</h3>
                        <table class="kwitansi-table">
                            <tr>
                                <td>No. Invoice</td>
                                <td>:</td>
                                <td>{{ $donasi->pembayaran->order_id ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Metode</td>
                                <td>:</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $donasi->pembayaran->payment_type ?? '-')) }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Bayar</td>
                                <td>:</td>
                                <td>
                                    @if($donasi->pembayaran && $donasi->pembayaran->transaction_time)
                                        {{ \Carbon\Carbon::parse($donasi->pembayaran->transaction_time)->translatedFormat('d F Y, H:i') }}
                                        WIB
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>

                <div class="kwitansi-divider"></div>

                {{-- CAMPAIGN --}}
                <div class="kwitansi-section">
                    <h3>Informasi Campaign</h3>
                    <div class="kwitansi-campaign">
                        @if($donasi->campaign && $donasi->campaign->thumbnail)
                            <img src="{{ asset('storage/' . $donasi->campaign->thumbnail) }}"
                                alt="{{ $donasi->campaign->judul }}" class="kwitansi-campaign-img">
                        @endif
                        <div class="kwitansi-campaign-info">
                            <h4>{{ $donasi->campaign->judul ?? 'Campaign tidak ditemukan' }}</h4>
                            @if($donasi->campaign && $donasi->campaign->penggalangDana)
                                <p>Oleh: {{ $donasi->campaign->penggalangDana->nama_penggalang }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="kwitansi-divider"></div>

                {{-- RINGKASAN PEMBAYARAN --}}
                <div class="kwitansi-section">
                    <h3>Ringkasan Pembayaran</h3>
                    <table class="kwitansi-summary-table">
                        <tr>
                            <td>Nominal Donasi</td>
                            <td>Rp{{ number_format($donasi->nominal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Biaya Layanan</td>
                            <td>Rp0</td>
                        </tr>
                        <tr class="kwitansi-summary-total">
                            <td>Total Pembayaran</td>
                            <td>Rp{{ number_format($donasi->nominal, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="kwitansi-divider"></div>

                {{-- FOOTER --}}
                <div class="kwitansi-footer">
                    <p>Dokumen ini merupakan bukti donasi resmi yang diterbitkan oleh <strong>OrangBaik.id</strong>.</p>
                    <p>Terima kasih telah berdonasi. Kebaikanmu sangat berarti bagi sesama.</p>
                    <div class="kwitansi-footer-meta">
                        <span>Diterbitkan: {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
                        <span>orangbaik.id</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

</body>

</html>