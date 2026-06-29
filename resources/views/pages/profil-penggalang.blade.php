<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Penggalang Dana - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profil-penggalang.css') }}">
</head>
<body>

@php
    $campaigns = [
        [
            'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulan Gratis',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 200.000.000',
            'image' => 'assets/slide1.png',
        ],
        [
            'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulan Gratis',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 200.000.000',
            'image' => 'assets/slide1.png',
        ],
        [
            'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulan Gratis',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 200.000.000',
            'image' => 'assets/slide1.png',
        ],
        [
            'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulan Gratis',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 200.000.000',
            'image' => 'assets/slide1.png',
        ],
        [
            'title' => 'Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulan Gratis',
            'organizer' => "Dompet Al-Qur'an Indonesia",
            'amount' => 'Rp 200.000.000',
            'image' => 'assets/slide1.png',
        ],
    ];
@endphp

<main class="fundraiser-profile-page">

    <section class="fundraiser-hero">
        <div class="fundraiser-container">

            <button class="fundraiser-back" type="button" onclick="history.back()">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M15 18L9 12L15 6" />
                </svg>
                <span>Kembali</span>
            </button>

            <img 
                src="{{ asset('assets/profile-banner.png') }}" 
                alt="Banner Dompet Al-Qur'an Indonesia"
                class="fundraiser-banner"
            >

        </div>
    </section>

    <section class="fundraiser-content">
        <div class="fundraiser-container">

            {{-- PROFILE SUMMARY --}}
            <div class="fundraiser-summary">
                <div class="fundraiser-info">
                    <img 
                        src="{{ asset('assets/logo-icon.png') }}" 
                        alt="Dompet Al-Qur'an Indonesia"
                        class="fundraiser-logo"
                    >

                    <div>
                        <h1>Dompet Al-Qur'an Indonesia</h1>

                        <div class="verified-row">
                            <span class="org-badge">✓ .org</span>
                            <span>Verified Organization</span>
                        </div>
                    </div>
                </div>

                <div class="fundraiser-actions">
                    <a href="#" class="dashboard-link">Dashboard</a>
                    <a href="#" class="edit-button">Edit</a>
                </div>
            </div>

            {{-- DETAIL INFO --}}
            <section class="accordion-list">

                <details class="info-card" open>
                    <summary>
                        <span>Informasi Organisasi</span>
                        <b>⌄</b>
                    </summary>

                    <div class="info-table">
                        <div>
                            <strong>• Nama Organisasi</strong>
                            <p>Dompet Al-Qur'an Indonesia</p>
                        </div>

                        <div>
                            <strong>• Bentuk Organisasi</strong>
                            <p>Yayasan</p>
                        </div>

                        <div>
                            <strong>• Tahun Berdiri</strong>
                            <p>Tahun 2011</p>
                        </div>

                        <div>
                            <strong>• Lokasi</strong>
                            <p>Ruko Citra City Blok R No. 28, Sarirogo, Sidoarjo, Jawa Timur, 61234 - Sidoarjo, Kab. Sidoarjo, Jawa Timur</p>
                        </div>
                    </div>
                </details>

                <details class="info-card" open>
                    <summary>
                        <span>Tentang Penggalang</span>
                        <b>⌄</b>
                    </summary>

                    <div class="paragraph-content">
                        <p>
                            Dompet Al-Qur’an Indonesia (DQ) adalah Lembaga Amil Zakat dan Nazhir Wakaf resmi yang berada
                            di bawah naungan Kementerian Agama RI dan Badan Wakaf Indonesia (BWI). DQ telah teraudit
                            dengan predikat Wajar Tanpa Pengecualian (WTP) sebagai bentuk komitmen terhadap transparansi
                            dan akuntabilitas.
                        </p>

                        <p>
                            DQ mengelola dana Zakat, Infaq, Sedekah, dan Wakaf untuk disalurkan melalui berbagai program,
                            seperti: Pendidikan, Ekonomi, Dakwah, dan Kemanusiaan, demi mewujudkan kesejahteraan
                            masyarakat secara berkelanjutan.
                        </p>
                    </div>
                </details>

                <details class="info-card" open>
                    <summary>
                        <span>Visi Misi</span>
                        <b>⌄</b>
                    </summary>

                    <div class="paragraph-content">
                        <h3>Visi</h3>
                        <p>
                            Menjadi Lembaga Profesional dalam Pemberdayaan dan Pelayanan serta membangun masyarakat
                            yang akrab dengan Al-Qur'an.
                        </p>

                        <h3>Misi</h3>
                        <ul>
                            <li>Aktif dalam membangun jaringan filantropi yang profesional</li>
                            <li>Meningkatkan kemandirian dan mengakrabkan masyarakat Indonesia dengan Al-Qur'an</li>
                            <li>Meningkatkan sumber daya melalui keunggulan lembaga</li>
                        </ul>
                    </div>
                </details>

                <details class="info-card" open>
                    <summary>
                        <span>Informasi Legalitas</span>
                        <b>⌄</b>
                    </summary>

                    <div class="info-table">
                        <div>
                            <strong>• Badan Wakaf Indonesia</strong>
                            <p>Dompet Al-Qur'an Indonesia</p>
                        </div>

                        <div>
                            <strong>• Dewan Pimpinan MUI</strong>
                            <p>No. Rek-1954/DP-MUI/VI/2025</p>
                        </div>

                        <div>
                            <strong>• No SK Kemenkumham</strong>
                            <p>AHU-00003862.AH.01.12</p>
                        </div>

                        <div>
                            <strong>• SK LAZ</strong>
                            <p>SK LAZ Kemenag RI No. 78 Tahun 2021.</p>
                        </div>
                    </div>
                </details>

                <details class="info-card" open>
                    <summary>
                        <span>Kontak & Sosial Media</span>
                        <b>⌄</b>
                    </summary>

                    <div class="info-table">
                        <div>
                            <strong>• Email</strong>
                            <p>info@dompetalquran.or.id</p>
                        </div>

                        <div>
                            <strong>• Hotline</strong>
                            <p>+62 813-8500-2300</p>
                        </div>

                        <div>
                            <strong>• Sosial Media</strong>
                            <p>
                                • Instagram &nbsp;&nbsp; @dompetalquran <br>
                                • Facebook &nbsp;&nbsp;&nbsp; @dompetalquran <br>
                                • Youtube &nbsp;&nbsp;&nbsp;&nbsp; @dompetalquran <br>
                                • Tiktok &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; @dompetalquran.id
                            </p>
                        </div>
                    </div>
                </details>

            </section>

            {{-- CAMPAIGN LIST --}}
            <section class="fundraiser-campaign-section">
                <h2>Penggalangan Dana</h2>

                <div class="campaign-list">
                    @foreach ($campaigns as $campaign)
                        <article class="campaign-row">
                            <img 
                                src="{{ asset($campaign['image']) }}" 
                                alt="{{ $campaign['title'] }}"
                                class="campaign-row-image"
                            >

                            <div class="campaign-row-body">
                                <h3>{{ $campaign['title'] }}</h3>

                                <p>
                                    {{ $campaign['organizer'] }}
                                    <span>●</span>
                                </p>

                                <div class="campaign-row-amount">
                                    <strong>{{ $campaign['amount'] }}</strong>
                                    <span>Terkumpul</span>
                                </div>

                                <div class="campaign-progress">
                                    <div></div>
                                </div>

                                <div class="campaign-meta">
                                    <span>👤 100rb ± donatur</span>
                                    <span>∞</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

        </div>
    </section>

</main>

@include('components.footer')

</body>
</html>