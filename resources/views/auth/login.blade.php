<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OrangBaik.id</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    <div class="login-wrapper">

        <div class="login-card">

            <!-- LEFT: FORM -->
            <div class="login-left">

                <h1>Masuk ke Akun Anda</h1>
                <p class="subtitle">Gunakan email dan password yang sudah terdaftar.</p>

                @if ($errors->any())
                    <div class="alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="nama@example.com" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-box">
                            <input type="password" id="password" name="password" placeholder="Masukkan Password"
                                required>
                            <button type="button" class="eye-button"
                                onclick="togglePassword('password', 'eyeOpen1', 'eyeClosed1')"
                                aria-label="Tampilkan password">
                                <svg id="eyeOpen1" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg id="eyeClosed1" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round" style="display:none;">
                                    <path
                                        d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a18.7 18.7 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19M14.12 14.12a3 3 0 11-4.24-4.24" />
                                    <line x1="1" y1="1" x2="23" y2="23" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-button">
                        Masuk Sekarang
                    </button>

                </form>
                <div class="register-links">
                    <a href="{{ route('password.request') }}">Reset Password</a>
                    <span class="divider">|</span>
                    <a href="{{ route('register') }}">Daftar Sekarang</a>
                </div>

            </div>

            <!-- RIGHT: WELCOME PANEL -->
            <div class="login-right">

                <h2>Selamat Datang Kembali di orangbaik.id</h2>

                <p class="description">
                    Platform donasi yang transparan, aman, dan berdampak nyata bagi masyarakat Indonesia.
                </p>

                @php
                    $quotes = [
                        [
                            'quote' => 'Kita mencari nafkah dengan apa yang kita dapatkan, tetapi kita membuat kehidupan dengan apa yang kita berikan.',
                            'author' => 'Winston Churchill'
                        ],
                        [
                            'quote' => 'Service to others is the rent you pay for your room here on Earth.',
                            'author' => 'Muhammad Ali'
                        ],
                        [
                            'quote' => 'Sedekahmu tidak akan diterima sampai engkau percaya bahwa: "Aku lebih membutuhkan pahala sedekah ini daripada si miskin membutuhkan uang tersebut."',
                            'author' => 'Khalifah Utsman bin Affan'
                        ]
                    ];

                    $randomQuote = $quotes[array_rand($quotes)];
                @endphp

                <div class="quote-box">
                    <div class="quote-icon">❝</div>

                    <p class="quote-text">
                        "{{ $randomQuote['quote'] }}"
                    </p>

                    <span class="quote-author">
                        — {{ $randomQuote['author'] }}
                    </span>
                </div>

            </div>

        </div>

    </div>

    <script>
        function togglePassword(inputId, openIconId, closedIconId) {
            const input = document.getElementById(inputId);
            const eyeOpen = document.getElementById(openIconId);
            const eyeClosed = document.getElementById(closedIconId);

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }
    </script>

</body>

</html>