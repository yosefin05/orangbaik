<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nominal Donasi - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-bayar.css') }}">
</head>
<body>

<main class="payment-page">
    <div class="payment-container">

        <button class="back-button" type="button" onclick="history.back()">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 18L9 12L15 6" />
            </svg>
            <span>Kembali</span>
        </button>

        {{-- FORM --}}
        <form class="payment-layout" id="donasiForm">
            @csrf

            {{-- LEFT --}}
            <section class="payment-left">

                <article class="campaign-mini-card">
                    <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}">
                    <div class="campaign-mini-body">
                        <h1>{{ $campaign->judul }}</h1>
                        <p>
                            {{ $campaign->penggalangDana->nama ?? 'Penggalang' }}
                            <span>●</span>
                        </p>

                        <div class="mini-amount">
                            <strong>Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</strong>
                            <span>Terkumpul</span>
                        </div>

                        <div class="mini-progress">
                            <div style="width: {{ $campaign->target > 0 ? min(($totalTerkumpul / $campaign->target) * 100, 100) : 0 }}%;"></div>
                        </div>

                        <div class="mini-meta">
                            <span>{{ $jumlahDonatur }} donatur</span>
                            <span>{{ $campaign->target ? 'Rp ' . number_format($campaign->target, 0, ',', '.') : '∞' }}</span>
                        </div>
                    </div>
                </article>

                <section class="nominal-section">
                    <h2>Masukkan Nominal Donasi</h2>

                    <div class="nominal-list">
                        @forelse ($campaign->packages as $index => $package)
                            <label class="nominal-card">
                                <input
                                    type="radio"
                                    name="nominal"
                                    value="{{ $package->nominal }}"
                                    {{ $index === 0 ? 'checked' : '' }}
                                >
                                <span class="nominal-emoji">{{ $package->emoji ?? '💰' }}</span>
                                <strong>Rp {{ number_format($package->nominal, 0, ',', '.') }}</strong>
                            </label>
                        @empty
                            <!-- Fallback DEFAULT jika tidak ada package -->
                            <label class="nominal-card">
                                <input type="radio" name="nominal" value="10000" checked>
                                <span class="nominal-emoji">💰</span>
                                <strong>Rp10.000</strong>
                            </label>
                            <label class="nominal-card">
                                <input type="radio" name="nominal" value="25000">
                                <span class="nominal-emoji">💎</span>
                                <strong>Rp25.000</strong>
                            </label>
                            <label class="nominal-card">
                                <input type="radio" name="nominal" value="50000">
                                <span class="nominal-emoji">🎁</span>
                                <strong>Rp50.000</strong>
                            </label>
                            <label class="nominal-card">
                                <input type="radio" name="nominal" value="100000">
                                <span class="nominal-emoji">🌟</span>
                                <strong>Rp100.000</strong>
                            </label>
                        @endforelse
                    </div>

                    <div class="custom-nominal-card">
                        <h3>Masukkan Donasi Lainnya</h3>

                        <div class="custom-input-wrap">
                            <span>Rp</span>
                            <input
                                type="number"
                                name="nominal_lainnya"
                                id="nominal_lainnya"
                                placeholder="0"
                                min="{{ $campaign->minimal_donasi ?? 5000 }}"
                                value="{{ old('nominal_lainnya') }}"
                            >
                        </div>

                        <p>Min. Donasi sebesar Rp {{ number_format($campaign->minimal_donasi ?? 5000, 0, ',', '.') }}</p>
                        <div id="error-nominal" class="error-text" style="display:none;"></div>
                    </div>
                </section>

                <section class="donor-card">
                    <p class="donor-title">
                        @if(auth()->check())
                            <span>Donasi sebagai <strong id="donorNameDisplay">{{ auth()->user()->name }}</strong></span>
                        @else
                            <a href="{{ route('login') }}">Masuk</a> atau lengkapi data di bawah ini
                        @endif
                    </p>

                    <div class="donor-input-group">
                        <div>
                            <input
                                type="text"
                                name="nama_donatur"
                                id="nama_donatur"
                                placeholder="Masukkan Nama Lengkap"
                                value="{{ old('nama_donatur', auth()->check() ? auth()->user()->name : '') }}"
                                {{ auth()->check() ? 'readonly' : '' }}
                            >
                            <div id="error-nama_donatur" class="error-text" style="display:none;"></div>
                        </div>

                        <div>
                            <input
                                type="text"
                                name="no_hp"
                                id="no_hp"
                                placeholder="Masukkan Nomor Ponsel"
                                value="{{ old('no_hp', auth()->check() ? auth()->user()->nomor : '') }}"
                            >
                            <div id="error-no_hp" class="error-text" style="display:none;"></div>
                        </div>
                    </div>

                    <p class="input-note">
                        <span>ⓘ</span>
                        Pastikan email atau nomor ponselmu sudah benar untuk menerima laporan donasi.
                    </p>

                    <label class="switch-row">
                        <span>Sembunyikan nama saya (donasi sebagai orangbaik)</span>
                        <input type="checkbox" name="anonymous_donor" id="anonymous_donor" {{ old('anonymous_donor') ? 'checked' : '' }}>
                        <i></i>
                    </label>
                </section>

                <section class="message-card">
                    <h2>Sampaikan doa serta pesan dukungan (opsional)</h2>

                    <div class="textarea-wrap">
                        <textarea
                            name="pesan"
                            id="pesan"
                            maxlength="255"
                            placeholder="Tuliskan doa dan harapan Anda untuk penggalang dana atau diri sendiri. Hindari penggunaan emoji agar pesan tetap nyaman dibaca."
                        >{{ old('pesan') }}</textarea>
                        <span id="charCount">0/255</span>
                    </div>
                    <div id="error-pesan" class="error-text" style="display:none;"></div>

                    <label class="switch-row">
                        <span>Sembunyikan nama saya (donasi sebagai orangbaik)</span>
                        <input type="checkbox" name="anonymous_message" id="anonymous_message" {{ old('anonymous_message') ? 'checked' : '' }}>
                        <i></i>
                    </label>
                </section>

            </section>

            {{-- RIGHT --}}
            <aside class="payment-right">
                <div class="payment-method-card">
                    <h2>Ringkasan Donasi</h2>

                    <div class="payment-total">
                        <span>Total Donasi</span>
                        <strong id="total-donasi">Rp0</strong>
                    </div>

                    <div class="payment-method-info">
                        <strong>💳 Metode Pembayaran</strong>
                        <p>Pilih metode pembayaran setelah klik "Bayar Sekarang".</p>
                        <small>Tersedia: Kartu Kredit, Virtual Account, E-Wallet, QRIS, dan lainnya.</small>
                    </div>

                    <button class="pay-button" type="button" id="payButton">
                        🛡 Bayar Sekarang
                    </button>
                    <div id="loading-text" style="display:none;">
                        ⏳ Memproses...
                    </div>
                </div>
            </aside>

        </form>
    </div>
