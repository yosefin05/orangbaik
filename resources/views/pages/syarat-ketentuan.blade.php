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
    <section class="tc-header">
        <div class="container tc-header-inner">
            <h1>Syarat &amp; Ketentuan</h1>
            <p>
                Ketentuan berikut mengatur penggunaan Anda atas platform OrangBaik.id.
                Dengan menggunakan layanan kami, Anda dianggap telah membaca dan
                menyetujui seluruh poin di bawah ini.
            </p>
        </div>
    </section>

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
        <div class="container">

            <div class="tc-faq-header">
                <h2>Pertanyaan yang Sering Diajukan</h2>
                <p>Beberapa informasi umum terkait penggunaan layanan OrangBaik.id.</p>
            </div>

            <div class="tc-faq-list">
                @forelse ($faqs as $faq)
                    <details class="tc-faq-item">
                        <summary>
                            <span>{{ $faq->pertanyaan }}</span>
                            <i class="bi bi-plus-lg"></i>
                        </summary>
                        <p>{{ $faq->jawaban }}</p>
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