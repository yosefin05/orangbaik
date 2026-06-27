<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Campaign - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail-campaign.css') }}">
</head>
<body>

@include('components.header')

@php
    $donations = [
        ['name' => 'Orang Baik', 'amount' => 'Rp10.000', 'time' => '1 Jam lalu'],
        ['name' => 'yayayay', 'amount' => 'Rp100.000', 'time' => '1 Jam lalu'],
        ['name' => 'Josie Raditya', 'amount' => 'Rp1.000.000', 'time' => '1 Jam lalu'],
    ];

    $fundraisers = [
        ['name' => 'Orang Baik', 'desc' => 'Berhasil mengajak 1 orang untuk berdonasi', 'amount' => 'Rp100.000'],
        ['name' => 'Fundraiser Baik', 'desc' => 'Berhasil mengajak 10 orang untuk berdonasi', 'amount' => 'Rp1.000.000'],
        ['name' => 'Rinto Aji', 'desc' => 'Berhasil mengajak 100 orang untuk berdonasi', 'amount' => 'Rp10.000.000'],
    ];

    $news = [
        [
            'title' => 'Bangkitkan Semangat Belajar, LAZ DQ Dirikan Sekolah Darurat untuk Anak Gaza',
            'date' => '12 Desember 2024',
            'image' => 'assets/slide1.png',
        ],
        [
            'title' => 'Bangkitkan Semangat Belajar, LAZ DQ Dirikan Sekolah Darurat untuk Anak Gaza',
            'date' => '12 Desember 2024',
            'image' => 'assets/slide1.png',
        ],
    ];
@endphp

<main class="campaign-detail-page">
    <div class="container detail-container">

        <div class="detail-layout">

            {{-- LEFT CONTENT --}}
            <div class="detail-main">

                <img 
                    src="{{ asset('assets/slide1.png') }}" 
                    alt="Beasiswa Yatim Dhuafa"
                    class="campaign-hero-image"
                >

                <section class="description-section">
                    <h1>Deskripsi</h1>

                    <p>
                        Pendidikan merupakan fondasi utama untuk kemajuan suatu bangsa, membuka jalan menuju kehidupan
                        yang sejahtera dan berdaya saing. Namun, tidak semua anak memiliki keberuntungan yang sama.
                        Banyak anak-anak di Indonesia yang berasal dari keluarga kurang mampu atau dhuafa menghadapi
                        risiko putus sekolah, menghalangi mereka untuk mendapatkan pendidikan yang layak dan menggapai
                        cita-cita mereka.
                    </p>

                    <p>
                        Menurut laporan Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi (Kemendikbud Ristek),
                        saat ini ada 76.834 anak yang putus sekolah pada tahun 2023.
                    </p>

                    <p>
                        Melalui Program Beasiswa Yatim Dhuafa, Dompet Al-Qur’an Indonesia berkomitmen untuk membantu
                        Anak-anak Yatim Dhuafa Penghafal Al-Qur’an dalam mewujudkan impian mereka mendapatkan pendidikan
                        yang layak. Program ini dirancang untuk memberikan dukungan finansial dan moral, memastikan
                        anak-anak ini memiliki kesempatan untuk melanjutkan pendidikan mereka tanpa terkendala oleh
                        kondisi ekonomi.
                    </p>

                    <p>
                        Insya Allah dengan kita mengambil langkah nyata mendukung pendidikan Anak-anak Yatim Dhuafa
                        Penghafal Al-Qur’an untuk bisa sekolah kelak kita di surga akan berada di dekat Nabi Muhammad SAW.
                    </p>

                    <p>
                        Rasulullah shallallahu ‘alaihi wa sallam bersabda: “Aku dan orang yang menanggung anak yatim
                        kedudukannya di surga seperti ini”, kemudian beliau shallallahu ‘alaihi wa sallam mengisyaratkan
                        jari telunjuk dan jari tengah beliau shallallahu ‘alaihi wa sallam, serta agak merenggangkan
                        keduanya” (HR Bukhari).
                    </p>

                    <p>
                        Oleh karena itu, kami mengajak Sahabat DQ dalam memberikan kontribusi bersama dalam membantu
                        biaya beasiswa pendidikan Anak-anak Yatim dan Dhuafa Penghafal Al-Qur’an yang membutuhkan.
                    </p>
                </section>

                {{-- NEWS --}}
                <section class="latest-news-section">
                    <h2>Kabar Terbaru</h2>

                    <div class="news-grid">
                        @foreach ($news as $item)
                            <article class="news-card">
                                <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}">

                                <div class="news-body">
                                    <h3>{{ $item['title'] }}</h3>
                                    <p>{{ $item['date'] }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                {{-- PRAYER --}}
                <section class="prayer-section">
                    <h2>Doa #OrangBaik</h2>

                    <div class="prayer-card">
                        <div class="prayer-user">
                            <div class="avatar-circle">👤</div>

                            <div>
                                <h3>Rinto Aji Pambudi</h3>
                                <p>1 Jam yang lalu</p>
                            </div>
                        </div>

                        <p class="prayer-text">
                            Mudahkanlah setiap langkah kami, kuatkan hati kami dalam menghadapi setiap tantangan,
                            dan berikan hasil terbaik dari setiap usaha yang kami lakukan.
                        </p>

                        <div class="prayer-footer">
                            <span>5 orang lainnya telah mengaminkan doa ini</span>
                            <button type="button">🤍 Aamiin kan doa ini</button>
                        </div>
                    </div>
                </section>

            </div>

            {{-- RIGHT SIDEBAR --}}
            <aside class="detail-sidebar">

                <div class="donation-summary-card">
                    <h2>Gotong Royong Infaq Jariyah Hadirkan Layanan Ambulan Gratis</h2>

                    <div class="summary-amount">
                        <strong>Rp 200.000.000</strong>
                        <span>Terkumpul dari <b>Rp 500.000.000</b></span>
                    </div>

                    <div class="summary-progress">
                        <div></div>
                    </div>

                    <div class="summary-meta">
                        <span>👤 1rb ± donatur</span>
                        <span>∞ Hari lagi</span>
                    </div>

                    <a href="#" class="donate-button">Donasi Sekarang</a>
                </div>

                <div class="fundraiser-info-card">
                    <h3>Informasi Penggalang Dana</h3>

                    <div class="fundraiser-profile">
                        <img src="{{ asset('assets/logo-icon.png') }}" alt="Dompet Al-Quran Indonesia">

                        <div>
                            <h4>Dompet Al-Quran Indonesia</h4>
                            <span>✓ .org</span>
                        </div>
                    </div>
                </div>

                <div class="side-list-card">
                    <h3>Donasi</h3>

                    @foreach ($donations as $donation)
                        <div class="side-list-item">
                            <div class="avatar-circle">👤</div>

                            <div>
                                <h4>{{ $donation['name'] }}</h4>
                                <p>Berdonasi sebesar <b>{{ $donation['amount'] }}</b></p>
                                <span>{{ $donation['time'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="side-list-card">
                    <h3>Fundraiser</h3>

                    @foreach ($fundraisers as $fundraiser)
                        <div class="side-list-item">
                            <div class="avatar-circle">👤</div>

                            <div>
                                <h4>{{ $fundraiser['name'] }}</h4>
                                <p>{{ $fundraiser['desc'] }}</p>
                                <b>{{ $fundraiser['amount'] }}</b>
                            </div>
                        </div>
                    @endforeach
                </div>

            </aside>

        </div>

    </div>
</main>

@include('components.footer')

</body>
</html>