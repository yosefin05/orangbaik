<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Berita - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail-berita.css') }}">
</head>
<body>

@include('components.header')

@php
    $relatedNews = [
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

<main class="detail-news-page">
    <div class="container detail-news-container">

        <div class="detail-news-layout">

            {{-- MAIN CONTENT --}}
            <article class="news-detail-main">

                <img 
                    src="{{ asset('assets/banjir.jpg') }}" 
                    alt="DQ Salurkan Bantuan makanan siap saji untuk penyintas banjir di Mojokerto"
                    class="news-hero-image"
                >

                <h1>DQ Salurkan Bantuan makanan siap saji untuk penyintas banjir di Mojokerto</h1>

                <div class="news-meta-row">
                    <span>Muhammad Pungky Setiawan</span>
                    <span>November 6, 2024</span>
                </div>

                <div class="news-content">
                    <p>
                        Musibah banjir yang melanda wilayah di beberapa wilayah di Kota Mojokerto, Genangan air dengan
                        ketinggian bervariasi telah merendam rumah-rumah warga, mengganggu akses jalan dan memengaruhi
                        aktivitas masyarakat. Sebagai bentuk kepedulian, LAZ Dompet Al-Qur'an Indonesia (DQ) telah
                        menyalurkan bantuan makanan siap saji kepada warga terdampak banjir di Jalan Prajurit Kulon Gang 6,
                        Mergelo, Kota Mojokerto, Pada tanggal 14-15 Desember 2024.
                    </p>

                    <p>
                        Bantuan ini juga dari berkat dukungan para donatur, puluhan paket makanan siap saji berhasil
                        didistribusikan selama dua hari kepada warga yang membutuhkan. Bantuan ini menjadi salah satu
                        langkah nyata untuk membantu memenuhi kebutuhan dasar saudara-saudara kita di lokasi terdampak.
                    </p>

                    <p>
                        Ifan, perwakilan dari DQ Cabang Mojokerto, menyampaikan rasa syukurnya atas terlaksananya
                        penyaluran bantuan ini. “Kami sangat berterima kasih kepada seluruh donatur yang telah
                        mempercayakan bantuannya melalui LAZ DQ. Bantuan ini adalah bentuk kepedulian bersama untuk
                        meringankan beban warga terdampak banjir. Kami berharap bantuan ini dapat memberikan sedikit
                        kelegaan di tengah kondisi yang sulit. Mari terus berdoa agar saudara-saudara kita senantiasa
                        diberikan perlindungan dan kekuatan oleh Allah SWT dalam menghadapi musibah ini,” ujar Ifan.
                    </p>

                    <p>
                        Terima kasih yang sebesar-besarnya kepada seluruh donatur atas kontribusi dan kepercayaannya
                        kepada LAZ DQ. Semoga Allah SWT membalas setiap kebaikan dengan keberkahan yang melimpah.
                    </p>
                </div>

                {{-- COMMENT --}}
                <section class="comment-section">
                    <div class="comment-header">
                        <strong>Komentar</strong>
                        <span>0</span>
                    </div>

                    <form class="comment-form" action="#" method="POST">
                        @csrf

                        <input type="text" name="komentar" placeholder="Tulis Komentar">

                        <button type="submit" aria-label="Kirim komentar">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 20L22 12L3 4V10L16 12L3 14V20Z" />
                            </svg>
                        </button>
                    </form>

                    <div class="empty-comment">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M7 18L4 21V6C4 4.9 4.9 4 6 4H18C19.1 4 20 4.9 20 6V16C20 17.1 19.1 18 18 18H7Z" />
                                <path d="M8 10H8.01" />
                                <path d="M12 10H12.01" />
                                <path d="M16 10H16.01" />
                            </svg>
                        </div>

                        <p>Tidak Ada Komentar</p>
                    </div>
                </section>

            </article>

            {{-- SIDEBAR --}}
            <aside class="news-sidebar">
                @foreach ($relatedNews as $news)
                    <article class="sidebar-news-card">
                        <img src="{{ asset($news['image']) }}" alt="{{ $news['title'] }}">

                        <div class="sidebar-news-body">
                            <h2>{{ $news['title'] }}</h2>
                            <p>{{ $news['date'] }}</p>
                        </div>
                    </article>
                @endforeach
            </aside>

        </div>

    </div>
</main>

@include('components.footer')

</body>
</html>