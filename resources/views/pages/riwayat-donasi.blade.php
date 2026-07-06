<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Donasi - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/riwayat-donasi.css') }}">
</head>
<body>

@include('components.header')

@php
    $donations = [
        [
            'type' => 'Donasi',
            'date' => '17 Juni 2026',
            'status' => 'Selesai',
            'status_key' => 'selesai',
            'title' => 'Sedekah Makan untuk Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp300.000',
            'amount_value' => 300000,
            'method' => 'QRIS',
            'invoice' => 'OB-20260617-001',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '17 Juni 2026',
            'status' => 'Selesai',
            'status_key' => 'selesai',
            'title' => 'Bantu Pendidikan Anak Yatim',
            'organizer' => 'Yayasan OrangBaik',
            'amount' => 'Rp250.000',
            'amount_value' => 250000,
            'method' => 'Transfer Bank',
            'invoice' => 'OB-20260617-002',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '16 Juni 2026',
            'status' => 'Menunggu',
            'status_key' => 'menunggu',
            'title' => 'Sedekah Subuh untuk Dhuafa',
            'organizer' => 'Komunitas Peduli Sesama',
            'amount' => 'Rp150.000',
            'amount_value' => 150000,
            'method' => 'QRIS',
            'invoice' => 'OB-20260616-003',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '15 Juni 2026',
            'status' => 'Selesai',
            'status_key' => 'selesai',
            'title' => 'Bantuan Makanan untuk Lansia',
            'organizer' => 'Rumah Peduli Indonesia',
            'amount' => 'Rp200.000',
            'amount_value' => 200000,
            'method' => 'E-Wallet',
            'invoice' => 'OB-20260615-004',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '14 Juni 2026',
            'status' => 'Gagal',
            'status_key' => 'gagal',
            'title' => 'Bantu Renovasi Masjid Pelosok',
            'organizer' => 'Gerakan Wakaf Baik',
            'amount' => 'Rp100.000',
            'amount_value' => 100000,
            'method' => 'QRIS',
            'invoice' => 'OB-20260614-005',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '13 Juni 2026',
            'status' => 'Selesai',
            'status_key' => 'selesai',
            'title' => 'Paket Sembako untuk Keluarga Prasejahtera',
            'organizer' => 'Aksi Baik Nusantara',
            'amount' => '300.000',
            'amount_value' => 300000,
            'method' => 'Transfer Bank',
            'invoice' => 'OB-20260613-006',
            'image' => 'assets/slide1.png',
        ],
    ];

    $totalDonasi = count($donations);
    $totalNominal = collect($donations)->sum('amount_value');
    $totalSelesai = collect($donations)->where('status_key', 'selesai')->count();
    $formatRupiah = fn ($value) => 'Rp' . number_format($value, 0, ',', '.');
@endphp

<main class="history-page page-wrapper">
    <div class="container">

        <section class="history-hero">
            <div class="history-hero-content">
                <span class="section-label">
                    Donasi Saya
                </span>

                <h1 class="section-title">
                    Riwayat Donasi
                </h1>

                <p class="section-description">
                    Pantau semua donasi yang sudah kamu lakukan, lihat status transaksi,
                    dan akses e-kwitansi dengan mudah.
                </p>
            </div>

            <div class="history-hero-card">
                <span>Total Donasi</span>
                <strong>{{ $formatRupiah($totalNominal) }}</strong>
                <p>Dari {{ $totalDonasi }} transaksi donasi</p>
            </div>
        </section>

        <section class="history-summary" aria-label="Ringkasan riwayat donasi">
            <article class="summary-card">
                <span class="summary-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 7H20V17H4V7Z" />
                        <path d="M7 10H7.01" />
                        <path d="M17 14H12" />
                    </svg>
                </span>

                <div>
                    <span>Total Transaksi</span>
                    <strong>{{ $totalDonasi }}</strong>
                </div>
            </article>

            <article class="summary-card">
                <span class="summary-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 6L9 17L4 12" />
                    </svg>
                </span>

                <div>
                    <span>Donasi Selesai</span>
                    <strong>{{ $totalSelesai }}</strong>
                </div>
            </article>

            <article class="summary-card">
                <span class="summary-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3V21" />
                        <path d="M17 7H9.5C8.1 7 7 7.9 7 9.2C7 10.5 8.1 11.2 9.5 11.5L14.5 12.5C15.9 12.8 17 13.5 17 14.8C17 16.1 15.9 17 14.5 17H7" />
                    </svg>
                </span>

                <div>
                    <span>Total Nominal</span>
                    <strong>{{ $formatRupiah($totalNominal) }}</strong>
                </div>
            </article>
        </section>

        <section class="history-panel">
            <div class="history-toolbar">
                <div>
                    <h2>Daftar Transaksi</h2>
                    <p>Riwayat donasi terbaru kamu di OrangBaik.id.</p>
                </div>

                <div class="history-filter" aria-label="Filter riwayat donasi">
                    <button class="filter-button active" type="button" data-filter="semua">
                        Semua
                    </button>

                    <button class="filter-button" type="button" data-filter="selesai">
                        Selesai
                    </button>

                    <button class="filter-button" type="button" data-filter="menunggu">
                        Menunggu
                    </button>

                    <button class="filter-button" type="button" data-filter="gagal">
                        Gagal
                    </button>
                </div>
            </div>

            @if (!empty($donations))
                <div class="history-list">
                    @foreach ($donations as $donation)
                        <article class="history-item" data-status="{{ $donation['status_key'] }}">
                            <div class="history-item-main">
                                <div class="history-image-wrap">
                                    <img
                                        src="{{ asset($donation['image']) }}"
                                        alt="{{ $donation['title'] }}"
                                        class="history-image"
                                    >
                                </div>

                                <div class="history-info">
                                    <div class="history-info-top">
                                        <span class="history-type">
                                            {{ $donation['type'] }}
                                        </span>

                                        <span class="status-badge status-{{ $donation['status_key'] }}">
                                            {{ $donation['status'] }}
                                        </span>
                                    </div>

                                    <h3>{{ $donation['title'] }}</h3>

                                    <p class="history-organizer">
                                        {{ $donation['organizer'] }}
                                    </p>

                                    <div class="history-detail">
                                        <span>{{ $donation['date'] }}</span>
                                        <span>{{ $donation['method'] }}</span>
                                        <span>{{ $donation['invoice'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="history-payment">
                                <span>Nominal</span>
                                <strong>{{ str_starts_with($donation['amount'], 'Rp') ? $donation['amount'] : 'Rp' . $donation['amount'] }}</strong>

                                <a href="#" class="receipt-button">
                                    E-Kwitansi
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="history-empty-filter" data-empty-filter>
                    <h3>Belum ada data pada filter ini</h3>
                    <p>Coba pilih filter lain untuk melihat riwayat donasi kamu.</p>
                </div>
            @else
                <div class="history-empty">
                    <h2>Belum ada riwayat donasi</h2>
                    <p>
                        Donasi yang kamu lakukan akan muncul di halaman ini.
                    </p>

                    <a href="{{ url('/donasi') }}" class="empty-button">
                        Mulai Donasi
                    </a>
                </div>
            @endif
        </section>

    </div>
</main>

@include('components.footer')

<script src="{{ asset('js/riwayat.js') }}"></script>

</body>
</html>