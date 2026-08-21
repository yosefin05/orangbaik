<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - OrangBaik.id</title>

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

                <h1>Lupa Password?</h1>
                <p class="subtitle">
                    Masukkan email Anda, kami akan kirimkan link reset password.
                </p>

                {{-- Alert Status --}}
                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Error --}}
                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">
                            <i class="bi bi-envelope"></i> Email
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               placeholder="nama@example.com" 
                               required 
                               autofocus>
                        @error('email')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="login-button">
                        <i class="bi bi-send"></i>
                        Kirim Link Reset Password
                    </button>

                </form>

                {{-- Link Kembali ke Login --}}
                <p class="register-text" style="margin-top: 16px;">
                    <i class="bi bi-arrow-left"></i>
                    <a href="{{ route('login') }}">Kembali ke Login</a>
                </p>

            </div>

            {{-- RIGHT: WELCOME PANEL --}}
            <div class="login-right">

                <h2>Reset Password</h2>

                <p class="description">
                    Kami akan mengirimkan link reset password ke email Anda. 
                    Link tersebut berlaku selama 60 menit.
                </p>

                <div class="quote-box">
                    <div class="quote-icon">🔐</div>
                    <p class="quote-text">
                        "Keamanan akun Anda adalah prioritas kami."
                    </p>
                    <span class="quote-author">— OrangBaik.id</span>
                </div>

            </div>

        </div>
    </div>

</body>

</html>