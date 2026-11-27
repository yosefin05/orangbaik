@extends('layouts.admin')

@section('page-title', 'Konfigurasi ' . $gateway->name)

@section('content')
@php
    $isMidtrans = $gateway->code === 'midtrans';
    $isFlip = $gateway->code === 'flip';
    $isManual = $gateway->code === 'manual';
    $mode = $gateway->activeCredentialMode();
    $isConfigured = $gateway->isConfigured();
@endphp

<div class="payment-detail">
    <a href="{{ route('admin.payment.gateway.index') }}" class="payment-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Payment Gateway
    </a>

    @if (session('success'))
        <div class="alert alert-success"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> {{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Periksa kembali konfigurasi:</strong>
            <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <header class="payment-detail__header">
        <div class="payment-provider">
            <div class="payment-provider__icon">
                @if ($isMidtrans)<i class="bi bi-lightning-charge-fill"></i>
                @elseif ($isFlip)<i class="bi bi-arrow-left-right"></i>
                @else<i class="bi bi-bank"></i>@endif
            </div>
            <div>
                <span class="payment-eyebrow">PAYMENT PROVIDER</span>
                <h1>{{ $gateway->name }}</h1>
                <p>{{ $isMidtrans ? 'Kelola kredensial dan koneksi Midtrans.' : ($isFlip ? 'Kelola kredensial dan koneksi Flip.' : 'Kelola rekening tujuan dan metode transfer manual OrangBaik.') }}</p>
            </div>
        </div>
        <div class="payment-header-actions">
            <span class="payment-status {{ $gateway->is_active ? 'is-active' : 'is-inactive' }}">
                <span></span>{{ $gateway->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
            <form action="{{ route('admin.payment.gateway.toggle', $gateway) }}" method="POST"
                onsubmit="return confirm('{{ $gateway->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $gateway->name }}?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $gateway->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} btn-sm">
                    <i class="bi bi-{{ $gateway->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                    {{ $gateway->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
        </div>
    </header>

    <div class="payment-summary">
        <div class="payment-summary__item">
            <span>Status konfigurasi</span>
            <strong>{{ $isManual ? 'Rekening dikelola melalui channel' : ($isConfigured ? 'Kredensial lengkap' : 'Belum lengkap') }}</strong>
            <small>{{ $isManual ? 'Pastikan minimal satu rekening aktif.' : 'Pemeriksaan kelengkapan key, bukan tes koneksi API.' }}</small>
        </div>
        <div class="payment-summary__item">
            <span>Environment aplikasi</span>
            <strong>{{ app()->environment() === 'production' ? 'Production' : ucfirst(app()->environment()) }}</strong>
            <small>{{ $isManual ? 'Tidak menggunakan API provider.' : 'Mode pembayaran dipilih dari pengaturan provider di bawah.' }}</small>
        </div>
        <div class="payment-summary__item">
            <span>Status provider</span>
            <strong>{{ $gateway->is_active ? ($isManual ? 'Aktif' : 'Aktif · belum diverifikasi') : 'Tidak menerima pembayaran' }}</strong>
            <small>Status aktif hanya mengizinkan gateway menerima alur pembayaran; koneksi dan webhook tetap perlu diverifikasi.</small>
        </div>
    </div>

    @if ($isManual)
        <section class="payment-panel">
            <div class="payment-panel__heading">
                <div>
                    <span class="payment-eyebrow">TRANSFER MANUAL</span>
                    <h2>Rekening tujuan donasi</h2>
                    <p>Informasi rekening dikelola sebagai payment channel. Histori transaksi lama tidak boleh diubah.</p>
                </div>
                <a href="{{ route('admin.payment.channel.index') }}" class="btn btn-primary">
                    <i class="bi bi-bank"></i> Kelola rekening
                </a>
            </div>

            @if ($channels->isEmpty())
                <div class="payment-empty">
                    <i class="bi bi-bank"></i>
                    <strong>Belum ada rekening terdaftar</strong>
                    <p>Tambahkan rekening yayasan agar donatur dapat memilih transfer manual.</p>
                    <a href="{{ route('admin.payment.channel.create') }}" class="btn btn-primary">Tambah rekening</a>
                </div>
            @else
                <div class="payment-bank-list">
                    @foreach ($channels as $channel)
                        <article class="payment-bank">
                            <div class="payment-bank__icon"><i class="bi bi-bank"></i></div>
                            <div class="payment-bank__main">
                                <strong>{{ $channel->name }}</strong>
                                <span>{{ $channel->account_name ?: 'Nama pemilik belum diisi' }}</span>
                                <code>{{ $channel->account_number ?: 'Nomor rekening belum diisi' }}</code>
                            </div>
                            <span class="payment-status {{ $channel->is_active ? 'is-active' : 'is-inactive' }}">
                                <span></span>{{ $channel->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <section class="payment-panel">
            <div class="payment-panel__heading">
                <div>
                    <span class="payment-eyebrow">PAYMENT METHODS</span>
                    <h2>Metode yang ditampilkan di OrangBaik</h2>
                    <p>Aktifkan metode yang ingin ditampilkan di halaman donasi. Metode tersebut juga harus sudah diaktifkan pada dashboard merchant {{ $gateway->name }}. Nomor dan nama rekening tidak perlu diisi di sini.</p>
                </div>
            </div>

            <div class="payment-method-catalog">
                <div class="payment-method-catalog__heading">
                    <div>
                        <strong>Daftar metode yang bisa ditambahkan</strong>
                        <p>{{ $isMidtrans ? 'E-wallet yang didukung integrasi Snap. Pastikan metode sudah diaktifkan pada dashboard Midtrans.' : 'Virtual Account, OVO, DANA, dan QRIS melalui Direct API v3. Semua metode memerlukan mode Flip Staging atau Production serta aktivasi merchant.' }}</p>
                    </div>
                    <span class="payment-catalog-count">{{ $availableMethods->count() }} tersedia</span>
                </div>
                @if ($availableMethods->isNotEmpty())
                    <div class="payment-catalog-grid">
                        @foreach ($availableMethods as $method)
                            <article class="payment-catalog-item">
                                <div class="payment-catalog-item__info">
                                    <span class="payment-catalog-item__icon"><i class="bi bi-{{ $method['payment_type'] === 'instant' ? 'wallet2' : 'bank' }}"></i></span>
                                    <span>
                                        <strong>{{ $method['name'] }}</strong>
                                        <small>{{ $method['payment_type'] === 'instant' ? 'E-Wallet · Direct API v3' : 'Virtual Account' }}</small>
                                    </span>
                                </div>
                                <form action="{{ route('admin.payment.gateway.methods.store', $gateway) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="channel_code" value="{{ $method['channel_code'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-plus-lg"></i> Tambahkan
                                    </button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="payment-catalog-empty">
                        <i class="bi bi-check2-circle"></i>
                        Semua metode pada katalog adapter sudah terdaftar.
                    </div>
                @endif
                <p class="payment-catalog-note"><i class="bi bi-info-circle"></i> Metode baru ditambahkan dalam keadaan tersembunyi. Setelah memastikan aktivasi di dashboard provider dan kredensial siap, tampilkan melalui kontrol di daftar metode di bawah.</p>
            </div>

            <div class="payment-method-list">
                @forelse ($channels as $channel)
                    <div class="payment-method-row">
                        <div class="payment-method-row__main">
                            <strong>{{ $channel->name }}</strong>
                            <span>{{ $channel->payment_type_label }} · Kode {{ strtoupper($channel->channel_code) }}</span>
                            <form action="{{ route('admin.payment.channel.routing', $channel) }}" method="POST" class="payment-routing-form">
                                @csrf
                                @method('PATCH')
                                <label class="visually-hidden" for="routing-{{ $channel->id }}">Provider {{ $channel->name }}</label>
                                <div class="payment-routing-form__controls">
                                    <select id="routing-{{ $channel->id }}" name="payment_gateway_id" class="form-select">
                                    @foreach ($routingOptions as $routingOption)
                                        <option value="{{ $routingOption->id }}" @selected($channel->payment_gateway_id === $routingOption->id) @disabled(!$routingOption->supportsPaymentType($channel->payment_type))>{{ $routingOption->name }}{{ $routingOption->supportsPaymentType($channel->payment_type) ? '' : ' · belum mendukung' }}</option>
                                    @endforeach
                                </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Simpan routing</button>
                                </div>
                            </form>
                        </div>
                        <div class="payment-method-row__actions">
                            <span class="payment-status {{ $channel->is_active ? 'is-active' : 'is-inactive' }}"><span></span>{{ $channel->is_active ? 'Ditampilkan' : 'Disembunyikan' }}</span>
                            <form action="{{ route('admin.payment.channel.toggle', $channel) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $channel->is_active ? 'btn-outline-secondary' : 'btn-outline-primary' }}" @disabled(!$isConfigured || !$gateway->is_active) title="Aktifkan gateway dan lengkapi kredensial terlebih dahulu">
                                {{ $channel->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                            </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="payment-empty">
                        <i class="bi bi-credit-card"></i>
                        <strong>Belum ada metode yang terdaftar</strong>
                        <p>Tambahkan metode yang dibutuhkan dari katalog di atas. Metode baru akan tersimpan dalam keadaan tersembunyi.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <form action="{{ route('admin.payment.gateway.update', $gateway) }}" method="POST" class="payment-config-form">
            @csrf
            @method('PUT')
            <section class="payment-panel">
                <div class="payment-panel__heading">
                    <div>
                        <span class="payment-eyebrow">GENERAL SETTINGS</span>
                        <h2>Informasi integrasi</h2>
                        <p>Atur identitas provider dan environment kredensial yang akan digunakan.</p>
                    </div>
                </div>
                <div class="payment-form-grid">
                    <div class="payment-field payment-field--full">
                        <label for="gateway-name">Nama provider</label>
                        <input id="gateway-name" name="name" class="form-control" value="{{ old('name', $gateway->name) }}" required maxlength="100">
                    </div>
                    <div class="payment-field payment-field--full">
                        <label for="gateway-description">Deskripsi <span>Opsional</span></label>
                        <input id="gateway-description" name="description" class="form-control" value="{{ old('description', $gateway->description) }}" maxlength="255" placeholder="Keterangan integrasi untuk admin">
                    </div>
                    <div class="payment-field payment-field--full">
                        <label for="gateway-webhook-url">Webhook Notification URL</label>
                        <input id="gateway-webhook-url" class="form-control" value="{{ route('payment.gateway.webhook', ['gateway' => $gateway->code]) }}" readonly onclick="this.select()">
                        <small>Salin URL ini ke pengaturan Notification URL / Callback URL di dashboard merchant {{ $gateway->name }}. Pastikan Webhook Token atau Signature Secret Key pada kedua sisi cocok.</small>
                    </div>
                    <div class="payment-field payment-field--full">
                        <label for="credential-mode">Mode pembayaran</label>
                        <select id="credential-mode" name="credential_mode" class="form-select">
                            @foreach (($isFlip ? ['sandbox' => 'Sandbox (VA legacy)', 'staging' => 'Flip Staging (Direct API v3)', 'production' => 'Production / Live'] : ['sandbox' => 'Sandbox', 'production' => 'Production / Live']) as $modeValue => $modeLabel)
                                <option value="{{ $modeValue }}" @selected(old('credential_mode', $gateway->activeCredentialMode()) === $modeValue)>{{ $modeLabel }}</option>
                            @endforeach
                        </select>
                        <small>{{ $isFlip ? 'OVO dan DANA menggunakan Direct API v3 pada Flip Staging atau Production. Saat mode Staging dipilih, Virtual Account tetap memakai kredensial Sandbox legacy.' : 'Production / Live memproses transaksi sungguhan, termasuk jika aplikasi berjalan di staging. Pastikan kredensial dan webhook sesuai environment.' }}</small>
                    </div>
                </div>
            </section>

            @if ($isMidtrans)
                @foreach (($isFlip ? ['sandbox' => 'Sandbox', 'staging' => 'Flip Staging', 'production' => 'Production'] : ['sandbox' => 'Sandbox', 'production' => 'Production']) as $environment => $label)
                    <section class="payment-panel">
                        <div class="payment-panel__heading">
                            <div>
                                <span class="payment-eyebrow">{{ strtoupper($environment) }} CREDENTIALS</span>
                                <h2>{{ $label }}</h2>
                                <p>{{ $environment === 'sandbox' ? 'Gunakan key dari dashboard Midtrans Sandbox untuk pengembangan dan staging.' : 'Gunakan key Live dari dashboard Midtrans. Simpan hanya setelah siap digunakan di server Production.' }}</p>
                            </div>
                            <span class="payment-env {{ $environment === 'sandbox' ? 'payment-env--sandbox' : 'payment-env--live' }}">{{ $label }}</span>
                        </div>
                        <div class="payment-form-grid">
                            @foreach ([
                                $environment . '_server_key' => 'Server Key',
                                $environment . '_client_key' => 'Client Key',
                                $environment . '_signature_secret_key' => 'Signature Secret Key',
                            ] as $key => $labelText)
                                <div class="payment-field">
                                    <label for="{{ $key }}">{{ $labelText }}</label>
                                    <div class="payment-secret-control">
                                        <input id="{{ $key }}" name="{{ $key }}" type="password" class="form-control" autocomplete="off" value="" placeholder="{{ $credentialFlags[$key] ? 'Tersimpan — klik mata untuk melihat' : 'Masukkan ' . $labelText }}">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-reveal-secret="{{ $key }}" data-reveal-url="{{ route('admin.payment.gateway.credentials.reveal', $gateway) }}" aria-label="Tampilkan {{ $labelText }}"><i class="bi bi-eye"></i></button>
                                    </div>
                                    <small>{{ $credentialFlags[$key] ? 'Kredensial tersimpan dan tidak dikirim ke halaman ini. Klik mata untuk meminta tampilan sementara.' : 'Belum ada kredensial tersimpan.' }}</small>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @else
                @foreach (['sandbox' => 'Sandbox', 'staging' => 'Flip Staging', 'production' => 'Production'] as $environment => $label)
                    <section class="payment-panel">
                        <div class="payment-panel__heading">
                            <div>
                                <span class="payment-eyebrow">{{ strtoupper($environment) }} CREDENTIALS</span>
                                <h2>{{ $label }}</h2>
                                <p>{{ $environment === 'sandbox' ? 'Kredensial untuk alur Virtual Account legacy pada Sandbox.' : ($environment === 'staging' ? 'Kredensial Direct API v3 dari lingkungan staging Flip yang sudah diberikan ke merchant.' : 'Kredensial Live Flip. Transaksi yang dibuat akan memproses uang sungguhan.' ) }}</p>
                            </div>
                            <span class="payment-env {{ $environment === 'sandbox' ? 'payment-env--sandbox' : 'payment-env--live' }}">{{ $label }}</span>
                        </div>
                        <div class="payment-form-grid">
                            @foreach ([
                                $environment . '_api_key' => 'API Secret Key',
                                $environment . '_webhook_token' => 'Webhook Token',
                            ] as $key => $labelText)
                                <div class="payment-field">
                                    <label for="{{ $key }}">{{ $labelText }}</label>
                                    <div class="payment-secret-control">
                                        <input id="{{ $key }}" name="{{ $key }}" type="password" class="form-control" autocomplete="off" value="" placeholder="{{ $credentialFlags[$key] ? 'Tersimpan — klik mata untuk melihat' : 'Masukkan ' . $labelText }}">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-reveal-secret="{{ $key }}" data-reveal-url="{{ route('admin.payment.gateway.credentials.reveal', $gateway) }}" aria-label="Tampilkan {{ $labelText }}"><i class="bi bi-eye"></i></button>
                                    </div>
                                    <small>{{ $credentialFlags[$key] ? 'Kredensial tersimpan dan tidak dikirim ke halaman ini. Klik mata untuk meminta tampilan sementara.' : 'Belum ada kredensial tersimpan.' }}</small>
                                </div>
                            @endforeach
                        </div>
                        @if ($isFlip && $environment === 'staging')
                            <div class="payment-form-grid mt-3">
                                <div class="payment-field payment-field--full">
                                    <label for="staging_base_url">Flip Staging Base URL</label>
                                    <input id="staging_base_url" name="staging_base_url" type="url" class="form-control" value="{{ old('staging_base_url', data_get($gateway->config, 'staging_base_url')) }}" placeholder="https://domain-staging-dari-flip">
                                    <small>Masukkan domain staging resmi yang diberikan tim Flip, tanpa path /big_api/v3/pwf/bill.</small>
                                </div>
                            </div>
                        @endif
                    </section>
                @endforeach
            @endif

            <div class="payment-form-footer">
                <p><i class="bi bi-shield-lock"></i> Kredensial dienkripsi saat disimpan. Kolom kosong tidak akan menimpa key yang sudah tersimpan.</p>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Simpan konfigurasi</button>
            </div>
        </form>

        <p class="payment-security-note"><i class="bi bi-info-circle"></i> Pengujian koneksi API belum tersedia. Status kredensial tersimpan tidak berarti koneksi provider sudah terverifikasi.</p>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', async function (event) {
    const button = event.target.closest('[data-reveal-secret]');
    if (!button) return;
    const input = document.getElementById(button.dataset.revealSecret);
    if (!input) return;

    if (input.type === 'text' && input.value !== '') {
        input.type = 'password';
        button.innerHTML = '<i class="bi bi-eye"></i>';
        return;
    }

    button.disabled = true;
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value;
        const response = await fetch(button.dataset.revealUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf || ''
            },
            body: JSON.stringify({ key: button.dataset.revealSecret })
        });
        if (!response.ok) throw new Error('Credential reveal rejected');
        const payload = await response.json();
        input.value = payload.value || '';
        input.type = 'text';
        button.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } catch (error) {
        window.alert('Kredensial gagal ditampilkan. Muat ulang halaman dan coba lagi.');
    } finally {
        button.disabled = false;
    }
});
</script>
@endpush

@push('styles')
<style>
.payment-detail{--pd-blue:#3365af;--pd-ink:#172b4d;--pd-muted:#667085;--pd-line:#e7ebf2;max-width:1180px;margin:0 auto;color:var(--pd-ink)}
.payment-detail *{box-sizing:border-box}
.payment-back{display:inline-flex;align-items:center;gap:8px;color:var(--pd-muted);text-decoration:none;font-size:13px;font-weight:600;margin-bottom:22px}
.payment-back:hover{color:var(--pd-blue)}
.payment-detail__header{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:22px}
.payment-provider{display:flex;align-items:center;gap:16px;min-width:0}
.payment-provider__icon{width:54px;height:54px;display:grid;place-items:center;flex:none;border-radius:15px;background:#edf3fc;color:var(--pd-blue);font-size:24px}
.payment-eyebrow{display:block;color:#8490a3;font-size:10px;font-weight:750;letter-spacing:.12em;margin-bottom:6px}
.payment-provider h1{font-size:26px;line-height:1.2;letter-spacing:-.04em;margin:0;color:var(--pd-ink)}
.payment-provider p,.payment-panel__heading p{color:var(--pd-muted);font-size:13px;line-height:1.65;margin:6px 0 0;max-width:760px}
.payment-header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
.payment-header-actions form,.payment-method-row form{margin:0}
.payment-security-note{display:flex;align-items:flex-start;gap:8px;color:var(--pd-muted);font-size:11px;line-height:1.55;margin:0 2px 16px}
.payment-security-note i{color:var(--pd-blue);margin-top:1px}
.payment-status{display:inline-flex;align-items:center;gap:7px;padding:7px 10px;border-radius:999px;font-size:11px;font-weight:700;white-space:nowrap}
.payment-status>span{width:7px;height:7px;border-radius:50%;background:currentColor}
.payment-status.is-active{background:#eaf7ef;color:#18794e}
.payment-status.is-inactive{background:#f1f3f6;color:#667085}
.payment-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}
.payment-summary__item{min-width:0;padding:17px 18px;background:#fff;border:1px solid var(--pd-line);border-radius:13px}
.payment-summary__item span,.payment-summary__item small{display:block;color:var(--pd-muted);font-size:11px;line-height:1.5}
.payment-summary__item strong{display:block;margin:7px 0 4px;font-size:15px;line-height:1.4;color:var(--pd-ink)}
.payment-panel{background:#fff;border:1px solid var(--pd-line);border-radius:15px;padding:24px;margin-bottom:16px;box-shadow:0 3px 14px rgba(20,40,80,.025)}
.payment-panel__heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}
.payment-panel__heading h2{font-size:17px;font-weight:700;letter-spacing:-.02em;margin:0;color:var(--pd-ink)}
.payment-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px 20px}
.payment-secret-control{display:flex;align-items:stretch;gap:7px}
.payment-secret-control .form-control{min-width:0;flex:1}
.payment-secret-control .btn{flex:none;min-width:42px}
.payment-field{min-width:0}
.payment-field--full{grid-column:1/-1}
.payment-field>label{display:block;font-size:12px;font-weight:650;color:#344054;margin-bottom:7px}
.payment-field>label span{font-size:10px;color:#98a2b3;font-weight:500;margin-left:4px}
.payment-field .form-control{width:100%;min-height:44px;border:1px solid #d9e0ea;border-radius:9px;padding:10px 12px;font-size:13px;box-shadow:none}
.payment-field .form-control:focus{border-color:var(--pd-blue);box-shadow:0 0 0 3px rgba(51,101,175,.11)}
.payment-field small{display:block;color:#8a94a6;font-size:10px;line-height:1.5;margin-top:6px}
.payment-env{font-size:10px;font-weight:750;letter-spacing:.04em;padding:6px 9px;border-radius:7px;white-space:nowrap}
.payment-env--sandbox{color:#245aa5;background:#edf4ff}
.payment-env--live{color:#946200;background:#fff5df}
.payment-check{display:flex!important;align-items:flex-start;gap:10px;padding:13px;border:1px solid var(--pd-line);border-radius:10px;cursor:pointer}
.payment-check input{margin-top:3px;accent-color:var(--pd-blue)}
.payment-check strong,.payment-check small{display:block}
.payment-check strong{font-size:12px;color:#344054}
.payment-check small{font-size:11px;color:var(--pd-muted);line-height:1.5;margin-top:4px}
.payment-form-footer{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:4px 2px 22px}
.payment-form-footer p{font-size:11px;color:var(--pd-muted);margin:0;line-height:1.5}
.payment-form-footer p i{color:#16845b;margin-right:5px}
.payment-form-footer .btn{min-height:43px;padding:10px 18px;border-radius:9px;white-space:nowrap}
.payment-method-catalog{padding:18px;margin:0 0 20px;background:#f8faff;border:1px solid #e5ecf7;border-radius:12px}
.payment-method-catalog__heading{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:14px}
.payment-method-catalog__heading strong{display:block;font-size:13px;color:var(--pd-ink)}
.payment-method-catalog__heading p{margin:4px 0 0;color:var(--pd-muted);font-size:11px;line-height:1.55}
.payment-catalog-count{flex:none;padding:5px 8px;border-radius:20px;background:#e9f0fb;color:var(--pd-blue);font-size:10px;font-weight:700}
.payment-catalog-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
.payment-catalog-item{display:flex;align-items:center;justify-content:space-between;gap:10px;min-width:0;padding:11px;background:#fff;border:1px solid var(--pd-line);border-radius:10px}
.payment-catalog-item__info{display:flex;align-items:center;gap:9px;min-width:0}
.payment-catalog-item__icon{display:grid;place-items:center;flex:none;width:34px;height:34px;border-radius:9px;background:#edf3fc;color:var(--pd-blue)}
.payment-catalog-item__info strong,.payment-catalog-item__info small{display:block}
.payment-catalog-item__info strong{font-size:12px;color:var(--pd-ink);line-height:1.35}
.payment-catalog-item__info small{margin-top:3px;color:var(--pd-muted);font-size:10px}
.payment-catalog-item form{margin:0;flex:none}
.payment-catalog-item .btn{white-space:nowrap;font-size:10px}
.payment-catalog-empty{display:flex;align-items:center;gap:8px;padding:12px;color:#18794e;font-size:12px;background:#fff;border-radius:9px}
.payment-catalog-note{display:flex;gap:7px;margin:12px 0 0;color:var(--pd-muted);font-size:10px;line-height:1.5}
.payment-catalog-note i{color:var(--pd-blue);flex:none}
.payment-method-list{display:grid;gap:0}
.payment-method-row{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:16px;padding:18px 0;border-top:1px solid var(--pd-line)}
.payment-method-row:first-child{border-top:0;padding-top:0}
.payment-method-row__main{display:grid;gap:6px;min-width:0}
.payment-method-row__main>strong{font-size:14px;line-height:1.4;color:var(--pd-ink)}
.payment-method-row__main>span{font-size:11px;color:var(--pd-muted)}
.payment-routing-form{display:grid;gap:7px;margin-top:5px!important;max-width:440px}
.payment-routing-form__controls{display:flex;align-items:center;gap:8px;min-width:0}
.payment-routing-form select{min-width:0;max-width:250px;min-height:36px;border:1px solid #d9e0ea;border-radius:8px;padding:6px 10px;font-size:12px;color:#344054;background-color:#fff}
.payment-routing-form .btn{min-height:36px;white-space:nowrap;border-radius:8px;font-size:11px;font-weight:650}
.payment-method-row__actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap}
.payment-method-row__actions .btn{white-space:nowrap}
.payment-bank-list{display:grid;gap:10px}
.payment-bank{display:flex;align-items:center;gap:13px;padding:14px;border:1px solid var(--pd-line);border-radius:11px}
.payment-bank__icon{width:40px;height:40px;display:grid;place-items:center;flex:none;border-radius:10px;background:#f2f5fa;color:var(--pd-blue);font-size:18px}
.payment-bank__main{display:grid;gap:3px;flex:1;min-width:0}
.payment-bank__main strong{font-size:13px}
.payment-bank__main span{font-size:11px;color:var(--pd-muted)}
.payment-bank__main code{font-size:12px;color:var(--pd-ink);overflow-wrap:anywhere}
.payment-empty{display:grid;justify-items:center;text-align:center;padding:34px 18px;color:var(--pd-muted)}
.payment-empty>i{font-size:27px;color:var(--pd-blue);margin-bottom:10px}
.payment-empty strong{color:var(--pd-ink);font-size:14px}
.payment-empty p{font-size:12px;line-height:1.6;margin:6px 0 14px}
@media(max-width:900px){.payment-summary{grid-template-columns:1fr 1fr}.payment-method-row{grid-template-columns:1fr;gap:12px}.payment-method-row__actions{justify-content:flex-start}}
@media(max-width:640px){
 .payment-detail__header{align-items:flex-start;flex-direction:column}
 .payment-header-actions{width:100%;justify-content:space-between}
 .payment-provider{align-items:flex-start;gap:12px}
 .payment-provider__icon{width:44px;height:44px;font-size:20px}
 .payment-provider h1{font-size:22px}
 .payment-summary{grid-template-columns:1fr;gap:9px}
 .payment-summary__item{padding:14px 15px}
 .payment-panel{padding:19px 15px;border-radius:12px}
 .payment-form-grid{grid-template-columns:1fr;gap:15px}
 .payment-field--full{grid-column:auto}
 .payment-panel__heading{flex-direction:column;align-items:stretch}
 .payment-form-footer{flex-direction:column;align-items:stretch}
 .payment-form-footer .btn{width:100%}
 .payment-method-catalog{padding:14px}
 .payment-catalog-grid{grid-template-columns:1fr}
 .payment-catalog-item{align-items:center}
 .payment-catalog-item .btn{min-height:36px}
 .payment-routing-form{max-width:none}
 .payment-routing-form__controls{align-items:stretch;flex-direction:column}
 .payment-routing-form select,.payment-routing-form .btn{width:100%;max-width:none;min-height:42px}
 .payment-method-row__actions{justify-content:space-between}
 .payment-method-row__actions form{flex:1}
 .payment-method-row__actions form .btn{width:100%}
 .payment-bank{align-items:flex-start;flex-wrap:wrap;padding:13px}
 .payment-bank .payment-status{margin-left:53px}
}
</style>
@endpush
