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
        @if (session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="container detail-container">

            <div class="detail-layout">

                {{-- LEFT CONTENT --}}
                <div class="detail-main">
                    <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}"
                        class="campaign-hero-image">

                    <section class="description-section">
                        <h1>{{ $campaign->judul }}</h1>
                        <p>
                            {{ $campaign->deskripsi }}

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
                                        <h3>{{ $campaign->judul }} </h3>
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
                        <h2>{{ $campaign->judul }}</h2>

                        <div class="summary-amount">
                            <strong>Rp {{ number_format($campaign->terkumpul ?? 0, 0, ',', '.') }}
                            </strong>
                            <span>
                                Terkumpul dari
                                <b>
                                    Rp {{ number_format($campaign->target_donasi, 0, ',', '.') }}
                                </b>
                            </span>
                        </div>

                        @php
                            $persen = $campaign->target_donasi > 0
                                ? (($campaign->terkumpul ?? 0) / $campaign->target_donasi) * 100
                                : 0;

                            $persen = min($persen, 100);
                        @endphp


                        <div class="summary-progress">
                            <div style="width: {{ $persen }}%"></div>
                        </div>

                        <div class="summary-meta">
                            <span>
                                👤 {{ $campaign->donasi_count ?? 0 }} donatur
                            </span>

                            <span>
                                @php
                                    use Carbon\Carbon;

                                    $hariIni = Carbon::today();
                                    $mulai = Carbon::parse($campaign->tanggal_mulai)->startOfDay();
                                    $akhir = Carbon::parse($campaign->tanggal_berakhir)->endOfDay();

                                    if ($hariIni->lt($mulai)) {
                                        $statusHari = 'Mulai dalam ' . $hariIni->diffInDays($mulai) . ' hari';
                                    } elseif ($hariIni->gt($akhir)) {
                                        $statusHari = 'Campaign berakhir';
                                    } else {
                                        $sisaHari = (int) $hariIni->diffInDays($akhir);

                                        $statusHari = $sisaHari == 0
                                            ? 'Hari terakhir'
                                            : $sisaHari . ' Hari lagi';
                                    }
                                @endphp
                                {{ $statusHari }}
                            </span>
                        </div>
                        <a href="#" class="donate-button">Donasi Sekarang</a>
                    </div>

                    <div class="fundraiser-info-card">
                        <h3>Informasi Penggalang Dana</h3>

                        <div class="fundraiser-profile">
                            <img src="{{ asset('storage/' . $campaign->penggalangDana->foto_profil) }}">
                            <div>
                                <h4>{{ $campaign->penggalangDana->nama_penggalang ?? 'Orang Baik' }}
                                </h4>
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