</main>

<!-- Midtrans Snap -->
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.clientKey') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============================================================
        // 1. AMBIL MINIMAL DONASI DARI DATABASE
        // ============================================================
        const minimalDonasi = {{ $campaign->minimal_donasi ?? 5000 }};

        // ============================================================
        // 2. UPDATE TOTAL DONASI & CHAR COUNT
        // ============================================================
        const totalEl = document.getElementById('total-donasi');
        const nominalRadios = document.querySelectorAll('input[name="nominal"]');
        const nominalLainnya = document.querySelector('input[name="nominal_lainnya"]');
        const charCounter = document.getElementById('charCount');
        const textarea = document.querySelector('textarea[name="pesan"]');

        function updateTotal() {
            let nominal = 0;
            const selectedRadio = document.querySelector('input[name="nominal"]:checked');
            if (selectedRadio) {
                nominal = parseInt(selectedRadio.value) || 0;
            }
            const customValue = parseInt(nominalLainnya.value);
            if (customValue && customValue > 0) {
                nominal = customValue;
            }
            totalEl.textContent = 'Rp' + nominal.toLocaleString('id-ID');
        }

        function updateCharCount() {
            const count = textarea.value.length;
            charCounter.textContent = count + '/255';
            charCounter.style.color = count > 250 ? '#e74c3c' : '';
        }

        nominalRadios.forEach(radio => radio.addEventListener('change', updateTotal));
        nominalLainnya.addEventListener('input', updateTotal);
        textarea.addEventListener('input', updateCharCount);
        updateTotal();
        updateCharCount();

        // ============================================================
        // 3. ANONIM: UBAH NAMA JADI "Orang Baik" (REAL-TIME)
        // ============================================================
        const anonymousDonor = document.getElementById('anonymous_donor');
        const anonymousMessage = document.getElementById('anonymous_message');
        const namaDonaturInput = document.getElementById('nama_donatur');
        const donorNameDisplay = document.getElementById('donorNameDisplay');
        const namaAsli = '{{ auth()->check() ? auth()->user()->name : '' }}';

        function updateAnonim() {
            const isAnonim = anonymousDonor.checked || anonymousMessage.checked;

            if (isAnonim) {
                // Jika anonim, ubah nama jadi "Orang Baik"
                if (namaDonaturInput) {
                    namaDonaturInput.value = 'Orang Baik';
                }
                if (donorNameDisplay) {
                    donorNameDisplay.textContent = 'Orang Baik';
                }
            } else {
                // Jika tidak anonim, kembalikan ke nama asli
                if (namaDonaturInput && !namaDonaturInput.readOnly) {
                    namaDonaturInput.value = namaAsli;
                }
                if (donorNameDisplay) {
                    donorNameDisplay.textContent = namaAsli || 'Donatur';
                }
            }
        }

        // Event listener untuk kedua checkbox
        anonymousDonor.addEventListener('change', updateAnonim);
        anonymousMessage.addEventListener('change', updateAnonim);

        // Jalankan sekali saat halaman dimuat
        updateAnonim();

        // ============================================================
        // 4. CLEAR ERROR
        // ============================================================
        document.querySelectorAll('#nama_donatur, #no_hp, #pesan, #nominal_lainnya').forEach(el => {
            el.addEventListener('input', function() {
                const errorId = 'error-' + this.id;
                const errorEl = document.getElementById(errorId);
                if (errorEl) {
                    errorEl.style.display = 'none';
                    errorEl.textContent = '';
                }
            });
        });

        // ============================================================
        // 5. VALIDASI NOMINAL SEBELUM SUBMIT
        // ============================================================
        function validateNominal(nominal) {
            const errorEl = document.getElementById('error-nominal');
            if (nominal < minimalDonasi) {
                errorEl.textContent = 'Minimal donasi Rp ' + minimalDonasi.toLocaleString('id-ID');
                errorEl.style.display = 'block';
                return false;
            }
            errorEl.style.display = 'none';
            return true;
        }

        // ============================================================
        // 6. TOMBOL BAYAR (AJAX)
        // ============================================================
        const payButton = document.getElementById('payButton');
        const loadingText = document.getElementById('loading-text');

        payButton.addEventListener('click', async function() {
            // Clear semua error
            document.querySelectorAll('.error-text').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });

            // Ambil nilai nominal
            let nominal = 0;
            const selectedRadio = document.querySelector('input[name="nominal"]:checked');
            if (selectedRadio) {
                nominal = parseInt(selectedRadio.value) || 0;
            }
            const customValue = parseInt(nominalLainnya.value);
            if (customValue && customValue > 0) {
                nominal = customValue;
            }

            // Validasi minimal donasi
            if (!validateNominal(nominal)) {
                return;
            }

            const form = document.getElementById('donasiForm');
            const formData = new FormData(form);

            payButton.disabled = true;
            payButton.textContent = '⏳ Memproses...';
            loadingText.style.display = 'block';

            try {
                const response = await fetch('{{ route("donasi.store", $campaign->slug) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    if (result.errors) {
                        for (const [field, messages] of Object.entries(result.errors)) {
                            const errorEl = document.getElementById('error-' + field);
                            if (errorEl) {
                                errorEl.textContent = messages[0];
                                errorEl.style.display = 'block';
                            }
                        }
                    } else {
                        alert(result.message || 'Terjadi kesalahan. Silakan coba lagi.');
                    }
                    resetButton();
                    return;
                }

                if (result.snap_token) {
                    snap.pay(result.snap_token, {
                        onSuccess: function(res) {
                            window.location.href = '{{ route("donasi.status", "sukses") }}';
                        },
                        onPending: function(res) {
                            window.location.href = '{{ route("donasi.status", "sukses") }}';
                        },
                        onError: function(res) {
                            window.location.href = '{{ route("donasi.status", "gagal") }}';
                        },
                        onClose: function() {
                            resetButton();
                            alert('Anda menutup popup pembayaran.');
                        }
                    });
                } else {
                    alert('Gagal mendapatkan token pembayaran.');
                    resetButton();
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                resetButton();
            }
        });

        function resetButton() {
            payButton.disabled = false;
            payButton.textContent = '🛡 Bayar Sekarang';
            loadingText.style.display = 'none';
        }
    });
</script>

</body>
</html>