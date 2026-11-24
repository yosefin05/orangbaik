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
            <small>{{ $isManual ? 'Tidak menggunakan API provider.' : 'Staging dan local selalu menggunakan Sandbox.' }}</small>
        </div>
        <div class="payment-summary__item">
            <span>Status provider</span>
            <strong>{{ $gateway->is_active ? 'Siap digunakan' : 'Tidak menerima pembayaran' }}</strong>
            <small>Status aktif/nonaktif berlaku untuk seluruh channel provider ini.</small>
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
                        <label class="payment-check">
                            <input type="checkbox" name="is_production" value="1" {{ old('is_production', data_get($gateway->config, 'is_production')) ? 'checked' : '' }}>
                            <span>
                                <strong>Gunakan kredensial Production saat aplikasi berjalan di Production</strong>
                                <small>Staging, local, dan testing tetap dipaksa memakai Sandbox. Jangan aktifkan sebelum pengujian Sandbox selesai.</small>
                            </span>
                        </label>
                    </div>
                </div>
            </section>

            @if ($isMidtrans)
                @foreach (['sandbox' => 'Sandbox', 'production' => 'Production'] as $environment => $label)
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
                                    <input id="{{ $key }}" name="{{ $key }}" type="password" class="form-control" autocomplete="new-password" placeholder="{{ $credentialFlags[$key] ? 'Tersimpan aman — isi hanya untuk mengganti' : 'Masukkan ' . $labelText }}" value="">
                                    <small>{{ $credentialFlags[$key] ? 'Kredensial sudah tersimpan dan tidak ditampilkan kembali.' : 'Belum ada kredensial tersimpan.' }}</small>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @else
                @foreach (['sandbox' => 'Sandbox', 'production' => 'Production'] as $environment => $label)
                    <section class="payment-panel">
                        <div class="payment-panel__heading">
                            <div>
                                <span class="payment-eyebrow">{{ strtoupper($environment) }} CREDENTIALS</span>
                                <h2>{{ $label }}</h2>
                                <p>{{ $environment === 'sandbox' ? 'Key pengujian Flip. Gunakan akses Sandbox resmi dari Flip.' : 'Key Live Flip untuk transaksi Production.' }}</p>
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
                                    <input id="{{ $key }}" name="{{ $key }}" type="password" class="form-control" autocomplete="new-password" placeholder="{{ $credentialFlags[$key] ? 'Tersimpan aman — isi hanya untuk mengganti' : 'Masukkan ' . $labelText }}" value="">
                                    <small>{{ $credentialFlags[$key] ? 'Kredensial sudah tersimpan dan tidak ditampilkan kembali.' : 'Belum ada kredensial tersimpan.' }}</small>
                                </div>
                            @endforeach
                        </div>
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

