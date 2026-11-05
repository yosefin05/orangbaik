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

               {{-- ==========================================
            1. HERO
        =========================================== --}}
        <section class="z-hero">
            <div class="container z-hero-grid">

                <div class="z-hero-copy">

                    <span class="z-eyebrow">
                        <i class="bi bi-calculator"></i>
                        Kalkulator Zakat
                    </span>

                    <h1>Zakat itu hitungan pasti, bukan tebakan.</h1>

                    <p>
                        Masukkan penghasilan, emas, tabungan, atau aset niaga Anda.
                        Kalkulator ini menghitung nisab dan besaran zakat sesuai
                        ketentuan yang berlaku, lengkap dengan rinciannya.
                    </p>

                    <a href="#zakatCalculator" class="z-cta">Mulai Hitung</a>

                </div>

                <ul class="z-hero-facts">
                    <li class="z-fact">
                        <span class="z-fact-icon"><i class="bi bi-percent"></i></span>
                        <span class="z-fact-text">
                            <strong>2,5%</strong>
                            <small>Tarif zakat</small>
                        </span>
                    </li>

                    <li class="z-fact">
                        <span class="z-fact-icon"><i class="bi bi-gem"></i></span>
                        <span class="z-fact-text">
                            <strong>85 gram</strong>
                            <small>Nisab emas</small>
                        </span>
                    </li>

                    <li class="z-fact">
                        <span class="z-fact-icon"><i class="bi bi-calendar-check"></i></span>
                        <span class="z-fact-text">
                            <strong>1 tahun</strong>
                            <small>Haul</small>
                        </span>
                    </li>
                </ul>

            </div>
        </section>


        {{-- ==========================================
            2. KALKULATOR
        =========================================== --}}
        <section class="zakat-calculator-section" id="zakatCalculator">
            <div class="container zakat-calculator-layout">

                {{-- KIRI: FORM --}}
                <div class="zakat-panel z-ledger">

                    <div class="z-ledger-header">
                        <h2>Pilih jenis zakat</h2>
                        <p>Setiap jenis punya cara hitung dan nisab yang berbeda.</p>
                    </div>

                    <div class="z-tabbar" role="tablist" aria-label="Jenis zakat">
                        <button type="button" class="zakat-tab active" data-zakat="penghasilan">
                            <i class="bi bi-briefcase-fill"></i>
                            <span class="zakat-tab-content"><strong>Penghasilan</strong></span>
                        </button>

                        <button type="button" class="zakat-tab" data-zakat="emas">
                            <i class="bi bi-gem"></i>
                            <span class="zakat-tab-content"><strong>Emas</strong></span>
                        </button>

                        <button type="button" class="zakat-tab" data-zakat="tabungan">
                            <i class="bi bi-bank2"></i>
                            <span class="zakat-tab-content"><strong>Tabungan</strong></span>
                        </button>

                        <button type="button" class="zakat-tab" data-zakat="perdagangan">
                            <i class="bi bi-bar-chart-fill"></i>
                            <span class="zakat-tab-content"><strong>Perniagaan</strong></span>
                        </button>
                    </div>

                    <form action="{{ url('/kalkulator/hitung') }}" method="POST" id="zakatForm" class="zakat-form">
                        @csrf

                        <input type="hidden" id="jenis" name="jenis" value="{{ $selectedZakat }}">
                        <input type="hidden" id="total_harta" name="total_harta" value="0">
                        <input type="hidden" id="total_hutang" name="total_hutang" value="0">
                        <input type="hidden" id="estimasi_zakat" name="estimasi_zakat" value="0">

                        <div class="z-form-head">
                            <h3 id="formTitle">Zakat Penghasilan</h3>

                            <div class="z-nisab">
                                <span>Nisab saat ini</span>
                                <strong id="nisabInfo">-</strong>
                            </div>
                        </div>

                        <div id="formFields" class="zakat-fields"></div>

                        <div class="zakat-live-preview">
                            <div>
                                <span>Estimasi zakat</span>
                                <strong id="liveZakatAmount">Rp0</strong>
                            </div>

                            <p id="liveZakatNote">
                                Isi data untuk melihat estimasi sebelum dihitung sistem.
                            </p>
                        </div>

                        <button type="submit" class="zakat-submit-button">
                            <i class="bi bi-calculator-fill"></i>
                            <span>Lihat Secara Rinci</span>
                        </button>
                    </form>

                    <p class="z-note">
                        <i class="bi bi-info-circle"></i>
                        <span>
                            Perhitungan ini bersifat estimasi. Untuk kepastian, cocokkan dengan
                            <a href="https://baznas.go.id/assets/pdf/ppid/tentang%20zakat/SK_01_2024.pdf" target="_blank" rel="noopener noreferrer">panduan BAZNAS</a>
                            atau ustadz tepercaya.
                        </span>
                    </p>

                </div>

                {{-- KANAN: HASIL --}}
                <aside class="zakat-result-area">

                    <div class="zakat-result-card is-empty" id="zakatResultCard">

                        <div class="zakat-result-header">
                            <h2 id="resultType">Belum Ada Hasil</h2>
                            <span class="zakat-result-badge" id="resultPercent">2.5%</span>
                        </div>

                        <div class="zakat-empty-state" id="zakatResultEmpty">
                            <i class="bi bi-receipt"></i>
                            <p>Isi form untuk melihat rincian zakat Anda di sini.</p>
                        </div>

                        <div class="zakat-result-content" id="zakatResultContent" hidden>

                            <p class="zakat-result-title">Total zakat yang harus dibayarkan</p>
                            <h3 class="zakat-result-amount" id="resultAmount">Rp0</h3>
                            <div class="zakat-result-status" id="resultStatus"></div>

                            <div class="zakat-result-detail"></div>

                            <p class="zakat-result-subtitle" id="resultBase">Dari dasar perhitungan Rp0</p>

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
                                <i class="bi bi-bookmark-check-fill"></i>
                                <div>
                                    <h4>Dasar Perhitungan</h4>
                                    <p id="resultLaw">Zakat dihitung berdasarkan harta bersih yang mencapai nisab.</p>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="zakat-action-card" id="zakatRecommendationCard" hidden>
                        <h3>Sudah tahu jumlahnya?</h3>
                        <p>Salurkan zakat Anda sekarang untuk membantu mereka yang membutuhkan.</p>

                        <a href="{{ url('/donasi') }}" class="zakat-pay-button">
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


        {{-- ==========================================
            3. SYARAT WAJIB ZAKAT
        =========================================== --}}
        <section class="z-terms-section">
            <div class="container">

                <div class="z-terms-heading">
                    <h2>Syarat wajib zakat</h2>
                    <p>Empat syarat umum yang jadi dasar kewajiban zakat.</p>
                </div>

                <div class="z-terms-list">
                    <div class="z-term-row">
                        <i class="bi bi-check2"></i>
                        <div>
                            <strong>Mencapai nisab</strong>
                            <p>Harta telah mencapai batas minimum yang ditentukan.</p>
                        </div>
                    </div>

                    <div class="z-term-row">
                        <i class="bi bi-check2"></i>
                        <div>
                            <strong>Genap satu tahun (haul)</strong>
                            <p>Harta tertentu telah dimiliki selama satu tahun penuh.</p>
                        </div>
                    </div>

                    <div class="z-term-row">
                        <i class="bi bi-check2"></i>
                        <div>
                            <strong>Harta berkembang</strong>
                            <p>Harta memiliki potensi berkembang atau menghasilkan.</p>
                        </div>
                    </div>

                    <div class="z-term-row">
                        <i class="bi bi-check2"></i>
                        <div>
                            <strong>Melebihi kebutuhan pokok</strong>
                            <p>Harta melebihi kebutuhan pokok dan tanggungan utama.</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

    </main>

    {{-- FLOATING WHATSAPP BUTTON --}}
    @if (env('ENABLE_WA_FLOATING', true))
        <div class="floating-wa-container">
            <a href="https://wa.me/{{ env('WHATSAPP_NUMBER', '6281385002300') }}?text={{ urlencode(env('WHATSAPP_MESSAGE', 'Halo tim OrangBaik.id, saya mau bertanya mengenai...')) }}"
                target="_blank" rel="noopener noreferrer" class="floating-wa-btn"
                aria-label="Hubungi Customer Service via WhatsApp">
                <div class="wa-icon-wrapper">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <span class="wa-tooltip">Hubungi CS</span>
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