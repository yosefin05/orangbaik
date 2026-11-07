<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Donasi - {{ $campaign->judul }} - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/donasi-bayar.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

{{-- TOPBAR: sama seperti topbar mobile halaman show (tombol kembali + judul) --}}
<header class="payment-topbar" id="paymentTopbar">
    <div class="container payment-topbar-inner">
        <button class="payment-topbar-back" type="button" id="backButton" aria-label="Kembali">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
        </button>

        <div class="payment-topbar-text">
            <h1 class="payment-topbar-title">Donasi</h1>
            <p class="payment-topbar-sub">{{ $campaign->judul }}</p>
        </div>
    </div>
</header>

<main class="payment-page">
    <div class="container">

        {{-- FORM DONASI --}}
        <form class="payment-layout" id="donasiForm" novalidate>
            @csrf

            {{-- LEFT COLUMN --}}
            <div class="payment-left">

                {{-- CAMPAIGN SUMMARY --}}
                <article class="campaign-mini-card">
                    <div class="campaign-mini-head">
                        <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}">
                        <div class="campaign-mini-body">
                            <h2>{{ $campaign->judul }}</h2>
                            <p>
                                <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
                                {{ $campaign->penggalangDana->nama_penggalang ?? 'Penggalang Dana' }}
                            </p>
                        </div>
                    </div>

                    <div class="campaign-mini-stats">
                        <div class="mini-amount">
                            <strong>Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</strong>
                            <span>terkumpul dari Rp {{ $campaign->target_donasi ? number_format($campaign->target_donasi, 0, ',', '.') : '∞' }}</span>
                        </div>

                        <div class="mini-progress">
                            <div style="width: {{ $campaign->target_donasi > 0 ? min(($totalTerkumpul / $campaign->target_donasi) * 100, 100) : 0 }}%;"></div>
                        </div>

                        <div class="mini-meta">
                            <span><i class="bi bi-people" aria-hidden="true"></i> {{ $jumlahDonatur }} Donatur</span>
                            <span><i class="bi bi-calendar-event" aria-hidden="true"></i> {{ $campaign->tanggal_berakhir ? $campaign->tanggal_berakhir->format('d M Y') : 'Tanpa batas waktu' }}</span>
                        </div>
                    </div>
                </article>

                {{-- LANGKAH 1–4: satu kartu alur --}}
                <div class="payment-steps">

                {{-- 1. NOMINAL --}}
                <section class="pay-step">
                    <div class="section-heading">
                        <span class="section-number">1</span>
                        <h2>Pilih Nominal Donasi</h2>
                    </div>

                    <div class="amount-grid" role="radiogroup" aria-label="Pilih nominal donasi">
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

                    <label class="custom-amount-label" for="nominal_lainnya">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i> Atau masukkan nominal lainnya
                    </label>
                    <div class="custom-input-wrap">
                        <span aria-hidden="true">Rp</span>
                        <input
                            type="number"
                            inputmode="numeric"
                            name="nominal_lainnya"
                            id="nominal_lainnya"
                            placeholder="0"
                            min="{{ $campaign->minimal_donasi ?? 1000 }}"
                            value="{{ old('nominal_lainnya') }}"
                        >
                    </div>
                    <p class="custom-amount-hint">Minimal donasi sebesar Rp {{ number_format($campaign->minimal_donasi ?? 1000, 0, ',', '.') }}</p>
                    <div id="error-nominal" class="error-text" role="alert" style="display:none;"></div>
                </section>

                {{-- 2. METODE PEMBAYARAN --}}
                <section class="pay-step">
                    <div class="section-heading">
                        <span class="section-number">2</span>
                        <h2>Pilih Metode Pembayaran</h2>
                    </div>
                    <div id="error-payment_channel_id" class="error-text" role="alert" style="display:none;"></div>

                    @if(isset($paymentChannels) && $paymentChannels->isNotEmpty())
                        @php
                            // Dikelompokkan berdasarkan tipe pembayaran: E-Wallet, Virtual Account, lainnya.
                            $paymentTypeLabels = [
                                'instant' => 'E-Wallet',
                                'va' => 'Virtual Account',
                            ];

                            $groupOrder = ['E-Wallet', 'Virtual Account', 'Transfer Manual'];

                            $grouped = $paymentChannels
                                ->groupBy(function ($item) use ($paymentTypeLabels) {
                                    return $paymentTypeLabels[$item->payment_type] ?? 'Transfer Manual';
                                })
                                ->sortBy(function ($channels, $groupName) use ($groupOrder) {
                                    $index = array_search($groupName, $groupOrder);
                                    return $index === false ? count($groupOrder) : $index;
                                });

                            $groupIcons = [
                                'E-Wallet' => 'bi-wallet2',
                                'Virtual Account' => 'bi-bank2',
                            ];

                            // Pencocokan logo berdasarkan nama channel (aset di public/images/payment-logos).
                            // CATATAN LISENSI: SVG paket "idn-finlogos" berlisensi CC BY-NC 4.0 —
                            // untuk produksi sebaiknya diganti aset resmi dari tiap penyedia.
                            $logoMap = [
                                'gopay' => 'gopay',
                                'ovo' => 'ovo-new',
                                'dana' => 'dana',
                                'shopeepay' => 'shopee-pay',
                                'shopee pay' => 'shopee-pay',
                                'linkaja' => 'linkaja',
                                'link aja' => 'linkaja',
                                'astrapay' => 'astra-pay',
                                'astra pay' => 'astra-pay',
                                'doku' => 'doku',
                                'bca' => 'bca',
                                'mandiri' => 'mandiri',
                                'bni' => 'bni',
                                'bri' => 'bri',
                                'permata' => 'permata',
                                'cimb' => 'cimb-niaga',
                                'danamon' => 'danamon',
                                'bsi' => 'bsi',
                                'btn' => 'btn',
                                'jago' => 'jago',
                                'jenius' => 'jenius',
                                'seabank' => 'seabank',
                                'qris' => 'qris',
                                'midtrans' => 'midtrans',
                                'xendit' => 'xendit',
                                'flip' => 'flip',
                            ];

                            $findLogoSlug = function (string $channelName) use ($logoMap) {
                                $name = strtolower($channelName);
                                foreach ($logoMap as $keyword => $slug) {
                                    if (str_contains($name, $keyword)) {
                                        return $slug;
                                    }
                                }
                                return null;
                            };
                        @endphp

                        {{-- Tiap kategori = accordion native <details>; hanya satu terbuka (diatur JS). --}}
                        <div class="channel-groups-wrapper">
                            @foreach ($grouped as $groupName => $groupChannels)
                                <details class="channel-group" {{ $loop->first ? 'open' : '' }}>
                                    <summary class="group-heading">
                                        <span class="group-heading-icon">
                                            <i class="bi {{ $groupIcons[$groupName] ?? 'bi-credit-card-2-front' }}" aria-hidden="true"></i>
                                        </span>
                                        <span class="group-heading-text">{{ $groupName }}</span>
                                        <span class="group-heading-selected" data-group-selected></span>
                                        <i class="bi bi-chevron-down group-heading-chevron" aria-hidden="true"></i>
                                    </summary>

                                    <div class="channel-row-list">
                                        @foreach ($groupChannels as $channel)
                                            @php
                                                $rowIcon = match ($channel->payment_type) {
                                                    'instant' => 'bi-lightning-charge-fill',
                                                    'va' => 'bi-bank2',
                                                    default => 'bi-building',
                                                };
                                                $logoSlug = $findLogoSlug($channel->name);
                                            @endphp
                                            <label class="channel-row">
                                                <input type="radio" name="payment_channel_id" value="{{ $channel->id }}" data-name="{{ $channel->name }}" {{ $loop->parent->first && $loop->first ? 'checked' : '' }}>
                                                <span class="channel-row-icon">
                                                    @if ($logoSlug)
                                                        <img src="{{ asset('images/payment-logos/' . $logoSlug . '.svg') }}" alt="" loading="lazy">
                                                    @else
                                                        <i class="bi {{ $rowIcon }}" aria-hidden="true"></i>
                                                    @endif
                                                </span>
                                                <span class="channel-row-info">
                                                    <strong>{{ $channel->name }}</strong>
                                                    <small>{{ $channel->payment_type_label }}</small>
                                                </span>
                                                <span class="channel-row-mark" aria-hidden="true"></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @else
                        <p class="channel-empty-text">Metode pembayaran belum tersedia saat ini.</p>
                    @endif
                </section>

                {{-- 3. DATA DONATUR --}}
                <section class="pay-step">
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
                                class="field-input"
                                type="text"
                                name="nama_donatur"
                                id="nama_donatur"
                                autocomplete="name"
                                placeholder="Masukkan Nama Lengkap"
                                value="{{ old('nama_donatur', auth()->check() ? auth()->user()->name : '') }}"
                                {{ auth()->check() ? 'readonly' : '' }}
                            >
                            <div id="error-nama_donatur" class="error-text" role="alert" style="display:none;"></div>
                        </div>

                        <div>
                            <label for="no_hp" class="field-label">Nomor WhatsApp / HP <span class="required-mark" aria-hidden="true">*</span></label>
                            <input
                                class="field-input"
                                type="tel"
                                inputmode="tel"
                                name="no_hp"
                                id="no_hp"
                                autocomplete="tel"
                                placeholder="Contoh: 081234567890"
                                value="{{ old('no_hp', auth()->check() ? auth()->user()->nomor : '') }}"
                                required
                            >
                            <div id="error-no_hp" class="error-text" role="alert" style="display:none;"></div>
                        </div>
                    </div>

                    <p class="input-note">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <span>Nomor WhatsApp digunakan untuk mengirimkan konfirmasi dan kuitansi donasi.</span>
                    </p>

                    <label class="switch-row">
                        <span>Sembunyikan nama saya di daftar donatur (Hamba Allah)</span>
                        <input type="checkbox" name="anonymous_donor" id="anonymous_donor" {{ old('anonymous_donor') ? 'checked' : '' }}>
                        <i aria-hidden="true"></i>
                    </label>
                </section>

                {{-- 4. PESAN & DOA --}}
                <section class="pay-step">
                    <div class="section-heading">
                        <span class="section-number">4</span>
                        <h2>Pesan &amp; Doa Kebaikan (Opsional)</h2>
                    </div>

                    <div class="textarea-wrap">
                        <textarea
                            name="pesan"
                            id="pesan"
                            maxlength="255"
                            aria-label="Pesan dan doa kebaikan"
                            placeholder="Tuliskan doa atau dukungan hangat Anda untuk penerima manfaat atau penggalang dana."
                        >{{ old('pesan') }}</textarea>
                        <span id="charCount" aria-hidden="true">0/255</span>
                    </div>
                    <div id="error-pesan" class="error-text" role="alert" style="display:none;"></div>

                    <label class="switch-row">
                        <span>Sembunyikan isi doa dari publik</span>
                        <input type="checkbox" name="anonymous_message" id="anonymous_message" {{ old('anonymous_message') ? 'checked' : '' }}>
                        <i aria-hidden="true"></i>
                    </label>
                </section>

                </div>

            </div>

            {{-- RIGHT COLUMN (RINGKASAN, sticky di desktop) --}}
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
                    
                    <button class="pay-button js-pay" type="button" id="payButton">
                        Lanjutkan Pembayaran
                    </button>
                    <div id="loading-text" role="status" hidden>
                        Memproses transaksi...
                    </div>
                </div>
            </aside>

        </form>
    </div>
