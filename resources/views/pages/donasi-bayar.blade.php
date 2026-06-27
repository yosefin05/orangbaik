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
        ['emoji' => '😊', 'amount' => 'Rp10.000'],
        ['emoji' => '😄', 'amount' => 'Rp50.000'],
        ['emoji' => '😘', 'amount' => 'Rp100.000'],
        ['emoji' => '😍', 'amount' => 'Rp500.000'],
    ];

    $ewallets = [
        ['name' => 'GOPAY'],
        ['name' => 'GOPAY'],
        ['name' => 'GOPAY'],
        ['name' => 'GOPAY'],
    ];

    $virtualAccounts = [
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'VA Bank BRI'],
    ];

    $bankTransfers = [
        ['bank' => 'BANK BRI', 'name' => 'Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'Bank BRI'],
        ['bank' => 'BANK BRI', 'name' => 'Bank BRI'],
    ];
@endphp

<main class="payment-page">
    <div class="payment-container">

        <button class="back-button" type="button" onclick="history.back()">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 18L9 12L15 6" />
            </svg>
            <span>Kembali</span>
        </button>

        <form class="payment-layout" action="#" method="POST">
            @csrf

            {{-- LEFT --}}
            <section class="payment-left">

                <article class="campaign-mini-card">
                    <img src="{{ asset('assets/slide1.png') }}" alt="Beasiswa Yatim Dhuafa">

                    <div class="campaign-mini-body">
                        <h1>Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulan Gratis</h1>

                        <p>
                            Dompet Al-Qur'an Indonesia
                            <span>●</span>
                        </p>

                        <div class="mini-amount">
                            <strong>Rp 200.000.000</strong>
                            <span>Terkumpul</span>
                        </div>

                        <div class="mini-progress">
                            <div></div>
                        </div>

                        <div class="mini-meta">
                            <span>👤 100rb ± donatur</span>
                            <span>∞</span>
                        </div>
                    </div>
                </article>

                <section class="nominal-section">
                    <h2>Masukkan Nominal Donasi</h2>

                    <div class="nominal-list">
                        @foreach ($nominals as $index => $nominal)
                            <label class="nominal-card">
                                <input 
                                    type="radio" 
                                    name="nominal" 
                                    value="{{ $nominal['amount'] }}"
                                    {{ $index === 0 ? 'checked' : '' }}
                                >

                                <span class="nominal-emoji">{{ $nominal['emoji'] }}</span>
                                <strong>{{ $nominal['amount'] }}</strong>
                            </label>
                        @endforeach
                    </div>

                    <div class="custom-nominal-card">
                        <h3>Masukkan Donasi Lainnya</h3>

                        <div class="custom-input-wrap">
                            <span>Rp</span>
                            <input type="number" name="nominal_lainnya" placeholder="0" min="5000">
                        </div>

                        <p>Min. Donasi sebesar Rp5.000</p>
                    </div>
                </section>

                <section class="donor-card">
                    <p class="donor-title">
                        <a href="#">Masuk</a> atau Lengkapi Data dibawah ini
                    </p>

                    <div class="donor-input-group">
                        <input type="text" name="nama" placeholder="Masukkan Nama Lengkap">
                        <input type="text" name="no_punggung" placeholder="Masukkan Nomor Ponsel">
                    </div>

                    <p class="input-note">
                        <span>ⓘ</span>
                        Pastikan email atau nomor ponselmu sudah benar untuk menerima laporan donasi.
                    </p>

                    <label class="switch-row">
                        <span>Sembunyikan nama saya (donasi sebagai orangbaik)</span>

                        <input type="checkbox" name="anonymous_donor">
                        <i></i>
                    </label>
                </section>

                <section class="message-card">
                    <h2>Sampaikan doa serta pesan dukungan (opsional)</h2>

                    <div class="textarea-wrap">
                        <textarea 
                            name="pesan"
                            maxlength="255"
                            placeholder="Tuliskan doa dan harapan Anda untuk penggalang dana atau diri sendiri. Hindari penggunaan emoji agar pesan tetap nyaman dibaca."
                        ></textarea>

                        <span>0/255</span>
                    </div>

                    <label class="switch-row">
                        <span>Sembunyikan nama saya (donasi sebagai orangbaik)</span>

                        <input type="checkbox" name="anonymous_message">
                        <i></i>
                    </label>
                </section>

            </section>

            {{-- RIGHT --}}
            <aside class="payment-right">
                <div class="payment-method-card">
                    <h2>Pilih Metode Pembayaran</h2>

                    <div class="method-group">
                        <h3>E-Wallet</h3>

                        @foreach ($ewallets as $index => $wallet)
                            <label class="method-item">
                                <input 
                                    type="radio" 
                                    name="payment_method" 
                                    value="gopay_{{ $index }}"
                                    {{ $index === 0 ? 'checked' : '' }}
                                >

                                <span class="custom-radio"></span>

                                <span class="wallet-logo">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M7 8H17C19.2 8 21 9.8 21 12C21 14.2 19.2 16 17 16H7C4.8 16 3 14.2 3 12C3 9.8 4.8 8 7 8Z" />
                                        <path d="M8 12H14" />
                                    </svg>
                                </span>

                                <strong>{{ $wallet['name'] }}</strong>
                            </label>
                        @endforeach
                    </div>

                    <div class="method-group">
                        <h3>Virtual Account (VA)</h3>

                        @foreach ($virtualAccounts as $index => $va)
                            <label class="method-item">
                                <input type="radio" name="payment_method" value="va_bri_{{ $index }}">

                                <span class="custom-radio"></span>

                                <span class="bank-logo">{{ $va['bank'] }}</span>
                                <strong>{{ $va['name'] }}</strong>
                            </label>
                        @endforeach
                    </div>

                    <div class="method-group">
                        <h3>Bank Transfer</h3>

                        @foreach ($bankTransfers as $index => $bank)
                            <label class="method-item">
                                <input type="radio" name="payment_method" value="bank_bri_{{ $index }}">

                                <span class="custom-radio"></span>

                                <span class="bank-logo">{{ $bank['bank'] }}</span>
                                <strong>{{ $bank['name'] }}</strong>
                            </label>
                        @endforeach
                    </div>

                    <div class="payment-total">
                        <span>Total Donasi</span>
                        <strong>Rp5.000</strong>
                    </div>

                    <button class="pay-button" type="submit">
                        🛡 Bayar Sekarang
                    </button>
                </div>
            </aside>

        </form>
    </div>
</main>

</body>
</html>