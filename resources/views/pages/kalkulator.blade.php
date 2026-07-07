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
                    <h3>1. Pilih jenis zakat</h3>
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
                    <input type="hidden" id="jenis" name="jenis" value="penghasilan">
                    <div class="zakat-form-header">
                        <h3 id="form-title"></h3>
                        <div class="zakat-info-box">
                            <strong>Nisab</strong>
                            <span id="nishab-info"></span>
                        </div>
                    </div>

                    <div id="form-fields"></div>

                    <button type="submit" class="zakat-submit-btn">
                        <i class="fa-solid fa-calculator"></i>
                        Hitung Zakat
                    </button>

                </form>
            </div>
        </div>

        <!-- ================= HASIL PERHITUNGAN ================= -->
        <div class="zakat-right">

            @if(session('hasil'))

                        <div class="zakat-result-card">

                            <div class="zakat-result-header">
                                <h3>Hasil Perhitungan</h3>

                                <span class="zakat-result-badge">
                                    {{ ucfirst(session('jenis', 'Zakat')) }}
                                </span>
                            </div>

                            <p class="zakat-result-title">
                                Total Zakat yang Harus Dibayarkan
                            </p>

                            <h1 class="zakat-result-amount">
                                Rp{{ number_format(session('hasil'), 0, ',', '.') }}
                            </h1>

                            <p class="zakat-result-subtitle">
                                ({{ session('persentase', 2.5) }}% dari
                                Rp{{ number_format(session('dasar', 0), 0, ',', '.') }})
                            </p>

                            <div class="zakat-result-detail">

                                <div class="zakat-result-row">
                                    <span>Total Harta</span>
                                    <strong>
                                        Rp{{ number_format(session('harta', 0), 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="zakat-result-row">
                                    <span>Total Hutang</span>
                                    <strong class="text-danger">
                                        -Rp{{ number_format(session('hutang', 0), 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="zakat-result-row">
                                    <span>Harta Bersih (Nisab)</span>
                                    <strong>
                                        Rp{{ number_format(session('nishab', 0), 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="zakat-result-row">
                                    <span>Persentase Zakat</span>
                                    <strong>
                                        {{ session('persentase', 2.5) }}%
                                    </strong>
                                </div>

                                <div class="zakat-result-row total">
                                    <span>Zakat yang Dikeluarkan</span>
                                    <strong class="text-success">
                                        Rp{{ number_format(session('hasil'), 0, ',', '.') }}
                                    </strong>
                                </div>

                            </div>

                            <div class="zakat-info-card">

                                <div class="info-icon">
                                    <i class="fa-regular fa-bookmark"></i>
                                </div>

                                <div>

                                    <h4>Dasar Perhitungan</h4>

                                    <p>
                                        {{ session(
                    'dasar_hukum',
                    'Zakat dihitung sebesar 2,5% dari harta bersih (setelah dikurangi hutang) yang telah mencapai nisab dan dimiliki selama 1 tahun (haul).'
                ) }}
                                    </p>

                                </div>

                            </div>

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
    <script>
        window.selectedZakat = "{{ session('selected_zakat', 'penghasilan') }}";
    </script>
</body>

</html>