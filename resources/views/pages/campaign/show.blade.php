<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedekah Pendidikan Anak - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/campaign.css') }}">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="{{ url('/') }}" class="brand">
            <div class="brand-logo">
                <img src="{{ asset('assets/logo-icon.png') }}" alt="OrangBaik.id">
            </div>
        </a>

        <nav class="nav-menu">
            <a href="{{ url('/') }}">Beranda</a>
            <a href="#" class="active">Donasi</a>
            <a href="#">Kalkulator</a>
            <a href="#">Berita</a>
        </nav>

        <div class="header-actions">
            <a href="{{ route('login') }}" class="login-link">Masuk</a>
            <a href="{{ route('register') }}" class="register-btn">Daftar</a>
        </div>
    </div>
</header>

<main class="campaign-page">
    <section class="campaign-hero">
        <div class="container campaign-grid">

            <div class="campaign-content">
                <div class="campaign-image">
                    <img src="{{ asset('assets/campaign-cover.jpg') }}" alt="Campaign Donasi">
                </div>

                <div class="campaign-meta">
                    <span class="badge">Pendidikan</span>
                    <span>OrangBaik.id Foundation</span>
                </div>

                <h1>Bantu Pendidikan Anak Yatim dan Dhuafa</h1>

                <p class="campaign-desc">
                    Mari bantu anak-anak yatim dan dhuafa mendapatkan perlengkapan sekolah,
                    biaya pendidikan, dan kebutuhan belajar agar mereka bisa terus mengejar cita-cita.
                </p>

                <div class="progress-box">
                    <div class="progress-top">
                        <div>
                            <p>Terkumpul</p>
                            <h3>Rp 42.500.000</h3>
                        </div>
                        <div class="target">
                            <p>Target</p>
                            <h3>Rp 100.000.000</h3>
                        </div>
                    </div>

                    <div class="progress-bar">
                        <span style="width: 42.5%"></span>
                    </div>

                    <div class="progress-info">
                        <span>425 Donatur</span>
                        <span>28 hari lagi</span>
                    </div>
                </div>

                <section class="story-section">
                    <h2>Cerita Campaign</h2>

                    <p>
                        Banyak anak-anak yang masih kesulitan mendapatkan akses pendidikan yang layak.
                        Sebagian dari mereka harus berjuang dengan keterbatasan biaya, perlengkapan sekolah,
                        dan kebutuhan belajar sehari-hari.
                    </p>

                    <p>
                        Melalui campaign ini, donasi yang terkumpul akan digunakan untuk membantu biaya sekolah,
                        buku, seragam, alat tulis, dan kebutuhan pendidikan lainnya.
                    </p>

                    <div class="info-cards">
                        <div class="info-card">
                            <h4>Tujuan Donasi</h4>
                            <p>Biaya pendidikan, perlengkapan sekolah, dan kebutuhan belajar.</p>
                        </div>

                        <div class="info-card">
                            <h4>Penerima Manfaat</h4>
                            <p>Anak yatim, dhuafa, dan pelajar dari keluarga kurang mampu.</p>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="donation-card">
                <h2>Mulai Donasi</h2>
                <p class="donation-subtitle">
                    Pilih cara donasi yang paling nyaman untuk kamu.
                </p>

                <div class="donation-tabs">
                    <button class="tab-btn active" data-tab="guest">Donasi Cepat</button>
                    <button class="tab-btn" data-tab="account">Pakai Akun</button>
                </div>

                <form id="guestForm" class="donation-form active-form" action="#" method="POST">
                    @csrf

                    <label>Nama Donatur</label>
                    <input type="text" name="name" placeholder="Contoh: Josie Karim">

                    <label>Email / No HP</label>
                    <input type="text" name="contact" placeholder="Email atau nomor WhatsApp">

                    <label>Nominal Donasi</label>
                    <div class="amount-options">
                        <button type="button" data-amount="10000">Rp10.000</button>
                        <button type="button" data-amount="25000">Rp25.000</button>
                        <button type="button" data-amount="50000">Rp50.000</button>
                        <button type="button" data-amount="100000">Rp100.000</button>
                    </div>

                    <input type="number" id="amountInput" name="amount" placeholder="Nominal lainnya">

                    <label>Metode Pembayaran</label>
                    <div class="payment-options">
                        <label>
                            <input type="radio" name="payment" checked>
                            <span>QRIS</span>
                        </label>

                        <label>
                            <input type="radio" name="payment">
                            <span>Transfer Bank</span>
                        </label>

                        <label>
                            <input type="radio" name="payment">
                            <span>E-Wallet</span>
                        </label>
                    </div>

                    <button type="submit" class="donate-btn">
                        Donasi Sekarang
                    </button>

                    <p class="form-note">
                        Dengan donasi cepat, kamu tidak perlu membuat password.
                        Setelah donasi berhasil, kamu bisa melihat status donasi.
                    </p>
                </form>

                <div id="accountForm" class="donation-form account-box">
                    <div class="account-option">
                        <h3>Sudah punya akun?</h3>
                        <p>Masuk untuk donasi dan menyimpan riwayat donasi kamu.</p>
                        <a href="{{ route('login') }}" class="outline-btn">Masuk Akun</a>
                    </div>

                    <div class="account-option">
                        <h3>Belum punya akun?</h3>
                        <p>Daftar dengan password agar bisa login kapan saja.</p>
                        <a href="{{ route('register') }}" class="donate-btn register-full">
                            Daftar Akun Donatur
                        </a>
                    </div>
                </div>
            </aside>

        </div>
    </section>
</main>

<script>
    const tabButtons = document.querySelectorAll('.tab-btn');
    const guestForm = document.getElementById('guestForm');
    const accountForm = document.getElementById('accountForm');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            if (button.dataset.tab === 'guest') {
                guestForm.classList.add('active-form');
                accountForm.classList.remove('active-form');
            } else {
                accountForm.classList.add('active-form');
                guestForm.classList.remove('active-form');
            }
        });
    });

    const amountButtons = document.querySelectorAll('.amount-options button');
    const amountInput = document.getElementById('amountInput');

    amountButtons.forEach(button => {
        button.addEventListener('click', () => {
            amountButtons.forEach(btn => btn.classList.remove('selected'));
            button.classList.add('selected');
            amountInput.value = button.dataset.amount;
        });
    });
</script>

</body>
</html>