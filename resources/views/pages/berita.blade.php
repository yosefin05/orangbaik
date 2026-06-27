<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/berita.css') }}">
</head>
<body>

@include('components.header')

@php
    $beritas = [
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

<main class="berita-page">
    <section class="container berita-section">
        <div class="berita-grid">
            @foreach ($beritas as $berita)
                <article class="berita-card">
                    <img 
                        src="{{ asset($berita['image']) }}" 
                        alt="{{ $berita['title'] }}"
                        class="berita-image"
                        loading="lazy"
                    >

                    <div class="berita-body">
                        <h2>{{ $berita['title'] }}</h2>
                        <p>{{ $berita['date'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</main>

@include('components.footer')

</body>
</html>