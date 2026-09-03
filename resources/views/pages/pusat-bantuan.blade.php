<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Bantuan - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pusat-bantuan.css') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    @include('components.header')

    @php
        $contacts = [
            [
                'title' => 'Hubungi Hotline OrangBaik.id',
                'desc' => 'Hubungi Hotline OrangBaik.id untuk menjawab pertanyaan, saran, atau kendalamu.',
                'url' => 'https://wa.me/6281385002300',
                'icon' => 'bi bi-telephone-fill',
            ],
            [
                'title' => 'Hubungi kami via email',
                'desc' => 'Hubungi untuk menjawab pertanyaan, saran, atau kendalamu via email kami.',
                'url' => 'mailto:info@dompetalquran.or.id',
                'icon' => 'bi bi-envelope-fill',
            ],
        ];
    @endphp

    <main class="help-page">

        <section class="help-hero">
            <div class="container">

                <div class="help-hero-content">
                    <div class="help-pill">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        <span>OrangBaik.id Official Chatbot</span>
                    </div>

                    <h1>
                        <span class="help-bot-icon" aria-hidden="true">
                            <i class="bi bi-robot"></i>
                        </span>

                        Chatbot Cerdas untuk
                        <br>

                        Pertanyaan Seputar <strong>OrangBaik.id</strong>
                    </h1>

                    <p>
                        Chatbot interaktif yang siap membantu menjawab pertanyaan seputar OrangBaik.id
                        dengan cepat, mudah, dan akurat kapan saja kamu membutuhkannya.
                    </p>

                    <form class="chatbox" id="help-ai-form" action="#" method="POST">
                        @csrf

                        <textarea name="message" class="chatbox-input" placeholder="What would you like to know?"
                            aria-label="Tulis pertanyaan" required></textarea>

                        <div class="chatbox-bottom">
                            <div class="chatbox-tools">
                                <button type="button" aria-label="Upload gambar">
                                    <i class="bi bi-image"></i>
                                </button>

                                <button type="button" aria-label="Kode">
                                    <i class="bi bi-code-slash"></i>
                                </button>

                                <button type="button" aria-label="Voice">
                                    <i class="bi bi-mic-fill"></i>
                                </button>
                            </div>

                            <button class="chatbox-send" type="submit" aria-label="Kirim pertanyaan">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </form>

                    <div class="chat-preview" id="help-ai-chat">
                        <div class="chat-message bot">
                            <strong>OrangBaik.id Assistant</strong>
                            <p>
                                Halo, saya siap membantu menjawab pertanyaan seputar donasi,
                                penggalang dana, transaksi, e-kwitansi, dan layanan OrangBaik.id.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <section class="help-contact-section">
            <div class="container">

                <div class="help-contact-layout">
                    <!-- Contact Cards -->
                    <div class="help-contact-list">
                        @foreach ($contacts as $contact)
                            <a href="{{ $contact['url'] }}" class="contact-card">
                                <span class="contact-icon" aria-hidden="true">
                                    <i class="{{ $contact['icon'] }}"></i>
                                </span>

                                <span class="contact-text">
                                    <strong>{{ $contact['title'] }}</strong>
                                    <small>{{ $contact['desc'] }}</small>
                                </span>

                                <span class="contact-arrow" aria-hidden="true">
                                    <i class="bi bi-chevron-right"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <!-- Map Card -->
                    <div class="map-card">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.849706646301!2d112.7224737!3d-7.4655644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7e3d98f3cd39b%3A0xf9ba86c029a86e32!2sLembaga%20Amil%20Zakat%20Dompet%20Alquran%20Indonesia!5e0!3m2!1sid!2sid!4v1710000000000"
                            width="100%" height="260" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Lembaga Amil Zakat Dompet Alquran Indonesia - Ruko Citra City Blok R28, Sidoarjo">
                        </iframe>

                        <div class="map-address">
                            <span class="map-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </span>
                            <div class="map-text">
                                <span class="map-title">Peta Dompet Al Quran Indonesia</span>
                                <span class="map-subtitle">Lembaga Amil Zakat Dompet Alquran Indonesia</span>
                                <span class="map-location">Ruko Citra City Blok R28, Sari Rogo, Sidoarjo, Sidoarjo
                                    Regency, East Java 61234</span>
                            </div>
                            <a href="https://www.google.com/maps/dir//Lembaga+Amil+Zakat+Dompet+Alquran+Indonesia+Ruko+Citra+City+Blok+R28+Sari+Rogo+Sidoarjo"
                                target="_blank" class="map-open-btn">
                                <i class="bi bi-box-arrow-up-right"></i>
                                Open in Maps
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <section class="about-section about-faq-section">
            <div class="container">

                <div class="about-section-heading">
                    <span class="about-section-label">FAQ</span>

                    <h2>Pertanyaan yang Sering Diajukan Tentang OrangBaik.id</h2>

                    <p>
                        Beberapa pertanyaan umum seputar OrangBaik.id, donasi,
                        penggalang dana, transaksi, dan laporan program.
                    </p>
                </div>

                <div class="about-faq-list">
                    @forelse ($faqs as $faq)
                        <details class="about-faq-item">
                            <summary>
                                <span>{{ $faq->pertanyaan }}</span>
                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
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
    <script src="{{ asset('js/pusat-bantuan.js') }}"></script>

</body>

</html>