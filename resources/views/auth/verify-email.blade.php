<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Orang Baik</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    <div class="login-wrapper">

        <div class="login-card reversed">

            <!-- LEFT: CONTENT & ACTIONS (tampil di KANAN karena class reversed) -->
            <div class="login-left">

                <h1>Verifikasi Email Kamu</h1>
                <p class="subtitle">Satu langkah lagi untuk mengaktifkan akun OrangBaik.id kamu.</p>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert-success">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <div class="verify-info-box">
                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </div>

                <div class="action-group">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="login-button">
                            {{ __('Resend Verification Email') }}
                        </button>
                    </form>

                    <div class="btn-logout-wrapper">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-logout">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- RIGHT: WELCOME PANEL & QUOTES (tampil di KIRI karena class reversed) -->
            <div class="login-right">

                <h2>Keamanan & Validitas Akun</h2>

                <p class="description">
                    Proses verifikasi ini dilakukan untuk memastikan email kamu aktif serta menjaga keamanan transaksi donasi kamu.
                </p>

                @php
                    $quotes = [
                        [
                            'quote' => 'Kita mencari nafkah dengan apa yang kita dapatkan, tetapi kita membuat kehidupan dengan apa yang kita berikan.',
                            'author' => 'Winston Churchill'
                        ],
                        [
                            'quote' => 'Melayani sesama adalah sewa yang kau bayar untuk tempatmu di Bumi ini.',
                            'author' => 'Muhammad Ali'
                        ],
                        [
                            'quote' => 'Sedekahmu tidak akan diterima sampai engkau percaya bahwa: "Aku lebih membutuhkan pahala sedekah ini daripada si miskin membutuhkan uang tersebut."',
                            'author' => 'Khalifah Utsman bin Affan'
                        ]
                    ];

                    $startIndex = array_rand($quotes);
                    $randomQuote = $quotes[$startIndex];
                @endphp

                <div class="quote-box" id="quoteBox" data-start-index="{{ $startIndex }}">
                    <div class="quote-icon">❝</div>

                    <p class="quote-text" id="quoteText">
                        "{{ $randomQuote['quote'] }}"
                    </p>

                    <span class="quote-author" id="quoteAuthor">
                        — {{ $randomQuote['author'] }}
                    </span>
                </div>

                <script id="quotesData" type="application/json">
                    {!! json_encode($quotes) !!}
                </script>

            </div>

        </div>

    </div>

    <script>
        (function () {
            const quotesEl = document.getElementById('quotesData');
            if (!quotesEl) return;

            const quotes = JSON.parse(quotesEl.textContent);
            const quoteBox = document.getElementById('quoteBox');
            const quoteText = document.getElementById('quoteText');
            const quoteAuthor = document.getElementById('quoteAuthor');

            if (!quoteBox || quotes.length <= 1) return;

            let currentIndex = parseInt(quoteBox.dataset.startIndex, 10) || 0;

            function showNextQuote() {
                quoteBox.classList.add('quote-fade-out');

                setTimeout(function () {
                    currentIndex = (currentIndex + 1) % quotes.length;
                    const next = quotes[currentIndex];

                    quoteText.textContent = '"' + next.quote + '"';
                    quoteAuthor.textContent = '— ' + next.author;

                    quoteBox.classList.remove('quote-fade-out');
                }, 300);
            }

            setInterval(showNextQuote, 5000);
        })();
    </script>

</body>

</html>