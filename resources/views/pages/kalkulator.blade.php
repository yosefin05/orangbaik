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

    @php
        $selectedZakat = session('selected_zakat', session('jenis', 'penghasilan'));
    @endphp

    <main class="zakat-page">

        {{-- HERO --}}
        <section class="zakat-hero">
            <div class="container zakat-hero-layout">

                <div class="zakat-hero-content">
                    <span class="zakat-eyebrow">Kalkulator Zakat</span>

                    <h1>Hitung zakat Anda dengan mudah dan cepat</h1>

                    <p>
                        Gunakan kalkulator zakat untuk membantu memperkirakan zakat yang perlu
                        dikeluarkan berdasarkan jenis harta dan kondisi keuangan Anda.
                    </p>

                    <div class="zakat-hero-actions">
                        <a href="#zakatCalculator" class="zakat-primary-link">
                            Mulai Hitung
                        </a>
                    </div>
                </div>

                <div class="zakat-hero-image">
                    <img src="{{ asset('assets/zakat.svg') }}" alt="Ilustrasi kalkulator zakat">
                </div>

            </div>
        </section>

        {{-- INFO --}}
        <section class="zakat-info-section">
            <div class="container">
                <div class="zakat-info-banner">
                    <div class="zakat-info-main">
                        <span class="zakat-info-icon">
                            <i class="bi bi-info-circle-fill"></i>
                        </span>

                        <div>
                            <strong>Perhitungan zakat bersifat estimasi</strong>
                            <p>
                                Hasil perhitungan dapat digunakan sebagai panduan awal.
                                Pastikan kembali dengan ketentuan lembaga zakat atau ustaz tepercaya.
                            </p>
                        </div>
                    </div>

                    <a href="https://baznas.go.id/assets/pdf/ppid/tentang%20zakat/SK_01_2024.pdf" target="_blank"
                        rel="noopener noreferrer">
                        <span>Baca Panduan</span>
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </section>

        {{-- CALCULATOR --}}
        <section class="zakat-calculator-section" id="zakatCalculator">
            <div class="container zakat-calculator-layout">

                {{-- LEFT --}}
                <div class="zakat-panel zakat-form-panel">

                    <div class="zakat-panel-header">
                        <span class="zakat-step">01</span>

                        <div>
                            <h2>Pilih Jenis Zakat</h2>
                            <p>Pilih jenis zakat, lalu isi data sesuai kondisi Anda.</p>
                        </div>
                    </div>

                    <div class="zakat-tabs" role="tablist" aria-label="Jenis zakat">
                        <button type="button" class="zakat-tab active" data-zakat="penghasilan">
                            <span class="zakat-tab-icon">
                                <i class="bi bi-briefcase-fill"></i>
                            </span>

                            <span class="zakat-tab-content">
                                <strong>Penghasilan</strong>
                                <small>Gaji, honor, atau pendapatan rutin.</small>
                            </span>
                        </button>

                        <button type="button" class="zakat-tab" data-zakat="emas">
                            <span class="zakat-tab-icon">
                                <i class="bi bi-gem"></i>
                            </span>

                            <span class="zakat-tab-content">
                                <strong>Emas</strong>
                                <small>Kepemilikan emas yang mencapai nisab.</small>
                            </span>
                        </button>

                        <button type="button" class="zakat-tab" data-zakat="tabungan">
                            <span class="zakat-tab-icon">
                                <i class="bi bi-bank2"></i>
                            </span>

                            <span class="zakat-tab-content">
                                <strong>Tabungan</strong>
                                <small>Simpanan uang, deposito, atau investasi.</small>
                            </span>
                        </button>

                        <button type="button" class="zakat-tab" data-zakat="perdagangan">
                            <span class="zakat-tab-icon">
                                <i class="bi bi-bar-chart-fill"></i>
                            </span>

                            <span class="zakat-tab-content">
                                <strong>Perniagaan</strong>
                                <small>Aset, stok barang, dan keuntungan usaha.</small>
                            </span>
                        </button>
                    </div>

                    <form action="{{ url('/kalkulator/hitung') }}" method="POST" id="zakatForm" class="zakat-form">
                        @csrf

                        <input type="hidden" id="jenis" name="jenis" value="{{ $selectedZakat }}">
                        <input type="hidden" id="total_harta" name="total_harta" value="0">
                        <input type="hidden" id="total_hutang" name="total_hutang" value="0">
                        <input type="hidden" id="estimasi_zakat" name="estimasi_zakat" value="0">

                        <div class="zakat-form-head">
                            <div>
                                <span class="zakat-form-label">Form Perhitungan</span>
                                <h3 id="formTitle">Zakat Penghasilan</h3>
                            </div>

                            <div class="zakat-nisab-box">
                                <strong>Nisab</strong>
                                <span id="nisabInfo">-</span>
                            </div>
                        </div>

                        <div id="formFields" class="zakat-fields"></div>

                        <div class="zakat-live-preview">
                            <div>
                                <span>Estimasi zakat</span>
                                <strong id="liveZakatAmount">Rp0</strong>
                            </div>

                            <p id="liveZakatNote">
                                Isi data untuk melihat estimasi awal sebelum dihitung oleh sistem.
                            </p>
                        </div>

                        <button type="submit" class="zakat-submit-button">
                            <i class="bi bi-calculator-fill"></i>
                            <span>Lihat Secara Rinci</span>
                        </button>
                    </form>

                </div>

                {{-- RIGHT --}}
                <aside class="zakat-result-area">

                    <div class="zakat-result-card is-empty" id="zakatResultCard">

                        <div class="zakat-result-header">
                            <div>
                                <span class="zakat-result-label">Hasil Perhitungan</span>
                                <h2 id="resultType">Belum Ada Hasil</h2>
                            </div>

                            <span class="zakat-result-badge" id="resultPercent">
                                2.5%
                            </span>
                        </div>

                        <div class="zakat-empty-state" id="zakatResultEmpty">
                            <span>
                                <i class="bi bi-calculator"></i>
                            </span>

                            <p>
                                Isi form di sebelah kiri, lalu klik
                                <strong>Hitung Zakat</strong> untuk melihat hasil.
                            </p>
                        </div>

                        <div class="zakat-result-content" id="zakatResultContent" hidden>

                            <p class="zakat-result-title">
                                Total zakat yang harus dibayarkan
                            </p>

                            <h3 class="zakat-result-amount" id="resultAmount">
                                Rp0
                            </h3>

                            <div class="zakat-result-status" id="resultStatus"></div>

                            <div class="zakat-result-detail"></div>

                            <p class="zakat-result-subtitle" id="resultBase">
                                Dari dasar perhitungan Rp0
                            </p>

                            <div class="zakat-result-detail">
                                <div class="zakat-result-row">
                                    <span>Total Harta</span>
                                    <strong id="resultHarta">Rp0</strong>
                                </div>

                                <div class="zakat-result-row">
                                    <span>Total Hutang</span>
                                    <strong class="text-danger" id="resultHutang">-Rp0</strong>
                                </div>

                                <div class="zakat-result-row">
                                    <span>Harta Bersih</span>
                                    <strong id="resultBersih">Rp0</strong>
                                </div>

                                <div class="zakat-result-row total">
                                    <span>Zakat Dikeluarkan</span>
                                    <strong class="text-success" id="resultFinal">Rp0</strong>
                                </div>
                            </div>

                            <div class="zakat-law-card">
                                <span>
                                    <i class="bi bi-bookmark-check-fill"></i>
                                </span>

                                <div>
                                    <h4>Dasar Perhitungan</h4>
                                    <p id="resultLaw">
                                        Zakat dihitung berdasarkan harta bersih yang mencapai nisab.
                                    </p>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="zakat-action-card" id="zakatRecommendationCard" hidden>
                        <h3>Rekomendasi Selanjutnya</h3>

                        <p>
                            Salurkan zakat Anda untuk membantu mereka yang membutuhkan.
                        </p>

                        <a href="{{ url('/donasi/zakat') }}" class="zakat-pay-button">
                            <i class="bi bi-heart-fill"></i>
                            <span>Salurkan Zakat Sekarang</span>
                        </a>

                        <button type="button" class="zakat-print-button" data-print-zakat>
                            <i class="bi bi-printer-fill"></i>
                            <span>Simpan Perhitungan</span>
                        </button>
                    </div>

                </aside>
            </div>
        </section>

        {{-- SYARAT --}}
        <section class="zakat-terms-section">
            <div class="container">

                <div class="zakat-section-heading">
                    <span>Syarat Zakat</span>
                    <h2>Syarat dan Ketentuan Zakat</h2>
                    <p>
                        Berikut beberapa syarat umum yang biasanya menjadi dasar kewajiban zakat.
                    </p>
                </div>

                <div class="zakat-terms-grid">
                    <article class="zakat-term-card">
                        <span>
                            <i class="bi bi-cash-stack"></i>
                        </span>

                        <h3>Mencapai Nisab</h3>
                        <p>Harta telah mencapai batas minimum yang ditentukan.</p>
                    </article>

                    <article class="zakat-term-card">
                        <span>
                            <i class="bi bi-calendar-check-fill"></i>
                        </span>

                        <h3>Genap 1 Tahun</h3>
                        <p>Harta tertentu telah dimiliki selama satu tahun atau haul.</p>
                    </article>

                    <article class="zakat-term-card">
                        <span>
                            <i class="bi bi-graph-up-arrow"></i>
                        </span>

                        <h3>Harta Berkembang</h3>
                        <p>Harta memiliki potensi berkembang atau menghasilkan.</p>
                    </article>

                    <article class="zakat-term-card">
                        <span>
                            <i class="bi bi-check-circle-fill"></i>
                        </span>

                        <h3>Melebihi Kebutuhan Pokok</h3>
                        <p>Harta melebihi kebutuhan pokok dan tanggungan utama.</p>
                    </article>
                </div>

            </div>
        </section>

    </main>

     <!-- FLOATING WHATSAPP BUTTON -->
    @if(env('ENABLE_WA_FLOATING', true))
        <div class="floating-wa-container">
            <a href="https://wa.me/{{ env('WHATSAPP_NUMBER', '6281385002300') }}?text={{ urlencode(env('WHATSAPP_MESSAGE', 'Halo tim OrangBaik.id, saya mau bertanya mengenai...')) }}"
                target="_blank" rel="noopener noreferrer" class="floating-wa-btn"
                aria-label="Hubungi Customer Service via WhatsApp">
                <div class="wa-icon-wrapper">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <span class="wa-tooltip">Hubungi CS</span>
                <span class="wa-badge">Online</span>
            </a>
        </div>
    @endif

    @include('components.footer')

    <script>
        window.selectedZakat = @json($selectedZakat);
    window.zakatOldInput = @json(old());
    </script>
    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/kalkulator.js') }}"></script>

</body>

</html>