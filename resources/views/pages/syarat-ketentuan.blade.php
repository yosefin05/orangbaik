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

    <main class="tc-page">

        {{-- HEADER --}}
        <div class="tc-header">
            <div class="container">
                <div class="tc-header-banner">
                    <div class="tc-header-banner-bg"></div>
                    <i class='bx bxs-star tc-star tc-star-1'></i>
                    <i class='bx bxs-star tc-star tc-star-2'></i>
                    <i class='bx bxs-star tc-star tc-star-3'></i>
                    <div class="tc-header-content">
                        <h1>Pahami Syarat dan Ketentuan Kami</h1>
                        <p>
                            Pelajari ketentuan layanan yang berlaku agar Anda dapat
                            menggunakan platform dengan aman, nyaman, dan sesuai aturan.
                        </p>
                    </div>
                    <div class="tc-header-illustration">
                        <img src="{{ asset('assets/syarat.png') }}" alt="Ilustrasi customer service OrangBaik.id">
                    </div>
                </div>
            </div>
        </div>

        {{-- CONTENT --}}
        <section class="tc-content-section">
            <div class="container tc-layout">

                <aside class="tc-toc">
                    <span class="tc-toc-label">Daftar Isi</span>

                    <nav>
                        @forelse ($terms as $index => $term)
                            <a href="#term-{{ $index + 1 }}">
                                <em>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</em>
                                {{ $term->judul }}
                            </a>
                        @empty
                            <p class="tc-toc-empty">Konten sedang diperbarui.</p>
                        @endforelse
                    </nav>
                </aside>

                <article class="tc-document">
                    @forelse ($terms as $index => $term)
                        <section class="tc-clause" id="term-{{ $index + 1 }}">
                            <span class="tc-clause-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>

                            <div class="tc-clause-body">
                                <h2>{{ $term->judul }}</h2>

                                @foreach ($term->paragraphs() as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <section class="tc-clause">
                            <div class="tc-clause-body">
                                <p>Konten syarat dan ketentuan sedang diperbarui.</p>
                            </div>
                        </section>
                    @endforelse
                </article>

            </div>
        </section>

        {{-- FAQ --}}
        <section class="tc-faq">
            <div class="container tc-faq-layout">

                <div class="tc-faq-intro">
                    <span class="tc-faq-eyebrow">FAQ</span>
                    <h2>Pertanyaan yang Sering Diajukan</h2>
                    <p>Beberapa informasi umum terkait penggunaan layanan OrangBaik.id.</p>

                    <div class="tc-faq-help">
                        <p>Masih ada pertanyaan lain?</p>
                        <a href="https://wa.me/6281385002300" class="tc-faq-help-link">
                            Hubungi tim kami <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                <div class="tc-faq-list">
                    @forelse ($faqs as $faq)
                        <details class="tc-faq-item">
                            <summary>
                                <span>{{ $faq->pertanyaan }}</span>
                                <i class="bi bi-plus-lg tc-faq-icon"></i>
                            </summary>
                            <div class="tc-faq-answer">
                                <p>{{ $faq->jawaban }}</p>
                            </div>
                        </details>
                    @empty
                        <p class="tc-faq-empty">Belum ada pertanyaan yang ditampilkan.</p>
                    @endforelse
                </div>

            </div>
        </section>

    </main>

    @include('components.footer')

    <script src="{{ asset('js/header.js') }}"></script>

</body>

</html>