<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Bantuan - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pusat-bantuan.css') }}">
</head>
<body>

@php
    $faqs = [
        'Apakah orangbaik.id memiliki izin legalitas dan diawasi oleh Pemerintah?',
        'Bagaimana orangbaik.id memastikan keaslian galang dana?',
        'Apakah ada potongan untuk biaya operasional orangbaik.id?',
        'Bagaimana cara mendapatkan laporan perkembangan program yang saya dukung?',
        'Bagaimana Cara Mendaftar menjadi Penggalang Dana?',
        'Apakah orangbaik.id memiliki izin legalitas dan diawasi oleh Pemerintah?',
        'Bagaimana orangbaik.id memastikan keaslian galang dana?',
    ];
@endphp

<main class="help-page">

    <section class="help-top">
        <div class="help-container">

            <button class="help-back" type="button" onclick="history.back()">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M15 18L9 12L15 6" />
                </svg>
                <span>Kembali</span>
            </button>

            <div class="chatbot-hero">
                <div class="chatbot-pill">
                    <span>✦</span>
                    orangbaik.id Official Chatbot
                </div>

                <h1>
                    <span class="bot-icon">🤖</span>
                    Chatbot Cerdas untuk <br>
                    Pertanyaan Seputar <strong>orangbaik.id</strong>
                </h1>

                <p>
                    Chatbot interaktif yang siap membantu menjawab pertanyaan seputar orangbaik.id
                    dengan cepat, mudah, dan akurat kapan saja Anda membutuhkannya.
                </p>

                <form class="chatbox" action="#" method="POST">
                    @csrf

                    <textarea name="message" placeholder="What would you like to know?"></textarea>

                    <div class="chatbox-bottom">
                        <div class="chatbox-actions">
                            <button type="button" aria-label="Upload gambar">▧</button>
                            <button type="button" aria-label="Kode">‹›</button>
                            <button type="button" aria-label="Voice">♟</button>
                        </div>

                        <button class="send-button" type="submit" aria-label="Kirim">
                            ↑
                        </button>
                    </div>
                </form>
            </div>

            <div class="help-contact-wrap">
                <div class="help-contact-list">

                    <a href="#" class="contact-card">
                        <span class="contact-icon">☎</span>

                        <span class="contact-text">
                            <strong>Hubungi Hotline orangbaik.id</strong>
                            <small>Hubungi Hotline orangbaik.id untuk menjawab pertanyaan, saran, atau kendalamu.</small>
                        </span>

                        <span class="contact-arrow">›</span>
                    </a>

                    <a href="mailto:info@dompetalquran.or.id" class="contact-card">
                        <span class="contact-icon">✉</span>

                        <span class="contact-text">
                            <strong>Hubungi kami via email</strong>
                            <small>Hubungi untuk menjawab pertanyaan, saran, atau kendalamu via email kami.</small>
                        </span>

                        <span class="contact-arrow">›</span>
                    </a>

                </div>

                <div class="map-card">
                    MAPS
                </div>
            </div>

        </div>
    </section>

    <section class="help-faq">
        <div class="help-container">

            <h2>Pertanyaan Yang Sering Diajukan Tentang Kitabisa</h2>

            <div class="faq-list">
                @foreach ($faqs as $faq)
                    <details class="faq-item">
                        <summary>
                            <span>{{ $faq }}</span>
                            <b>+</b>
                        </summary>

                        <p>
                            OrangBaik.id menjaga transparansi, keamanan, serta kepercayaan pengguna
                            melalui proses verifikasi dan pengawasan campaign.
                        </p>
                    </details>
                @endforeach
            </div>

        </div>
    </section>

</main>

@include('components.footer')

</body>
</html>