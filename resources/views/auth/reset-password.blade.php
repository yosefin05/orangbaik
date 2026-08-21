<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - OrangBaik.id</title>

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

                <h1>Reset Password</h1>
                <p class="subtitle">
                    Masukkan password baru untuk akun Anda.
                </p>

                {{-- Error --}}
                @if ($errors->any())
                    <div class="alert alert-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    {{-- Token --}}
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    {{-- Email --}}
                    <div class="form-group">
                        <label for="email">
                            <i class="bi bi-envelope"></i> Email
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $request->email) }}"
                               placeholder="nama@example.com" 
                               required 
                               autofocus>
                        @error('email')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Password Baru --}}
                    <div class="form-group">
                        <label for="password">
                            <i class="bi bi-lock"></i> Password Baru
                        </label>
                        <div class="password-box">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimal 8 karakter"
                                   required>
                            <button type="button" 
                                    class="eye-button" 
                                    onclick="togglePassword('password')"
                                    aria-label="Tampilkan password">
                                <i class="bi bi-eye" id="eyeOpen"></i>
                                <i class="bi bi-eye-slash" id="eyeClosed" style="display:none;"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="form-group">
                        <label for="password_confirmation">
                            <i class="bi bi-lock-fill"></i> Konfirmasi Password
                        </label>
                        <div class="password-box">
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Ulangi password baru"
                                   required>
                            <button type="button" 
                                    class="eye-button" 
                                    onclick="togglePassword('password_confirmation')"
                                    aria-label="Tampilkan password">
                                <i class="bi bi-eye" id="eyeOpen2"></i>
                                <i class="bi bi-eye-slash" id="eyeClosed2" style="display:none;"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-button">
                        <i class="bi bi-check-circle"></i>
                        Reset Password
                    </button>

                </form>

                {{-- Link Kembali --}}
                <p class="register-text" style="margin-top: 16px;">
                    <i class="bi bi-arrow-left"></i>
                    <a href="{{ route('login') }}">Kembali ke Login</a>
                </p>

            </div>

            {{-- RIGHT: WELCOME PANEL --}}
            <div class="login-right">

                <h2>Buat Password Baru</h2>

                <p class="description">
                    Pastikan password baru Anda kuat dan mudah diingat.
                    Gunakan kombinasi huruf, angka, dan simbol.
                </p>

                <div class="quote-box">
                    <div class="quote-icon">🔒</div>
                    <p class="quote-text">
                        "Password yang kuat adalah kunci keamanan akun Anda."
                    </p>
                    <span class="quote-author">— OrangBaik.id</span>
                </div>

            </div>

        </div>
    </div>

    {{-- Script --}}
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const eyeOpen = document.getElementById(inputId === 'password' ? 'eyeOpen' : 'eyeOpen2');
            const eyeClosed = document.getElementById(inputId === 'password' ? 'eyeClosed' : 'eyeClosed2');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'inline';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'inline';
                eyeClosed.style.display = 'none';
            }
        }
    </script>

</body>

</html>