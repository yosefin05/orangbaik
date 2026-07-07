<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Zakat - OrangBaik.id</title>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kalkulator.css') }}">
</head>

<body>

    @include('components.header')

    <!-- HERO -->
    <section class="zakat-hero">
        <div class="zakat-hero-inner">
            <div class="zakat-hero-text">
                <h1>Kalkulator Zakat</h1>
                <p>
                    Hitung zakat Anda dengan mudah dan cepat.<br>
                    Pastikan zakat yang Anda bayarkan sudah sesuai<br>
                    dengan ketentuan syariat.
                </p>
            </div>
            <div class="zakat-hero-img">
                <img src="{{ asset('images/kalkulator-zakat.png') }}" alt="Kalkulator Zakat">
            </div>
        </div>
    </section>

    <!-- INFO BANNER -->
    <div class="zakat-info-banner">
        <div class="zakat-info-banner-inner">
            <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                Zakat yang dihitung berdasarkan ketentuan syariat
            </span>
            <a href="https://baznas.go.id/assets/pdf/ppid/tentang%20zakat/SK_01_2024.pdf" target="_blank">📖 Pelajari
                Lebih Lanjut</a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="zakat-main">
        <!-- KOLOM KIRI -->
        <div class="zakat-left">
            <!-- 1. PILIH JENIS ZAKAT -->
            <div class="zakat-card">
                <div class="zakat-card-header">
                    <h2>Kalkulator Zakat</h2>
                    <p>Pilih jenis zakat, kemudian isi data sesuai kondisi Anda.</p>
                </div>

                <div class="zakat-tabs">

                    <button type="button" class="zakat-tab active" onclick="selectZakat('penghasilan', this)">
                        <span class="zakat-icon">💼</span>

                        <div class="zakat-info">
                            <h4>Penghasilan</h4>
                            <p>Zakat dari gaji, honor, atau pendapatan rutin.</p>
                        </div>
                    </button>

                    <button type="button" class="zakat-tab" onclick="selectZakat('emas', this)">
                        <span class="zakat-icon">🥇</span>

                        <div class="zakat-info">
                            <h4>Emas</h4>
                            <p>Zakat atas kepemilikan emas yang mencapai nisab.</p>
                        </div>
                    </button>

                    <button type="button" class="zakat-tab" onclick="selectZakat('tabungan', this)">
                        <span class="zakat-icon">🏦</span>

                        <div class="zakat-info">
                            <h4>Tabungan</h4>
                            <p>Zakat atas simpanan uang yang telah mencapai nisab.</p>
                        </div>
                    </button>

                    <button type="button" class="zakat-tab" onclick="selectZakat('perdagangan', this)">
                        <span class="zakat-icon">📊</span>

                        <div class="zakat-info">
                            <h4>Perniagaan</h4>
                            <p>Zakat dari aset dan keuntungan usaha perdagangan.</p>
                        </div>
                    </button>

                </div>

                <form action="{{ url('/kalkulator/hitung') }}" method="POST" id="zakatForm">
                    @csrf
                    <input type="hidden" name="jenis" id="jenis" value="penghasilan">

                    <div class="zakat-form-header">
                        <h3 id="form-title">
                            Zakat Penghasilan
                        </h3>
                        <p id="form-subtitle">
                            Masukkan penghasilan bulanan Anda.
                        </p>
                    </div>

                    <div class="zakat-info-box">
                        <strong>Nishab</strong>
                        <span id="nishab-info">
                            85 gram emas per tahun
                        </span>
                    </div>

                    <div id="form-fields">
                    </div>

                    <button type="submit" class="zakat-submit-btn">
                        <svg class="zakat-submit-icon" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect x="4" y="2" width="16" height="20" rx="2" stroke="currentColor" stroke-width="2" />
                            <line x1="8" y1="6" x2="16" y2="6" stroke="currentColor" stroke-width="2" />
                            <circle cx="8" cy="11" r="1" fill="currentColor" />
                            <circle cx="12" cy="11" r="1" fill="currentColor" />
                            <circle cx="16" cy="11" r="1" fill="currentColor" />
                            <circle cx="8" cy="15" r="1" fill="currentColor" />
                            <circle cx="12" cy="15" r="1" fill="currentColor" />
                            <circle cx="16" cy="15" r="1" fill="currentColor" />
                        </svg>
                        Hitung Zakat
                    </button>
                </form>

                <form action="{{ url('/kalkulator/hitung') }}" method="POST" id="zakatForm">


            </div>
        </div>

        <!-- KOLOM KANAN -->
        <div class="zakat-right">

            @if(session('hasil'))

                <!-- HASIL PERHITUNGAN -->
                <div class="zakat-result-card">

                    <div class="zakat-result-header">
                        <h3>Hasil Perhitungan</h3>
                        <span class="zakat-result-badge">{{ ucfirst(session('jenis', 'Zakat')) }}</span>
                    </div>

                    <div class="zakat-result-label">Total Zakat yang Harus Dibayarkan</div>

                    <div class="zakat-result-amount">
                        Rp{{ number_format(session('hasil'), 0, ',', '.') }}
                    </div>

                    @if(session('persentase'))
                        <div class="zakat-result-note">({{ session('persentase') }}% dari
                            {{ session('dasar') ? 'Rp' . number_format(session('dasar'), 0, ',', '.') : '-' }})
                        </div>
                    @endif

                    <div class="zakat-result-table">

                        @if(session('harta'))
                            <div class="zakat-result-row">
                                <span>Total Harta</span>
                                <strong>Rp{{ number_format(session('harta'), 0, ',', '.') }}</strong>
                            </div>
                        @endif

                        @if(session('utang'))
                            <div class="zakat-result-row">
                                <span>Total Hutang</span>
                                <strong class="red">- Rp{{ number_format(session('utang'), 0, ',', '.') }}</strong>
                            </div>
                        @endif

                        @if(session('nishab'))
                            <div class="zakat-result-row">
                                <span>Harta Bersih (Nishab)</span>
                                <strong>Rp{{ number_format(session('nishab'), 0, ',', '.') }}</strong>
                            </div>
                        @endif

                        @if(session('persentase'))
                            <div class="zakat-result-row">
                                <span>Persentase Zakat</span>
                                <strong>{{ session('persentase') }}%</strong>
                            </div>
                        @endif

                        <div class="zakat-result-row">
                            <span>Zakat yang Dikeluarkan</span>
                            <strong class="green">Rp{{ number_format(session('hasil'), 0, ',', '.') }}</strong>
                        </div>

                    </div>

                    @if(session('dasar_hukum'))
                        <div class="zakat-basis-box">
                            <div class="zakat-basis-icon">📖</div>
                            <div>
                                <h4>Dasar Perhitungan</h4>
                                <p>{{ session('dasar_hukum') }}</p>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- REKOMENDASI -->
                <div class="zakat-rekomendasi-card">
                    <h3>Rekomendasi Selanjutnya</h3>
                    <p>Salurkan zakat Anda untuk membantu mereka yang membutuhkan</p>

                    <a href="/donasi/zakat" class="btn-pay-primary">
                        🤝 Salurkan Zakat Sekarang
                    </a>

                    <button type="button" class="btn-pay-outline" onclick="window.print()">
                        🔖 Simpan Perhitungan
                    </button>
                </div>

            @else

                <!-- EMPTY STATE -->
                <div class="zakat-result-card">
                    <div class="zakat-result-header">
                        <h3>Hasil Perhitungan</h3>
                    </div>
                    <div class="zakat-empty-result">
                        <span class="empty-icon">🧮</span>
                        <p>Isi form di sebelah kiri dan klik<br><strong>Hitung Zakat</strong> untuk melihat hasil
                            perhitungan.
                        </p>
                    </div>
                </div>

            @endif

        </div>

    </div>

    <!-- SYARAT & KETENTUAN -->
    <div class="zakat-syarat">

        <h3>Syarat dan Ketentuan Zakat</h3>

        <div class="zakat-syarat-grid">

            <div class="zakat-syarat-item">
                <span class="syarat-icon">💰</span>
                <h4>Mencapai Nishab</h4>
                <p>Harta telah mencapai batas minimum (nishab)</p>
            </div>

            <div class="zakat-syarat-item">
                <span class="syarat-icon">📅</span>
                <h4>Genap 1 Tahun</h4>
                <p>Harta telah dimiliki selama 1 tahun (haul)</p>
            </div>

            <div class="zakat-syarat-item">
                <span class="syarat-icon">📈</span>
                <h4>Harta Berkembang</h4>
                <p>Harta berpotensi berkembang</p>
            </div>

            <div class="zakat-syarat-item">
                <span class="syarat-icon">✅</span>
                <h4>Bebas dari Kebutuhan Pokok</h4>
                <p>Harta melebihi kebutuhan pokok dan utang</p>
            </div>

        </div>

    </div>

    @include('components.footer')
    <script src="{{ asset('js/kalkulator.js') }}"></script>
</body>

</html>