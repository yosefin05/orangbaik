<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nominal Donasi - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-bayar.css') }}">
</head>
<body>

@php
    $nominals = [
        ['emoji' => '😊', 'amount' => 'Rp10.000', 'value' => 10000],
        ['emoji' => '😄', 'amount' => 'Rp50.000', 'value' => 50000],
        ['emoji' => '😘', 'amount' => 'Rp100.000', 'value' => 100000],
        ['emoji' => '😍', 'amount' => 'Rp500.000', 'value' => 500000],
        ['emoji' => '😍', 'amount' => 'Rp1.000.000', 'value' => 1000000],
        ['emoji' => '😍', 'amount' => 'Rp10.000.000', 'value' => 10000000],
    ];
@endphp

<main class="nominal-page">
    <div class="nominal-container">

        <button class="back-button" type="button" onclick="history.back()">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 18L9 12L15 6" />
            </svg>
            <span>Kembali</span>
        </button>

        <form id="nominalForm" action="#" method="POST" class="nominal-form">
            @csrf

            {{-- CAMPAIGN INFO --}}
            <article class="campaign-preview">
                <img 
                    src="{{ asset('assets/slide1.png') }}" 
                    alt="Beasiswa Yatim Dhuafa"
                    class="campaign-preview-image"
                >

                <div class="campaign-preview-body">
                    <h1>Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulan Gratis</h1>

                    <p class="campaign-organizer">
                        Dompet Al-Qur'an Indonesia
                        <span>●</span>
                    </p>

                    <div class="campaign-amount">
                        <strong>Rp 200.000.000</strong>
                        <span>Terkumpul</span>
                    </div>

                    <div class="campaign-progress">
                        <div></div>
                    </div>

                    <div class="campaign-meta">
                        <span>👤 100rb ± donatur</span>
                        <span>∞</span>
                    </div>
                </div>
            </article>

            {{-- NOMINAL --}}
            <section class="nominal-section">
                <h2>Masukkan Nominal Donasi</h2>

                <div class="nominal-grid">
                    @foreach ($nominals as $index => $nominal)
                        <label class="nominal-option">
                            <input 
                                type="radio" 
                                name="nominal" 
                                value="{{ $nominal['value'] }}"
                                {{ $index === 0 ? 'checked' : '' }}
                            >

                            <span class="nominal-emoji">{{ $nominal['emoji'] }}</span>
                            <strong>{{ $nominal['amount'] }}</strong>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- CUSTOM NOMINAL --}}
            <section class="custom-nominal">
                <h2>Masukkan Donasi Lainnya</h2>

                <div class="custom-input">
                    <span>Rp</span>
                    <input type="number" name="nominal_lainnya" placeholder="0" min="5000">
                </div>

                <p>Min. Donasi sebesar Rp5.000</p>
            </section>

        </form>

    </div>

    <div class="bottom-action">
        <button type="submit" form="nominalForm" class="continue-button">
            Lanjutkan Pembayaran
        </button>
    </div>
</main>

</body>
</html>