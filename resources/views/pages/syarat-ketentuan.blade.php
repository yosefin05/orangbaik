<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/syarat-ketentuan.css') }}">
</head>
<body>

@include('components.header')

<main class="terms-page">

    {{-- HERO --}}
    <section class="terms-hero">
        <div class="container">

            <div class="terms-hero-card">
                <div class="terms-hero-content">
                    <span class="terms-eyebrow">
                        Syarat & Ketentuan
                    </span>

                    <h1>
                        Pahami Syarat dan Ketentuan Kami
                    </h1>

                    <p>
                        Pelajari ketentuan layanan yang berlaku agar Anda dapat menggunakan
                        platform OrangBaik.id dengan aman, nyaman, dan sesuai aturan.
                    </p>
                </div>
            </div>

        </div>
    </section>

    {{-- CONTENT --}}
    <section class="terms-content-section">
        <div class="container terms-layout">

            <aside class="terms-sidebar">
                <h3>Daftar Isi</h3>

                <nav>
                    @forelse ($terms as $index => $term)
                        <a href="#term-{{ $index + 1 }}">
                            {{ $term->judul }}
                        </a>
                    @empty
                        <p>Konten syarat dan ketentuan sedang diperbarui.</p>
                    @endforelse
                </nav>
            </aside>

            <article class="terms-document">
                @forelse ($terms as $index => $term)
                    <section class="terms-block" id="term-{{ $index + 1 }}">
                        <h2>{{ $term->judul }}</h2>

                        @foreach ($term->paragraphs() as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </section>
                @empty
                    <section class="terms-block">
                        <p>Konten syarat dan ketentuan sedang diperbarui.</p>
                    </section>
                @endforelse
            </article>

        </div>
    </section>

    {{-- FAQ --}}
    <section class="terms-faq">
        <div class="container">

            <div class="terms-faq-header">
                <h2>Pertanyaan yang Sering Diajukan</h2>

                <p>
                    Beberapa informasi umum terkait penggunaan layanan OrangBaik.id.
                </p>
            </div>

            <div class="faq-list">
                @forelse ($faqs as $faq)
                    <details class="faq-item">
                        <summary>
                            <span>{{ $faq->pertanyaan }}</span>
                            <i class="bi bi-plus-lg"></i>
                        </summary>

                        <p>
                            {{ $faq->jawaban }}
                        </p>
                    </details>
                @empty
                    <p>Belum ada pertanyaan yang ditampilkan.</p>
                @endforelse
            </div>

        </div>
    </section>

</main>

@include('components.footer')

<script src="{{ asset('js/header.js') }}"></script>

</body>
</html>