<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Bantuan - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pusat-bantuan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/maps.css') }}">

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
                'icon' => 'bi bi-whatsapp',
                'label' => '+62 813-8500-2300',
            ],
            [
                'title' => 'Hubungi kami via email',
                'desc' => 'Hubungi untuk menjawab pertanyaan, saran, atau kendalamu via email kami.',
                'url' => 'mailto:info@dompetalquran.or.id',
                'icon' => 'bi bi-envelope-fill',
                'label' => 'info@dompetalquran.or.id',
            ],
        ];
    @endphp

    <main class="help-page">

        <!-- ========== HERO CHATBOT ========== -->
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

                    <!-- ===== CHATBOX SEDERHANA ===== -->
                    <div class="chatbox" id="help-ai-form">
                        <textarea id="help-ai-input" class="chatbox-input" 
                                  placeholder="Tanyakan sesuatu tentang OrangBaik.id..."
                                  aria-label="Tulis pertanyaan" required></textarea>

                        <div class="chatbox-bottom">
                            <!-- Tools: Voice Input (opsional) -->
                            <div class="chatbox-tools">
                                <button type="button" id="voice-btn" aria-label="Voice input" title="Suara">
                                    <i class="bi bi-mic-fill"></i>
                                </button>
                            </div>

                            <!-- Tombol Kirim -->
                            <button class="chatbox-send" id="help-ai-send" aria-label="Kirim pertanyaan">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ===== CHAT PREVIEW ===== -->
                    <div class="chat-preview" id="help-ai-chat">
                        <div class="chat-message bot" id="welcome-message">
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

        <!-- ========== CONTACT + MAP SECTION ========== -->
        <section class="help-contact-section">
            <div class="container">

                <div class="help-contact-layout">
                    <!-- Contact Cards -->
                    <div class="help-contact-list">
                        @foreach ($contacts as $contact)
                            <a href="{{ $contact['url'] }}" class="contact-card" 
                               target="{{ str_starts_with($contact['url'], 'https://wa.me') ? '_blank' : '' }}">
                                <span class="contact-icon" aria-hidden="true">
                                    <i class="{{ $contact['icon'] }}"></i>
                                </span>

                                <span class="contact-text">
                                    <strong>{{ $contact['title'] }}</strong>
                                    <small>
                                        <span class="contact-label">{{ $contact['label'] }}</span>
                                        <span class="contact-desc">{{ $contact['desc'] }}</span>
                                    </small>
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

        <!-- ========== FAQ SECTION ========== -->
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

    <!-- ============================================ -->
    <!-- SCRIPT CHATBOT - PAKAI API PACKAGE           -->
    <!-- ============================================ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('help-ai-input');
            const sendBtn = document.getElementById('help-ai-send');
            const chatContainer = document.getElementById('help-ai-chat');
            const voiceBtn = document.getElementById('voice-btn');
            let isFirstMessage = true;

            // ===== KIRIM PESAN =====
            async function sendMessage() {
                const message = input.value.trim();
                if (!message) return;

                // Sembunyikan welcome message
                const welcome = document.getElementById('welcome-message');
                if (welcome && isFirstMessage) {
                    welcome.style.display = 'none';
                    isFirstMessage = false;
                }

                // Tampilkan pesan user
                const userMsg = document.createElement('div');
                userMsg.className = 'chat-message user';
                userMsg.innerHTML = `
                    <strong>Anda</strong>
                    <p>${escapeHtml(message)}</p>
                `;
                chatContainer.appendChild(userMsg);

                input.value = '';
                input.style.height = 'auto';

                // Loading indicator
                const loadingMsg = document.createElement('div');
                loadingMsg.className = 'chat-message bot loading';
                loadingMsg.id = 'loading-indicator';
                loadingMsg.innerHTML = `
                    <strong>OrangBaik.id Assistant</strong>
                    <p>Mengetik...</p>
                `;
                chatContainer.appendChild(loadingMsg);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                try {
                    // Panggil API package chatbot
                    const response = await fetch('{{ route("chatbot.message") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: message })
                    });

                    const data = await response.json();

                    // Hapus loading
                    const loading = document.getElementById('loading-indicator');
                    if (loading) loading.remove();

                    // Tampilkan balasan bot
                    const reply = data.reply || data.message || 'Maaf, saya belum bisa menjawab pertanyaan itu.';
                    const botMsg = document.createElement('div');
                    botMsg.className = 'chat-message bot';
                    botMsg.innerHTML = `
                        <strong>OrangBaik.id Assistant</strong>
                        <p>${escapeHtml(reply)}</p>
                    `;
                    chatContainer.appendChild(botMsg);
                    chatContainer.scrollTop = chatContainer.scrollHeight;

                } catch (error) {
                    console.error('Error:', error);
                    const loading = document.getElementById('loading-indicator');
                    if (loading) loading.remove();

                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'chat-message bot';
                    errorMsg.innerHTML = `
                        <strong>OrangBaik.id Assistant</strong>
                        <p>⚠️ Maaf, terjadi kesalahan. Silakan coba lagi.</p>
                    `;
                    chatContainer.appendChild(errorMsg);
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            }

            // ===== ESCAPE HTML =====
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // ===== EVENT LISTENERS =====
            sendBtn.addEventListener('click', sendMessage);
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Auto-resize textarea
            input.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 60) + 'px';
            });

            // ===== VOICE INPUT (OPSIONAL) =====
            if (voiceBtn && 'webkitSpeechRecognition' in window) {
                const recognition = new webkitSpeechRecognition();
                recognition.lang = 'id-ID';
                recognition.continuous = false;
                recognition.interimResults = false;

                voiceBtn.addEventListener('click', function() {
                    recognition.start();
                    voiceBtn.style.color = '#e74c3c';
                    voiceBtn.style.transform = 'scale(1.1)';
                });

                recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript;
                    input.value = transcript;
                    voiceBtn.style.color = '';
                    voiceBtn.style.transform = 'scale(1)';
                    // Otomatis kirim setelah voice selesai
                    setTimeout(sendMessage, 300);
                };

                recognition.onerror = function() {
                    voiceBtn.style.color = '';
                    voiceBtn.style.transform = 'scale(1)';
                };

                recognition.onend = function() {
                    voiceBtn.style.color = '';
                    voiceBtn.style.transform = 'scale(1)';
                };
            } else {
                // Sembunyikan tombol voice kalau browser gak support
                if (voiceBtn) voiceBtn.style.display = 'none';
            }
        });
    </script>

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/pusat-bantuan.js') }}"></script>

</body>

</html>