@push('styles')
<style>
.payment-detail{--pd-blue:#3365af;--pd-ink:#172b4d;--pd-muted:#667085;--pd-line:#e7ebf2;max-width:1180px;margin:0 auto;color:var(--pd-ink)}
.payment-back{display:inline-flex;align-items:center;gap:8px;color:var(--pd-muted);text-decoration:none;font-size:13px;font-weight:600;margin-bottom:22px}
.payment-back:hover{color:var(--pd-blue)}
.payment-detail__header{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:22px}
.payment-provider{display:flex;align-items:center;gap:16px;min-width:0}
.payment-provider__icon{width:54px;height:54px;display:grid;place-items:center;flex:none;border-radius:15px;background:#edf3fc;color:var(--pd-blue);font-size:24px}
.payment-eyebrow{display:block;color:#8490a3;font-size:10px;font-weight:750;letter-spacing:.12em;margin-bottom:6px}
.payment-provider h1{font-size:26px;line-height:1.2;letter-spacing:-.04em;margin:0;color:var(--pd-ink)}
.payment-provider p,.payment-panel__heading p,.payment-next-step p{color:var(--pd-muted);font-size:13px;line-height:1.65;margin:6px 0 0}
.payment-header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}.payment-header-actions form{margin:0}.payment-security-note{display:flex;align-items:flex-start;gap:8px;color:var(--pd-muted);font-size:11px;line-height:1.55;margin:0 2px 16px}.payment-security-note i{color:var(--pd-blue);margin-top:1px}.payment-status{display:inline-flex;align-items:center;gap:7px;padding:7px 10px;border-radius:999px;font-size:11px;font-weight:700;white-space:nowrap}
.payment-status>span{width:7px;height:7px;border-radius:50%;background:currentColor}
.payment-status.is-active{background:#eaf7ef;color:#18794e}.payment-status.is-inactive{background:#f1f3f6;color:#667085}
.payment-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}
.payment-summary__item{padding:17px 18px;background:#fff;border:1px solid var(--pd-line);border-radius:13px}
.payment-summary__item span,.payment-summary__item small{display:block;color:var(--pd-muted);font-size:11px}
.payment-summary__item strong{display:block;margin:7px 0 4px;font-size:15px;color:var(--pd-ink)}
.payment-panel{background:#fff;border:1px solid var(--pd-line);border-radius:15px;padding:24px;margin-bottom:16px;box-shadow:0 3px 14px rgba(20,40,80,.025)}
.payment-panel__heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px}
.payment-panel__heading h2,.payment-next-step h2{font-size:17px;font-weight:700;letter-spacing:-.02em;margin:0;color:var(--pd-ink)}
.payment-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px 20px}
.payment-field{min-width:0}.payment-field--full{grid-column:1/-1}
.payment-field>label{display:block;font-size:12px;font-weight:650;color:#344054;margin-bottom:7px}
.payment-field>label span{font-size:10px;color:#98a2b3;font-weight:500;margin-left:4px}
.payment-field .form-control{min-height:44px;border:1px solid #d9e0ea;border-radius:9px;padding:10px 12px;font-size:13px;box-shadow:none}
.payment-field .form-control:focus{border-color:var(--pd-blue);box-shadow:0 0 0 3px rgba(51,101,175,.11)}
.payment-field small{display:block;color:#8a94a6;font-size:10px;line-height:1.5;margin-top:6px}
.payment-env{font-size:10px;font-weight:750;letter-spacing:.04em;padding:6px 9px;border-radius:7px;white-space:nowrap}
.payment-env--sandbox{color:#245aa5;background:#edf4ff}.payment-env--live{color:#946200;background:#fff5df}
.payment-check{display:flex!important;align-items:flex-start;gap:10px;padding:13px;border:1px solid var(--pd-line);border-radius:10px;cursor:pointer}
.payment-check input{margin-top:3px;accent-color:var(--pd-blue)}
.payment-check strong,.payment-check small{display:block}.payment-check strong{font-size:12px;color:#344054}.payment-check small{font-size:11px;color:var(--pd-muted);line-height:1.5;margin-top:4px}
.payment-form-footer{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:4px 2px 22px}
.payment-form-footer p{font-size:11px;color:var(--pd-muted);margin:0;line-height:1.5}
.payment-form-footer p i{color:#16845b;margin-right:5px}
.payment-form-footer .btn{min-height:43px;padding:10px 18px;border-radius:9px;white-space:nowrap}
.payment-next-step{display:flex;align-items:center;justify-content:space-between;gap:18px}
.payment-bank-list{display:grid;gap:10px}
.payment-bank{display:flex;align-items:center;gap:13px;padding:14px;border:1px solid var(--pd-line);border-radius:11px}
.payment-bank__icon{width:40px;height:40px;display:grid;place-items:center;border-radius:10px;background:#f2f5fa;color:var(--pd-blue);font-size:18px}
.payment-bank__main{display:grid;gap:3px;flex:1;min-width:0}.payment-bank__main strong{font-size:13px}.payment-bank__main span{font-size:11px;color:var(--pd-muted)}.payment-bank__main code{font-size:12px;color:var(--pd-ink)}
.payment-empty{display:grid;justify-items:center;text-align:center;padding:30px 15px;color:var(--pd-muted)}.payment-empty>i{font-size:27px;color:var(--pd-blue);margin-bottom:10px}.payment-empty strong{color:var(--pd-ink);font-size:14px}.payment-empty p{font-size:12px;margin:6px 0 14px}
@media(max-width:700px){.payment-detail__header{align-items:flex-start}.payment-header-actions{align-items:flex-end;flex-direction:column}.payment-provider{align-items:flex-start;gap:12px}.payment-provider__icon{width:44px;height:44px;font-size:20px}.payment-provider h1{font-size:22px}.payment-summary{grid-template-columns:1fr}.payment-panel{padding:18px 15px}.payment-form-grid{grid-template-columns:1fr}.payment-field--full{grid-column:auto}.payment-panel__heading,.payment-next-step{flex-direction:column;align-items:stretch}.payment-form-footer{flex-direction:column;align-items:stretch}.payment-form-footer .btn{width:100%}.payment-bank{align-items:flex-start;flex-wrap:wrap}.payment-bank .payment-status{margin-left:53px}}
</style>
@endpush
