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
                'desc' => 'Respons cepat untuk pertanyaan, saran, atau kendalamu.',
                'url' => 'https://wa.me/6281385002300',
                'icon' => 'bi bi-whatsapp',
                'label' => '+62 813-8500-2300',
            ],
            [
                'title' => 'Hubungi kami via email',
                'desc' => 'Cocok untuk pertanyaan yang butuh penjelasan lebih detail.',
                'url' => 'mailto:info@dompetalquran.or.id',
                'icon' => 'bi bi-envelope-fill',
                'label' => 'info@dompetalquran.or.id',
            ],
        ];
    @endphp

    <main class="help-page">

        <!-- ========== HERO CHATBOT ========== -->
        <section class="help-hero">
            <div class="help-hero-decor" aria-hidden="true">
                <span class="help-blob help-blob-1"></span>
                <span class="help-blob help-blob-2"></span>
                <span class="help-blob help-blob-3"></span>
                <i class="bi bi-chat-heart-fill help-float-icon help-float-1"></i>
                <i class="bi bi-receipt help-float-icon help-float-2"></i>
                <i class="bi bi-hand-thumbs-up-fill help-float-icon help-float-3"></i>
            </div>

            <div class="container">
                <div class="help-hero-content">
                    <div class="help-pill">
                        <i class="bi bi-stars" aria-hidden="true"></i>
                        <span>OrangBaik.id Official Chatbot</span>
                    </div>

                    <h1>
                        <span class="help-bot-icon" aria-hidden="true">
                            <i class="bi bi-robot"></i>
                            <span class="help-bot-pulse"></span>
                        </span>
                        Chatbot Cerdas untuk
                        <br>
                        Pertanyaan Seputar <strong>OrangBaik.id</strong>
                    </h1>

                    <p>
                        Chatbot interaktif yang siap membantu menjawab pertanyaan seputar donasi,
                        penggalangan dana, transaksi, dan layanan OrangBaik.id lainnya — kapan saja kamu butuhkan.
                    </p>

                    <div class="help-trust-row">
                        <span class="help-trust-item"><i class="bi bi-lightning-charge-fill"></i> Respon instan</span>
                        <span class="help-trust-item"><i class="bi bi-clock-history"></i> Aktif 24/7</span>
                        <span class="help-trust-item"><i class="bi bi-shield-check"></i> Jawaban terverifikasi</span>
                    </div>

                    <!-- ===== CHATBOX (id & struktur tetep, JS gak berubah) ===== -->
                    <div class="chatbox" id="help-ai-form">
                        <textarea id="help-ai-input" class="chatbox-input"
                            placeholder="Tanyakan sesuatu tentang OrangBaik.id..." aria-label="Tulis pertanyaan"
                            required></textarea>

                        <div class="chatbox-bottom">
                            <div class="chatbox-tools">
                                <button type="button" id="voice-btn" aria-label="Voice input" title="Suara">
                                    <i class="bi bi-mic-fill"></i>
                                </button>
                            </div>

                            <button class="chatbox-send" id="help-ai-send" aria-label="Kirim pertanyaan">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="chat-preview" id="help-ai-chat">
                        <div class="chat-message bot" id="welcome-message">
                            <strong>OrangBaik.id Assistant</strong>
                            <p>
                                Halo, saya siap membantu menjawab pertanyaan seputar donasi,
                                penggalang dana, transaksi, e-kwitansi, dan layanan OrangBaik.id.
                            </p>
                        </div>
                    </div>

                    <div class="help-quick-topics">
                        <span>Topik populer:</span>
                        <button type="button" class="help-quick-chip">Cara berdonasi</button>
                        <button type="button" class="help-quick-chip">Buat penggalangan dana</button>
                        <button type="button" class="help-quick-chip">E-kwitansi</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== CONTACT SECTION ========== -->
        <section class="help-contact-section">
            <div class="container">
                <div class="help-section-heading">
                    <span class="help-section-label">Butuh Bantuan Langsung?</span>
                    <h2>Hubungi Tim Kami</h2>
                    <p>Tidak menemukan jawaban dari chatbot? Tim kami siap bantu lewat kanal berikut.</p>
                </div>

                <div class="help-contact-grid">
                    @foreach ($contacts as $contact)
                        <a href="{{ $contact['url'] }}" class="contact-card"
                            target="{{ str_starts_with($contact['url'], 'https://wa.me') ? '_blank' : '' }}">
                            <span class="contact-icon" aria-hidden="true">
                                <i class="{{ $contact['icon'] }}"></i>
                            </span>

                            <span class="contact-text">
                                <strong>{{ $contact['title'] }}</strong>
                                <small class="contact-label">{{ $contact['label'] }}</small>
                                <small class="contact-desc">{{ $contact['desc'] }}</small>
                            </span>

                            <span class="contact-arrow" aria-hidden="true">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        </a>
                    @endforeach

                    <div class="contact-hours-card">
                        <span class="contact-icon" aria-hidden="true">
                            <i class="bi bi-clock-fill"></i>
                        </span>
                        <span class="contact-text">
                            <strong>Jam Layanan Tim</strong>
                            <small class="contact-desc">Senin – Jumat, 08.00 – 16.00 WIB</small>
                            <small class="contact-desc">Sabtu, 08.00 – 12.00 WIB</small>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== FAQ SECTION ========== -->
        <section class="help-faq-section">
            <div class="container help-faq-layout">

                <div class="help-faq-intro">
                    <span class="help-section-label">FAQ</span>
                    <h2>Pertanyaan yang Sering Diajukan</h2>
                    <p>Beberapa pertanyaan umum seputar OrangBaik.id, donasi, penggalangan dana, transaksi, dan laporan
                        program.</p>
                    <div class="tc-faq-help">
                        <p>Masih ada pertanyaan lain?</p>
                        <a href="https://wa.me/6281385002300" class="tc-faq-help-link">
                            Hubungi tim kami <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>

                <div class="help-faq-list">
                    @forelse ($faqs as $faq)
                        <details class="help-faq-item">
                            <summary>
                                <span>{{ $faq->pertanyaan }}</span>
                                <i class="bi bi-plus-lg help-faq-icon" aria-hidden="true"></i>
                            </summary>
                            <div class="help-faq-answer">
                                <p>{{ $faq->jawaban }}</p>
                            </div>
                        </details>
                    @empty
                        <p class="help-faq-empty">Belum ada pertanyaan yang ditampilkan.</p>
                    @endforelse
                </div>

            </div>
        </section>

    </main>

    @include('components.footer')

    <!-- JS chatbot TIDAK DIUBAH — copy persis dari sebelumnya -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('help-ai-input');
            const sendBtn = document.getElementById('help-ai-send');
            const chatContainer = document.getElementById('help-ai-chat');
            const voiceBtn = document.getElementById('voice-btn');
            let isFirstMessage = true;

            async function sendMessage() {
                const message = input.value.trim();
                if (!message) return;

                const welcome = document.getElementById('welcome-message');
                if (welcome && isFirstMessage) {
                    welcome.style.display = 'none';
                    isFirstMessage = false;
                }

                const userMsg = document.createElement('div');
                userMsg.className = 'chat-message user';
                userMsg.innerHTML = `
                    <strong>Anda</strong>
                    <p>${escapeHtml(message)}</p>
                `;
                chatContainer.appendChild(userMsg);

                input.value = '';
                input.style.height = 'auto';

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
                    const response = await fetch('{{ route("chatbot.message") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message: message })
                    });

                    const data = await response.json();

                    const loading = document.getElementById('loading-indicator');
                    if (loading) loading.remove();

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

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            sendBtn.addEventListener('click', sendMessage);
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendMessage();
                }
            });

            input.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 60) + 'px';
            });

            // Chip topik cepat -> isi textarea & fokus
            document.querySelectorAll('.help-quick-chip').forEach(function (chip) {
                chip.addEventListener('click', function () {
                    input.value = chip.textContent.trim();
                    input.focus();
                    input.dispatchEvent(new Event('input'));
                });
            });

            if (voiceBtn && 'webkitSpeechRecognition' in window) {
                const recognition = new webkitSpeechRecognition();
                recognition.lang = 'id-ID';
                recognition.continuous = false;
                recognition.interimResults = false;

                voiceBtn.addEventListener('click', function () {
                    recognition.start();
                    voiceBtn.style.color = '#e74c3c';
                    voiceBtn.style.transform = 'scale(1.1)';
                });

                recognition.onresult = function (event) {
                    const transcript = event.results[0][0].transcript;
                    input.value = transcript;
                    voiceBtn.style.color = '';
                    voiceBtn.style.transform = 'scale(1)';
                    setTimeout(sendMessage, 300);
                };

                recognition.onerror = function () {
                    voiceBtn.style.color = '';
                    voiceBtn.style.transform = 'scale(1)';
                };

                recognition.onend = function () {
                    voiceBtn.style.color = '';
                    voiceBtn.style.transform = 'scale(1)';
                };
            } else {
                if (voiceBtn) voiceBtn.style.display = 'none';
            }
        });
    </script>

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/pusat-bantuan.js') }}"></script>

</body>

</html>