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
        // Data dikirim dari controller: $formattedDonations, $totalDonasi, $totalNominal, $totalSelesai
        $donations = $formattedDonations ?? [];
        $formatRupiah = fn($value) => 'Rp' . number_format($value, 0, ',', '.');
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
                    <strong>{{ $formatRupiah($totalNominal ?? 0) }}</strong>
                    <p>Dari {{ $totalDonasi ?? 0 }} transaksi donasi</p>
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
                        <strong>{{ $totalDonasi ?? 0 }}</strong>
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
                        <strong>{{ $totalSelesai ?? 0 }}</strong>
                    </div>
                </article>

                <article class="summary-card">
                    <span class="summary-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3V21" />
                            <path
                                d="M17 7H9.5C8.1 7 7 7.9 7 9.2C7 10.5 8.1 11.2 9.5 11.5L14.5 12.5C15.9 12.8 17 13.5 17 14.8C17 16.1 15.9 17 14.5 17H7" />
                        </svg>
                    </span>

                    <div>
                        <span>Total Nominal</span>
                        <strong>{{ $formatRupiah($totalNominal ?? 0) }}</strong>
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
                                        <img src="{{ $donation['image'] }}" alt="{{ $donation['title'] }}"
                                            class="history-image">
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
                                    <strong>{{ $donation['amount'] }}</strong>

                                    <a href="{{ route('riwayat-donasi.kwitansi', ['donasi' => $donation['id']]) }}"
                                        class="receipt-button" target="_blank">
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