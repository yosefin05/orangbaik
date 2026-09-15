<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi - {{ $campaign->judul }} - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-bayar.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                            <i class="bi bi-patch-check-fill"></i>
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
                    <div class="section-heading">
                        <span class="section-number">1</span>
                        <h2>Pilih Nominal Donasi</h2>
                    </div>

                    <div class="amount-grid">
                        @forelse ($campaign->packages as $index => $package)
                            <label class="amount-chip">
                                <input
                                    type="radio"
                                    name="nominal"
                                    value="{{ $package->nominal }}"
                                    {{ $index === 0 ? 'checked' : '' }}
                                >
                                <span>Rp {{ number_format($package->nominal, 0, ',', '.') }}</span>
                            </label>
                        @empty
                            <label class="amount-chip">
                                <input type="radio" name="nominal" value="10000" checked>
                                <span>Rp10.000</span>
                            </label>
                            <label class="amount-chip">
                                <input type="radio" name="nominal" value="25000">
                                <span>Rp25.000</span>
                            </label>
                            <label class="amount-chip">
                                <input type="radio" name="nominal" value="50000">
                                <span>Rp50.000</span>
                            </label>
                            <label class="amount-chip">
                                <input type="radio" name="nominal" value="100000">
                                <span>Rp100.000</span>
                            </label>
                        @endforelse
                    </div>

                    <p class="custom-amount-label">
                        <i class="bi bi-pencil-square"></i> Atau masukkan nominal lainnya
                    </p>
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
                    <p class="custom-amount-hint">Minimal donasi sebesar Rp {{ number_format($campaign->minimal_donasi ?? 1000, 0, ',', '.') }}</p>
                    <div id="error-nominal" class="error-text" style="display:none;"></div>
                </section>

                {{-- 3. PAYMENT METHOD SECTION --}}
                <section class="payment-channels-section">
                    <div class="section-heading">
                        <span class="section-number">2</span>
                        <h2>Pilih Metode Pembayaran</h2>
                    </div>
                    <div id="error-payment_channel_id" class="error-text" style="display:none;"></div>

                    @if(isset($paymentChannels) && $paymentChannels->isNotEmpty())
                        @php
                            $grouped = $paymentChannels->groupBy(function($item) {
                                return $item->gateway?->name ?? 'Metode Pembayaran Lainnya';
                            });
                        @endphp

                        <div class="channel-groups-wrapper">
                            @foreach ($grouped as $groupName => $groupChannels)
                                <div class="channel-group">
                                    <h3 class="group-heading">
                                        <i class="bi bi-credit-card-2-front"></i>
                                        <span>{{ $groupName }}</span>
                                    </h3>
                                    <div class="channel-row-list">
                                        @foreach ($groupChannels as $channel)
                                            @php
                                                $rowIcon = match ($channel->payment_type) {
                                                    'instant' => 'bi-lightning-charge-fill',
                                                    'va' => 'bi-bank2',
                                                    default => 'bi-building',
                                                };
                                            @endphp
                                            <label class="channel-row">
                                                <input type="radio" name="payment_channel_id" value="{{ $channel->id }}" data-name="{{ $channel->name }}" {{ $loop->parent->first && $loop->first ? 'checked' : '' }}>
                                                <span class="channel-row-icon">
                                                    <i class="bi {{ $rowIcon }}"></i>
                                                </span>
                                                <span class="channel-row-info">
                                                    <strong>{{ $channel->name }}</strong>
                                                    <small>{{ $channel->payment_type_label }}</small>
                                                </span>
                                                <span class="channel-row-check">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                            @endforeach
                        </div>
                    @else
                        <p class="channel-empty-text">Metode pembayaran belum tersedia saat ini.</p>
                    @endif
                </section>

                {{-- 4. DONOR INFORMATION SECTION --}}
                <section class="donor-card">
                    <div class="section-heading">
                        <span class="section-number">3</span>
                        <h2>Data Donatur</h2>
                    </div>
                    <p class="donor-title">
                        @if(auth()->check())
                            <span>Berdonasi sebagai <strong id="donorNameDisplay">{{ auth()->user()->name }}</strong></span>
                        @else
                            <span>Lengkapi data di bawah ini atau <a href="{{ route('login') }}">Masuk Akun</a></span>
                        @endif
                    </p>

                    <div class="donor-input-group">
                        <div>
                            <label for="nama_donatur" class="field-label">Nama Lengkap</label>
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
                            <label for="no_hp" class="field-label">Nomor WhatsApp / HP <span class="required-mark">*</span></label>
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

                    <p class="input-note">
                        <span>ⓘ</span>
                        Nomor WhatsApp digunakan untuk mengirimkan konfirmasi dan kuitansi donasi.
                    </p>

                    <label class="switch-row">
                        <span>Sembunyikan nama saya di daftar donatur (Hamba Allah)</span>
                        <input type="checkbox" name="anonymous_donor" id="anonymous_donor" {{ old('anonymous_donor') ? 'checked' : '' }}>
                        <i></i>
                    </label>
                </section>

                {{-- 5. MESSAGE / DOA SECTION --}}
                <section class="message-card">
                    <div class="section-heading">
                        <span class="section-number">4</span>
                        <h2>Pesan & Doa Kebaikan (Opsional)</h2>
                    </div>

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

                    <label class="switch-row">
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

                    <div class="payment-summary-list">
                        <div class="summary-row">
                            <span>Nominal Donasi</span>
                            <strong id="summary-nominal">Rp0</strong>
                        </div>
                        <div class="summary-row">
                            <span>Metode Pembayaran</span>
                            <strong id="summary-metode">Belum dipilih</strong>
                        </div>
                    </div>

                    <div class="payment-total">
                        <span>Total Bayar</span>
                        <strong id="total-donasi">Rp0</strong>
                    </div>

                    <div class="payment-method-info">
                        <strong><i class="bi bi-shield-check"></i> Transaksi Aman & Terenkripsi</strong>
                        <p>Pembayaran Anda diproses secara otomatis dengan keamanan standar perbankan.</p>
                    </div>

                    <button class="pay-button" type="button" id="payButton">
                        <i class="bi bi-lock-fill"></i> Lanjutkan Pembayaran
                    </button>
                    <div id="loading-text" style="display:none;">
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
        const summaryNominalEl = document.getElementById('summary-nominal');
        const summaryMetodeEl = document.getElementById('summary-metode');
        const nominalRadios = document.querySelectorAll('input[name="nominal"]');
        const nominalLainnya = document.querySelector('input[name="nominal_lainnya"]');
        const channelRadios = document.querySelectorAll('input[name="payment_channel_id"]');
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
            const formatted = 'Rp ' + nominal.toLocaleString('id-ID');
            totalEl.textContent = formatted;
            if (summaryNominalEl) {
                summaryNominalEl.textContent = formatted;
            }
        }

        function updateMetodeSummary() {
            if (!summaryMetodeEl) return;
            const selected = document.querySelector('input[name="payment_channel_id"]:checked');
            summaryMetodeEl.textContent = selected ? (selected.dataset.name || 'Terpilih') : 'Belum dipilih';
        }

        channelRadios.forEach(radio => radio.addEventListener('change', updateMetodeSummary));
        updateMetodeSummary();

        function updateCharCount() {
            const count = textarea.value.length;
            charCounter.textContent = count + '/255';
            charCounter.classList.toggle('char-limit-warning', count > 240);
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