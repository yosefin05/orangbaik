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

<main class="berita-page">
    <section class="container berita-section">

        <div class="berita-grid">

            @forelse ($beritas as $berita)

                <article class="berita-card">

                    <a href="{{ route('berita.show', $berita->slug) }}">

                        <img
                            src="{{ asset('storage/' . $berita->thumbnail) }}"
                            alt="{{ $berita->judul }}"
                            class="berita-image"
                            loading="lazy">

                        <div class="berita-body">

                            <h2>
                                {{ $berita->judul }}
                            </h2>

                            <p>
                                {{ $berita->created_at->translatedFormat('d F Y') }}
                            </p>

                        </div>

                    </a>

                </article>

            @empty

                <p>Belum ada berita.</p>

            @endforelse

        </div>

        <div class="pagination-wrapper">
            @if (is_object($beritas) && method_exists($beritas, 'links'))
                {{ $beritas->links() }}
            @endif
        </div>

    </section>
</main>

@include('components.footer')

</body>
</html>