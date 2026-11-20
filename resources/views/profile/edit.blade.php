<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Profil - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ubah-profile.css') }}?v=2">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @include('components.header')

    @php
        $user = auth()->user();
        $pwErrors = $errors->getBag('updatePassword');
        $emailVerified = $user->hasVerifiedEmail();
    @endphp

    <main class="pf-page">
        <div class="pf-container">

            <header class="pf-head">
                <h1>Profil Saya</h1>
                <p>Kelola informasi dan keamanan akunmu di <span>orangbaik.id</span></p>
            </header>

            @if (session('success'))
                <div class="pf-alert pf-alert--success" role="status">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="pf-layout">

                {{-- ============ RINGKASAN + FOTO ============ --}}
                <aside class="pf-summary">
                    <div class="pf-avatar">
                        <div class="pf-avatar-preview" id="avatarPreview">
                            @if ($user->foto_profil)
                                <img id="previewImage" src="{{ asset('storage/' . $user->foto_profil) }}" alt="Foto Profil">
                            @else
                                <i class="bi bi-person-fill"></i>
                            @endif
                        </div>

                        {{-- input ada di luar <form>, tetap terkirim lewat atribut form="" --}}
                        <label class="pf-avatar-btn" title="Ganti foto">
                            <input type="file" name="foto_profil" id="avatarInput" accept="image/*" form="profileForm">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                    </div>

                    <div class="pf-summary-text">
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $user->email }}</span>
                    </div>

                    <p class="pf-avatar-note" id="avatarNote" hidden>
                        Foto baru tersimpan saat kamu menekan <b>Simpan Perubahan</b>.
                    </p>

                    @error('foto_profil')
                        <small class="pf-error">{{ $message }}</small>
                    @enderror
                </aside>

                <div class="pf-main">

                    {{-- ============ INFO AKUN ============ --}}
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                        class="pf-card" id="profileForm">
                        @csrf
                        @method('PATCH')

                        <div class="pf-card-head">
                            <h2>Info Akun</h2>
                            <p>Data ini dipakai sebagai kontak pada aktivitas donasimu.</p>
                        </div>

                        <div class="pf-grid">

                            <div class="pf-field pf-field--full">
                                <label class="pf-label" for="name">Nama Lengkap</label>
                                <input class="pf-input @error('name') is-error @enderror" type="text" id="name"
                                    name="name" value="{{ old('name', $user->name) }}" placeholder="Masukkan nama lengkap"
                                    autocomplete="name">
                                @error('name') <small class="pf-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="pf-field">
                                <label class="pf-label" for="jenis_kelamin">Jenis Kelamin</label>
                                <div class="pf-select">
                                    <select class="pf-input @error('jenis_kelamin') is-error @enderror"
                                        id="jenis_kelamin" name="jenis_kelamin">
                                        <option value="">Pilih jenis kelamin</option>
                                        <option value="L" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'L')>Laki-laki</option>
                                        <option value="P" @selected(old('jenis_kelamin', $user->jenis_kelamin) == 'P')>Perempuan</option>
                                    </select>
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                                @error('jenis_kelamin') <small class="pf-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="pf-field">
                                <label class="pf-label" for="nomor">Nomor HP</label>
                                <input class="pf-input @error('nomor') is-error @enderror" type="text" id="nomor"
                                    name="nomor" value="{{ old('nomor', $user->nomor) }}" placeholder="08xxxxxxxxxx"
                                    inputmode="tel" autocomplete="tel">
                                @error('nomor') <small class="pf-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="pf-field pf-field--full">
                                <div class="pf-label-row">
                                    <label class="pf-label" for="email">Email</label>
                                    @if ($user->email)
                                        <span class="pf-chip {{ $emailVerified ? 'pf-chip--ok' : 'pf-chip--warn' }}">
                                            <i class="bi {{ $emailVerified ? 'bi-patch-check-fill' : 'bi-exclamation-circle-fill' }}"></i>
                                            {{ $emailVerified ? 'Terverifikasi' : 'Belum diverifikasi' }}
                                        </span>
                                    @endif
                                </div>
                                <input class="pf-input @error('email') is-error @enderror" type="email" id="email"
                                    name="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com"
                                    autocomplete="email">
                                @error('email') <small class="pf-error">{{ $message }}</small> @enderror
                            </div>

                        </div>

                        <div class="pf-actions">
                            <button type="submit" class="pf-btn">Simpan Perubahan</button>
                        </div>
                    </form>

                    {{-- ============ KEAMANAN (form terpisah) ============ --}}
                    <form action="{{ route('password.update') }}" method="POST" class="pf-card" id="passwordForm"
                        autocomplete="off">
                        @csrf
                        @method('PUT')

                        <div class="pf-card-head">
                            <h2>Ubah Password</h2>
                            <p>Gunakan password yang kuat dan jangan dibagikan ke siapa pun.</p>
                        </div>

                        @if (session('password_status'))
                            <div class="pf-alert pf-alert--success" role="status">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Password berhasil diperbarui.</span>
                            </div>
                        @endif

                        <div class="pf-grid">

                            <div class="pf-field pf-field--full">
                                <label class="pf-label" for="current_password">Password Saat Ini</label>
                                <div class="pf-password">
                                    <input class="pf-input @if ($pwErrors->has('current_password')) is-error @endif"
                                        type="password" id="current_password" name="current_password"
                                        placeholder="Masukkan password saat ini" autocomplete="current-password">
                                    <button type="button" class="pf-eye" data-target="current_password"
                                        aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                                </div>
                                @if ($pwErrors->has('current_password'))
                                    <small class="pf-error">{{ $pwErrors->first('current_password') }}</small>
                                @endif
                            </div>

                            <div class="pf-field">
                                <label class="pf-label" for="password">Password Baru</label>
                                <div class="pf-password">
                                    <input class="pf-input @if ($pwErrors->has('password')) is-error @endif"
                                        type="password" id="password" name="password" placeholder="Minimal 8 karakter"
                                        autocomplete="new-password">
                                    <button type="button" class="pf-eye" data-target="password"
                                        aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                                </div>
                                @if ($pwErrors->has('password'))
                                    <small class="pf-error">{{ $pwErrors->first('password') }}</small>
                                @else
                                    <small class="pf-hint">Kombinasi huruf dan angka.</small>
                                @endif
                            </div>

                            <div class="pf-field">
                                <label class="pf-label" for="password_confirmation">Ulangi Password Baru</label>
                                <div class="pf-password">
                                    <input class="pf-input" type="password" id="password_confirmation"
                                        name="password_confirmation" placeholder="Ulangi password baru"
                                        autocomplete="new-password">
                                    <button type="button" class="pf-eye" data-target="password_confirmation"
                                        aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>

                        </div>

                        <div class="pf-actions">
                            <button type="submit" class="pf-btn pf-btn--outline">Perbarui Password</button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </main>

    @include('components.footer')

    <script>
        // Preview foto profil
        (function () {
            const input = document.getElementById('avatarInput');
            const preview = document.getElementById('avatarPreview');
            const note = document.getElementById('avatarNote');
            if (!input || !preview) return;

            input.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = '<img id="previewImage" src="' + e.target.result + '" alt="Preview Foto Profil">';
                    if (note) note.hidden = false;
                };
                reader.readAsDataURL(file);
            });
        })();

        // Tampilkan / sembunyikan password
        document.querySelectorAll('.pf-eye').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.target);
                if (!input) return;
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                this.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            });
        });

        // Cegah kirim ganda
        document.querySelectorAll('.pf-card').forEach(function (form) {
            form.addEventListener('submit', function () {
                const btn = form.querySelector('button[type="submit"]');
                if (!btn) return;
                btn.disabled = true;
                btn.textContent = 'Menyimpan...';
            });
        });

        // Setelah ubah password (sukses / gagal), arahkan ke form password
        @if (session('password_status') || $pwErrors->any())
            document.getElementById('passwordForm')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        @endif
    </script>

</body>

</html>