</main>

{{-- CTA BAWAH (mobile) --}}
<div class="payment-sticky-cta">
    <div class="payment-cta-info">
        <span class="payment-cta-label">Total Bayar</span>
        <strong class="payment-cta-val" id="cta-total">Rp0</strong>
    </div>
    <button class="btn-pay-mobile js-pay" type="button">
        Bayar Sekarang
    </button>
</div>

<!-- Midtrans Snap -->
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.clientKey') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const minimalDonasi = {{ (int) ($campaign->minimal_donasi ?? 1000) }};
        const storeUrl = @json(route('donasi.store', $campaign->getRouteSlug()));
        const statusUrl = {
            sukses: @json(route('donasi.status', 'sukses')),
            pending: @json(route('donasi.status', 'pending')),
            gagal: @json(route('donasi.status', 'gagal')),
        };
        const namaAsli = @json(auth()->check() ? auth()->user()->name : '');

        const topbar = document.getElementById('paymentTopbar');
        const totalEl = document.getElementById('total-donasi');
        const ctaTotalEl = document.getElementById('cta-total');
        const summaryNominalEl = document.getElementById('summary-nominal');
        const summaryMetodeEl = document.getElementById('summary-metode');
        const nominalRadios = document.querySelectorAll('input[name="nominal"]');
        const nominalLainnya = document.getElementById('nominal_lainnya');
        const channelRadios = document.querySelectorAll('input[name="payment_channel_id"]');
        const channelGroups = document.querySelectorAll('.channel-group');
        const charCounter = document.getElementById('charCount');
        const textarea = document.getElementById('pesan');
        const payButtons = document.querySelectorAll('.js-pay');
        const loadingText = document.getElementById('loading-text');

        // ----- Topbar -----
        document.getElementById('backButton').addEventListener('click', function () {
            history.back();
        });

        function updateTopbar() {
            topbar.classList.toggle('is-scrolled', window.scrollY > 4);
        }
        window.addEventListener('scroll', updateTopbar, { passive: true });
        updateTopbar();

        // ----- Nominal & ringkasan -----
        function getNominal() {
            const customValue = parseInt(nominalLainnya.value, 10);
            if (customValue && customValue > 0) {
                return customValue;
            }
            const selectedRadio = document.querySelector('input[name="nominal"]:checked');
            return selectedRadio ? (parseInt(selectedRadio.value, 10) || 0) : 0;
        }

        function updateTotal() {
            const formatted = 'Rp ' + getNominal().toLocaleString('id-ID');
            totalEl.textContent = formatted;
            ctaTotalEl.textContent = formatted;
            summaryNominalEl.textContent = formatted;
        }

        function updateMetodeSummary() {
            const selected = document.querySelector('input[name="payment_channel_id"]:checked');
            summaryMetodeEl.textContent = selected ? (selected.dataset.name || 'Terpilih') : 'Belum dipilih';
        }

        // Tampilkan metode terpilih di judul tiap dropdown, supaya tetap terlihat saat tertutup.
        function updateGroupSelectedLabels() {
            channelGroups.forEach(function (group) {
                const checked = group.querySelector('input[name="payment_channel_id"]:checked');
                const label = group.querySelector('[data-group-selected]');
                if (label) {
                    label.textContent = checked ? checked.dataset.name : '';
                }
            });
        }

        // Accordion: buka satu dropdown, tutup yang lain.
        channelGroups.forEach(function (group) {
            group.addEventListener('toggle', function () {
                if (group.open) {
                    channelGroups.forEach(function (other) {
                        if (other !== group) other.open = false;
                    });
                }
            });
        });

        channelRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                updateMetodeSummary();
                updateGroupSelectedLabels();
            });
        });

        nominalRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (this.checked) nominalLainnya.value = '';
                updateTotal();
            });
        });

        nominalLainnya.addEventListener('input', function () {
            if (this.value && parseInt(this.value, 10) > 0) {
                nominalRadios.forEach(function (r) { r.checked = false; });
            }
            updateTotal();
        });

        function updateCharCount() {
            const count = textarea.value.length;
            charCounter.textContent = count + '/255';
            charCounter.classList.toggle('char-limit-warning', count > 240);
        }
        textarea.addEventListener('input', updateCharCount);

        updateMetodeSummary();
        updateGroupSelectedLabels();
        updateTotal();
        updateCharCount();

        // ----- Anonim (realtime) -----
        const anonymousDonor = document.getElementById('anonymous_donor');
        const anonymousMessage = document.getElementById('anonymous_message');
        const namaDonaturInput = document.getElementById('nama_donatur');
        const donorNameDisplay = document.getElementById('donorNameDisplay');
        const labelAnonim = 'Hamba Allah';

        // Nama yang sedang diketik/dimiliki donatur, supaya tidak hilang saat switch dibolak-balik.
        let namaTersimpan = namaDonaturInput ? namaDonaturInput.value : '';

        function isAnonim() {
            return anonymousDonor.checked || anonymousMessage.checked;
        }

        namaDonaturInput.addEventListener('input', function () {
            if (!isAnonim()) namaTersimpan = this.value;
        });

        function updateAnonim() {
            if (isAnonim()) {
                if (namaDonaturInput.value !== labelAnonim) {
                    namaTersimpan = namaDonaturInput.value;
                }
                namaDonaturInput.value = labelAnonim;
                if (donorNameDisplay) donorNameDisplay.textContent = labelAnonim;
            } else {
                namaDonaturInput.value = namaTersimpan || namaAsli;
                if (donorNameDisplay) donorNameDisplay.textContent = namaAsli || 'Donatur';
            }
        }

        anonymousDonor.addEventListener('change', updateAnonim);
        anonymousMessage.addEventListener('change', updateAnonim);

        // ----- Hapus error inline saat mengetik -----
        document.querySelectorAll('#nama_donatur, #no_hp, #pesan, #nominal_lainnya').forEach(function (el) {
            el.addEventListener('input', function () {
                const errorEl = document.getElementById('error-' + this.id);
                if (errorEl) {
                    errorEl.style.display = 'none';
                    errorEl.textContent = '';
                }
            });
        });

        function showError(field, message) {
            const errorEl = document.getElementById('error-' + field);
            if (!errorEl) return;
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }

        function validateNominal(nominal) {
            const errorEl = document.getElementById('error-nominal');
            if (nominal < minimalDonasi) {
                showError('nominal', 'Minimal donasi Rp ' + minimalDonasi.toLocaleString('id-ID'));
                errorEl.scrollIntoView({ block: 'center', behavior: 'smooth' });
                return false;
            }
            errorEl.style.display = 'none';
            return true;
        }

        // ----- Tombol bayar (desktop & CTA mobile berbagi satu logika) -----
        payButtons.forEach(function (btn) {
            btn.dataset.label = btn.textContent.trim();
        });

        function setLoading(isLoading) {
            payButtons.forEach(function (btn) {
                btn.disabled = isLoading;
                btn.textContent = isLoading ? 'Memproses...' : btn.dataset.label;
            });
            loadingText.hidden = !isLoading;
        }

        async function submitDonasi() {
            document.querySelectorAll('.error-text').forEach(function (el) {
                el.style.display = 'none';
                el.textContent = '';
            });

            if (!validateNominal(getNominal())) return;

            const noHpInput = document.getElementById('no_hp');
            if (!noHpInput.value.trim()) {
                showError('no_hp', 'Nomor WhatsApp / HP wajib diisi.');
                noHpInput.focus();
                return;
            }

            const form = document.getElementById('donasiForm');
            const formData = new FormData(form);

            setLoading(true);

            try {
                const response = await fetch(storeUrl, {
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
                        let firstField = null;
                        for (const [field, messages] of Object.entries(result.errors)) {
                            showError(field, messages[0]);
                            if (!firstField) firstField = field;
                        }
                        const firstEl = firstField && document.getElementById('error-' + firstField);
                        if (firstEl) firstEl.scrollIntoView({ block: 'center', behavior: 'smooth' });
                    } else {
                        alert(result.message || 'Terjadi kesalahan. Silakan coba lagi.');
                    }
                    setLoading(false);
                    return;
                }

                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                    return;
                }

                if (result.snap_token) {
                    snap.pay(result.snap_token, {
                        onSuccess: function () {
                            window.location.href = statusUrl.sukses;
                        },
                        onPending: function () {
                            const url = new URL(statusUrl.pending, window.location.origin);
                            url.searchParams.set('pembayaran', result.pembayaran_id);
                            url.searchParams.set('token', result.payment_token);
                            window.location.href = url.toString();
                        },
                        onError: function () {
                            window.location.href = statusUrl.gagal;
                        },
                        onClose: function () {
                            setLoading(false);
                            alert('Anda menutup popup pembayaran.');
                        }
                    });
                } else {
                    alert('Gagal mendapatkan informasi pembayaran.');
                    setLoading(false);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
                setLoading(false);
            }
        }

        payButtons.forEach(function (btn) {
            btn.addEventListener('click', submitDonasi);
        });
    });
</script>

</body>
</html>