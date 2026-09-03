<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Password - OrangBaik.id</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card">

            {{-- LEFT: FORM --}}
            <div class="login-left">

                <h1>Konfirmasi Password</h1>
                <p class="subtitle">
                    Ini adalah area aman. Silakan konfirmasi password Anda untuk melanjutkan.
                </p>

                {{-- Error --}}
                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <div class="form-group">
                        <label for="password">
                            <i class="bi bi-lock"></i> Password
                        </label>
                        <div class="password-box">
                            <input type="password" id="password" name="password" placeholder="Masukkan Password Anda" required autocomplete="current-password" autofocus>
                        </div>
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="login-button">
                        <i class="bi bi-shield-check"></i>
                        Konfirmasi Password
                    </button>

                </form>

            </div>

            {{-- RIGHT: PANEL --}}
            <div class="login-right">

                <h2>Area Aman</h2>

                <p class="description">
                    Konfirmasi password diperlukan untuk memverifikasi bahwa ini benar-benar Anda.
                </p>

                <div class="quote-box">
                    <div class="quote-icon">🛡️</div>
                    <p class="quote-text">
                        "Privasi dan keamanan akun donasi Anda senantiasa terjaga."
                    </p>
                    <span class="quote-author">— Tim OrangBaik.id</span>
                </div>

            </div>

        </div>
    </div>

</body>

</html>
