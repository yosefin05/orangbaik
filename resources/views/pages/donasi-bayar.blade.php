<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi - {{ $campaign->judul }} - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-bayar.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #3365af;
            --primary-dark: #244980;
            --primary-light: #eff6ff;
        }
        body {
            background-color: #f8fafc;
            color: #0f172a;
        }
        .payment-channels-section {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .group-heading {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0.75rem 0 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .channel-groups-wrapper {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .channel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 0.75rem;
        }
        .channel-item {
            position: relative;
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #ffffff;
        }
        .channel-item:hover {
            border-color: #93c5fd;
            background: #f8fafc;
        }
        .channel-item.selected,
        .channel-item input[type="radio"]:checked ~ .channel-item-content {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        .channel-item input[type="radio"] {
            margin-right: 0.75rem;
            accent-color: var(--primary-color);
            width: 18px;
            height: 18px;
        }
        .channel-item-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .channel-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .channel-name {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #0f172a;
        }
        .channel-type {
            font-size: 0.75rem;
            color: #64748b;
        }
        .channel-provider-tag {
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 9999px;
            background: #f1f5f9;
            color: #475569;
        }
        .channel-item input[type="radio"]:checked + .channel-item-content .channel-provider-tag {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .error-text {
            color: #dc2626;
            font-size: 0.8125rem;
            margin-top: 0.375rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

<main class="payment-page">
    <div class="payment-container">

        <button class="back-button" type="button" onclick="history.back()">
            <svg viewBox="0 0 24 24" aria-hidden="true" width="20" height="20">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
            <span>Kembali ke Detail Campaign</span>
        </button>

        {{-- FORM DONASI --}}
        <form class="payment-layout" id="donasiForm">
            @csrf

            {{-- LEFT COLUMN --}}
            <section class="payment-left">

                {{-- 1. CAMPAIGN SUMMARY CARD --}}
                <article class="campaign-mini-card">
                    <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}">
                    <div class="campaign-mini-body">
                        <h1>{{ $campaign->judul }}</h1>
                        <p>
                            <i class="bi bi-patch-check-fill text-primary"></i>
                            {{ $campaign->penggalangDana->nama_penggalang ?? 'Penggalang Dana' }}
                        </p>

                        <div class="mini-amount">
                            <strong>Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</strong>
                            <span>terkumpul dari Rp {{ $campaign->target_donasi ? number_format($campaign->target_donasi, 0, ',', '.') : '∞' }}</span>
                        </div>

                        <div class="mini-progress">
                            <div style="width: {{ $campaign->target_donasi > 0 ? min(($totalTerkumpul / $campaign->target_donasi) * 100, 100) : 0 }}%;"></div>
                        </div>

                        <div class="mini-meta">
                            <span><i class="bi bi-people"></i> {{ $jumlahDonatur }} Donatur</span>
                            <span><i class="bi bi-calendar-event"></i> {{ $campaign->tanggal_berakhir ? $campaign->tanggal_berakhir->format('d M Y') : 'Tanpa batas waktu' }}</span>
                        </div>
                    </div>
                </article>

                {{-- 2. NOMINAL DONATION SECTION --}}
                <section class="nominal-section">
                    <h2>Pilih Nominal Donasi</h2>

                    <div class="nominal-list">
                        @forelse ($campaign->packages as $index => $package)
                            <label class="nominal-card">
                                <input
                                    type="radio"
                                    name="nominal"
                                    value="{{ $package->nominal }}"
                                    {{ $index === 0 ? 'checked' : '' }}
                                >
                                <span class="nominal-emoji">{{ $package->emoji ?? '🎁' }}</span>
                                <strong>Rp {{ number_format($package->nominal, 0, ',', '.') }}</strong>
                            </label>
                        @empty
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
                            <div class="custom-nominal-card">
                        <h3>Atau Masukkan Nominal Lainnya</h3>

                        <div class="custom-input-wrap">
                            <span>Rp</span>
                            <input
                                type="number"
                                name="nominal_lainnya"
                                id="nominal_lainnya"
                                placeholder="0"
                                min="{{ $campaign->minimal_donasi ?? 1000 }}"
                                value="{{ old('nominal_lainnya') }}"
                            >
                        </div>

                        <p class="mt-2 text-muted" style="font-size:0.8125rem;">
                            Minimal donasi sebesar Rp {{ number_format($campaign->minimal_donasi ?? 1000, 0, ',', '.') }}
                        </p>
                        <div id="error-nominal" class="error-text" style="display:none;"></div>
                    </div>
                        @endforelse
                    </div>
                </section>

                {{-- 3. PAYMENT METHOD SECTION --}}
                <section class="payment-channels-section">
                    <h2>Pilih Metode Pembayaran</h2>
                    <div id="error-payment_channel_id" class="error-text" style="display:none; margin-bottom: 0.75rem;"></div>

                    @if(isset($paymentChannels) && $paymentChannels->isNotEmpty())
                        @php
                            $grouped = $paymentChannels->groupBy(function($item) {
                                return $item->gateway?->name ?? 'Metode Pembayaran Lainnya';
                            });
                        @endphp

                        <div class="channel-groups-wrapper">
                            @foreach ($grouped as $groupName => $groupChannels)
                                <div class="channel-group mb-3">
                                    <h3 class="group-heading">
                                        <i class="bi bi-credit-card-2-front text-primary"></i>
                                        <span>{{ $groupName }}</span>
                                    </h3>
                                    <div class="channel-grid">
                                        @foreach ($groupChannels as $channel)
                                            <label class="channel-item">
                                                <input type="radio" name="payment_channel_id" value="{{ $channel->id }}" {{ $loop->parent->first && $loop->first ? 'checked' : '' }}>
                                                <div class="channel-item-content">
                                                    <div class="channel-info">
                                                        <strong class="channel-name">{{ $channel->name }}</strong>
                                                        <span class="channel-type">{{ $channel->payment_type_label }}</span>
                                                    </div>
                                                    <span class="channel-provider-tag">
                                                        {{ $channel->payment_type === 'instant' ? '⚡ Instan' : ($channel->payment_type === 'va' ? '🤖 VA Otomatis' : '🏢 Transfer Resmi') }}
                                                    </span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color:#64748b; font-size:0.875rem;">Metode pembayaran belum tersedia saat ini.</p>
                    @endif
                </section>

                {{-- 4. DONOR INFORMATION SECTION --}}
                <section class="donor-card">
                    <h2>Data Donatur</h2>
                    <p class="donor-title">
                        @if(auth()->check())
                            <span>Berdonasi sebagai <strong id="donorNameDisplay">{{ auth()->user()->name }}</strong></span>
                        @else
                            <span>Lengkapi data di bawah ini atau <a href="{{ route('login') }}" style="color:#3365af; font-weight:600;">Masuk Akun</a></span>
                        @endif
                    </p>

                    <div class="donor-input-group">
                        <div>
                            <label for="nama_donatur" style="font-size:0.8125rem; font-weight:600; color:#334155; margin-bottom:4px; display:block;">Nama Lengkap</label>
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
                            <label for="no_hp" style="font-size:0.8125rem; font-weight:600; color:#334155; margin-bottom:4px; display:block;">Nomor WhatsApp / HP <span style="color:#dc2626;">*</span></label>
                            <input
                                type="text"
                                name="no_hp"
                                id="no_hp"
                                placeholder="Contoh: 081234567890"
                                value="{{ old('no_hp', auth()->check() ? auth()->user()->nomor : '') }}"
                                required
                            >
                            <div id="error-no_hp" class="error-text" style="display:none;"></div>
                        </div>
                    </div>

                    <p class="input-note mt-2">
                        <span>ⓘ</span>
                        Nomor WhatsApp digunakan untuk mengirimkan konfirmasi dan kuitansi donasi.
                    </p>

                    <label class="switch-row mt-3">
                        <span>Sembunyikan nama saya di daftar donatur (Hamba Allah)</span>
                        <input type="checkbox" name="anonymous_donor" id="anonymous_donor" {{ old('anonymous_donor') ? 'checked' : '' }}>
                        <i></i>
                    </label>
                </section>

                {{-- 5. MESSAGE / DOA SECTION --}}
                <section class="message-card">
                    <h2>Pesan & Doa Kebaikan (Opsional)</h2>

                    <div class="textarea-wrap">
                        <textarea
                            name="pesan"
                            id="pesan"
                            maxlength="255"
                            placeholder="Tuliskan doa atau dukungan hangat Anda untuk penerima manfaat atau penggalang dana."
                        >{{ old('pesan') }}</textarea>
                        <span id="charCount">0/255</span>
                    </div>
                    <div id="error-pesan" class="error-text" style="display:none;"></div>

                    <label class="switch-row mt-3">
                        <span>Sembunyikan isi doa dari publik</span>
                        <input type="checkbox" name="anonymous_message" id="anonymous_message" {{ old('anonymous_message') ? 'checked' : '' }}>
                        <i></i>
                    </label>
                </section>

            </section>

            {{-- RIGHT COLUMN (STICKY SUMMARY) --}}
            <aside class="payment-right">
                <div class="payment-method-card">
                    <h2>Ringkasan Pembayaran</h2>

                    <div class="payment-total">
                        <span>Total Nominal Donasi</span>
                        <strong id="total-donasi">Rp0</strong>
                    </div>

                    <div class="payment-method-info">
                        <strong><i class="bi bi-shield-check text-success"></i> Transaksi Aman & Terenkripsi</strong>
                        <p>Pembayaran Anda diproses secara otomatis dengan keamanan standar perbankan.</p>
                    </div>

                    <button class="pay-button" type="button" id="payButton">
                        <i class="bi bi-lock-fill"></i> Lanjutkan Pembayaran
                    </button>
                    <div id="loading-text" style="display:none; text-align:center; margin-top:10px; font-weight:600; color:#3365af;">
                        ⏳ Memproses transaksi...
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
        const minimalDonasi = {{ $campaign->minimal_donasi ?? 1000 }};

        const totalEl = document.getElementById('total-donasi');
        const nominalRadios = document.querySelectorAll('input[name="nominal"]');
        const nominalLainnya = document.querySelector('input[name="nominal_lainnya"]');
        const charCounter = document.getElementById('charCount');
        const textarea = document.querySelector('textarea[name="pesan"]');

        function updateTotal() {
            let nominal = 0;
            const customValue = parseInt(nominalLainnya.value);
            if (customValue && customValue > 0) {
                nominal = customValue;
            } else {
                const selectedRadio = document.querySelector('input[name="nominal"]:checked');
                if (selectedRadio) {
                    nominal = parseInt(selectedRadio.value) || 0;
                }
            }
            totalEl.textContent = 'Rp ' + nominal.toLocaleString('id-ID');
        }

        function updateCharCount() {
            const count = textarea.value.length;
            charCounter.textContent = count + '/255';
            charCounter.style.color = count > 240 ? '#e74c3c' : '';
        }

        nominalRadios.forEach(radio => radio.addEventListener('change', function() {
            if (this.checked) {
                nominalLainnya.value = '';
            }
            updateTotal();
        }));

        nominalLainnya.addEventListener('input', function() {
            if (this.value && parseInt(this.value) > 0) {
                nominalRadios.forEach(r => r.checked = false);
            }
            updateTotal();
        });

        textarea.addEventListener('input', updateCharCount);
        updateTotal();
        updateCharCount();

        // Anonim realtime sync
        const anonymousDonor = document.getElementById('anonymous_donor');
        const anonymousMessage = document.getElementById('anonymous_message');
        const namaDonaturInput = document.getElementById('nama_donatur');
        const donorNameDisplay = document.getElementById('donorNameDisplay');
        const namaAsli = '{{ auth()->check() ? auth()->user()->name : '' }}';

        function updateAnonim() {
            const isAnonim = anonymousDonor.checked || anonymousMessage.checked;

            if (isAnonim) {
                if (namaDonaturInput) {
                    namaDonaturInput.value = 'Hamba Allah';
                }
                if (donorNameDisplay) {
                    donorNameDisplay.textContent = 'Hamba Allah';
                }
            } else {
                if (namaDonaturInput && !namaDonaturInput.readOnly) {
                    namaDonaturInput.value = namaAsli;
                }
                if (donorNameDisplay) {
                    donorNameDisplay.textContent = namaAsli || 'Donatur';
                }
            }
        }

        anonymousDonor.addEventListener('change', updateAnonim);
        anonymousMessage.addEventListener('change', updateAnonim);

        // Clear error inline
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

        // Submit AJAX
        const payButton = document.getElementById('payButton');
        const loadingText = document.getElementById('loading-text');

        payButton.addEventListener('click', async function() {
            document.querySelectorAll('.error-text').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });

            let nominal = 0;
            const customValue = parseInt(nominalLainnya.value);
            if (customValue && customValue > 0) {
                nominal = customValue;
            } else {
                const selectedRadio = document.querySelector('input[name="nominal"]:checked');
                if (selectedRadio) {
                    nominal = parseInt(selectedRadio.value) || 0;
                }
            }

            if (!validateNominal(nominal)) {
                return;
            }

            const noHpInput = document.getElementById('no_hp');
            if (!noHpInput.value.trim()) {
                const errorHp = document.getElementById('error-no_hp');
                errorHp.textContent = 'Nomor WhatsApp / HP wajib diisi.';
                errorHp.style.display = 'block';
                noHpInput.focus();
                return;
            }

            const form = document.getElementById('donasiForm');
            const formData = new FormData(form);

            payButton.disabled = true;
            payButton.textContent = '⏳ Memproses...';
            loadingText.style.display = 'block';

            try {
                const response = await fetch('{{ route("donasi.store", $campaign->getRouteSlug()) }}', {
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

                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                    return;
                }

                if (result.snap_token) {
                    snap.pay(result.snap_token, {
                        onSuccess: function(res) {
                            window.location.href = '{{ route("donasi.status", "sukses") }}';
                        },
                        onPending: function(res) {
                            // FIX: onPending redirect to pending status (BUKAN sukses!)
                            window.location.href = '{{ route("donasi.status", "pending") }}';
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
                    alert('Gagal mendapatkan informasi pembayaran.');
                    resetButton();
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
                resetButton();
            }
        });

        function resetButton() {
            payButton.disabled = false;
            payButton.innerHTML = '<i class="bi bi-lock-fill"></i> Lanjutkan Pembayaran';
            loadingText.style.display = 'none';
        }
    });
</script>

</body>
</html>