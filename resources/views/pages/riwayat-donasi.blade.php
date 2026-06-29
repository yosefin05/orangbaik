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

@php
    $donations = [
        [
            'type' => 'Donasi',
            'date' => '17 Juni 2026',
            'status' => 'Selesai',
            'title' => 'Sedekah Makan untuk Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp300.000',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '17 Juni 2026',
            'status' => 'Selesai',
            'title' => 'Sedekah Makan untuk Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp300.000',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '17 Juni 2026',
            'status' => 'Selesai',
            'title' => 'Sedekah Makan untuk Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp300.000',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '17 Juni 2026',
            'status' => 'Selesai',
            'title' => 'Sedekah Makan untuk Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp300.000',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '17 Juni 2026',
            'status' => 'Selesai',
            'title' => 'Sedekah Makan untuk Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp300.000',
            'image' => 'assets/slide1.png',
        ],
        [
            'type' => 'Donasi',
            'date' => '17 Juni 2026',
            'status' => 'Selesai',
            'title' => 'Sedekah Makan untuk Yatim dan Dhuafa',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp300.000',
            'image' => 'assets/slide1.png',
        ],
    ];
@endphp

<main class="history-page">
    <div class="history-container">

        <button class="history-back" type="button" onclick="history.back()">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 18L9 12L15 6" />
            </svg>
            <span>Kembali</span>
        </button>

        <h1 class="history-title">Riwayat Donasi</h1>

        <section class="history-grid">
            @foreach ($donations as $donation)
                <article class="history-card">

                    <div class="history-card-top">
                        <div class="history-type">
                            <span class="history-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3C7.6 3 4 6.6 4 11C4 15.4 7.6 19 12 19C16.4 19 20 15.4 20 11C20 6.6 16.4 3 12 3Z"/>
                                    <path d="M12 7V15"/>
                                    <path d="M9.5 9C9.5 7.9 10.6 7 12 7C13.4 7 14.5 7.9 14.5 9C14.5 10.1 13.4 10.7 12 11C10.6 11.3 9.5 11.9 9.5 13C9.5 14.1 10.6 15 12 15C13.4 15 14.5 14.1 14.5 13"/>
                                    <path d="M5 21H19"/>
                                </svg>
                            </span>

                            <div>
                                <strong>{{ $donation['type'] }}</strong>
                                <small>{{ $donation['date'] }}</small>
                            </div>
                        </div>

                        <span class="status-badge">{{ $donation['status'] }}</span>
                    </div>

                    <div class="campaign-box">
                        <img 
                            src="{{ asset($donation['image']) }}" 
                            alt="{{ $donation['title'] }}"
                            class="campaign-image"
                        >

                        <div class="campaign-body">
                            <h2>{{ $donation['title'] }}</h2>

                            <p>
                                {{ $donation['organizer'] }}
                                <span>●</span>
                            </p>
                        </div>
                    </div>

                    <div class="history-card-bottom">
                        <div>
                            <span>Nominal Donasi</span>
                            <strong>{{ $donation['amount'] }}</strong>
                        </div>

                        <a href="#" class="receipt-button">E-Kwitansi</a>
                    </div>

                </article>
            @endforeach
        </section>

    </div>
</main>

@include('components.footer')

</body>
</html>