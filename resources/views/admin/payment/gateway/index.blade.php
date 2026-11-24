@extends('layouts.admin')

@section('page-title', 'Payment Gateway')

@section('content')

    {{-- ================================================================ --}}
    {{-- HEADER + BREADCRUMB                                              --}}
    {{-- ================================================================ --}}
    <section class="gateway-page-header">
        <div>
            <h2>Payment Gateway</h2>
            <p>Kelola integrasi pembayaran dan metode pembayaran dari satu tempat. Setiap provider memiliki pengaturan yang terpisah.</p>
        </div>
        <div class="gateway-header-actions">
            <a href="{{ route('admin.payment.channel.index') }}" class="btn btn-primary">
                <i class="bi bi-grid-1x2"></i>
                Rekening transfer manual
            </a>
        </div>
    </section>

    {{-- ================================================================ --}}
    {{-- ALERT MESSAGES                                                   --}}
    {{-- ================================================================ --}}
    @if (session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('warning') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-x-circle-fill"></i>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-x-circle-fill"></i>
            <ul style="margin: 0; padding-left: 1rem;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- GATEWAY CARDS                                                    --}}
    {{-- ================================================================ --}}
    <section class="gateway-grid">

        @foreach ($gateways as $gateway)
            @php
                $isConfigured = $gateway->isConfigured();
            @endphp
            <div class="gateway-card {{ $gateway->is_active ? '' : 'gateway-card--inactive' }}">

                <div class="gateway-card__header">
                    <div class="gateway-icon">
                        @if ($gateway->code === 'midtrans')
                            <i class="bi bi-lightning-charge-fill"></i>
                        @elseif ($gateway->code === 'flip')
                            <i class="bi bi-arrow-left-right"></i>
                        @elseif ($gateway->code === 'manual')
                            <i class="bi bi-bank"></i>
                        @else
                            <i class="bi bi-credit-card-2-front"></i>
                        @endif
                    </div>
                    <div style="flex:1;">
                        <h3 class="gateway-card__name">{{ $gateway->name }}</h3>
                        <span class="gateway-card__code">{{ $gateway->code }}</span>
                    </div>

                    <span class="badge {{ $gateway->is_active ? 'badge-success' : 'badge-secondary' }} ms-auto">
                        {{ $gateway->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div class="gateway-card__body">
                    <div class="gateway-stat">
                        <span class="gateway-stat__label">{{ $gateway->code === 'manual' ? 'Rekening terdaftar' : 'Metode terdaftar' }}</span>
                        <span class="gateway-stat__value">{{ $gateway->channels_count }}</span>
                    </div>

                    {{-- Keterangan status konfigurasi dinamis --}}
                    <div class="gateway-config-note">
                        @if ($gateway->code === 'manual')
                            <i class="bi bi-info-circle text-blue"></i>
                            <small class="text-blue fw-semibold">Transfer Manual Yayasan</small>
                        @elseif ($isConfigured)
                            <i class="bi bi-shield-check text-success"></i>
                            <small class="text-success fw-semibold">Kredensial tersedia</small>
                        @else
                            <i class="bi bi-exclamation-circle text-warning"></i>
                            <small class="text-warning fw-semibold">Kredensial wajib belum lengkap</small>
                        @endif
                    </div>

                    @if ($gateway->description)
                        <p class="gateway-desc">{{ $gateway->description }}</p>
                    @endif
                </div>

                <div class="gateway-card__footer">
                    <a href="{{ route('admin.payment.gateway.show', $gateway) }}"
                        class="btn btn-primary btn-sm">
                        <i class="bi bi-sliders"></i>
                        Kelola konfigurasi
                    </a>

                    @if (!in_array($gateway->code, ['midtrans', 'flip', 'manual']) && $gateway->channels_count === 0)
                        <form action="{{ route('admin.payment.gateway.destroy', $gateway) }}" method="POST"
                            onsubmit="return confirm('Hapus gateway {{ $gateway->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Gateway">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>

            </div>
        @endforeach

    </section>

    {{-- ================================================================ --}}
    {{-- INFO BOX: KEAMANAN & ARSITEKTUR                                  --}}
    {{-- ================================================================ --}}
    <div class="gateway-info-box">
        <i class="bi bi-shield-lock-fill"></i>
        <div>
            <strong>Konfigurasi Fleksibel & Modular (Driver Pattern)</strong>
            <p>Atur kredensial melalui panel ini. Nilai rahasia tidak ditampilkan kembali setelah disimpan. Status kredensial bukan bukti bahwa transaksi sudah berhasil; lakukan pengujian Sandbox sebelum mengaktifkan pembayaran.</p>
        </div>
    </div>

@endsection

@push('styles')
<style>
.gateway-page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin:0 0 24px}
.gateway-page-header h2{margin:0 0 6px;color:#172b4d;font-size:25px;font-weight:750;letter-spacing:-.035em}
.gateway-page-header p{max-width:660px;margin:0;color:#667085;font-size:13px;line-height:1.65}
.gateway-header-actions{display:flex;align-items:center;gap:10px}
.gateway-header-actions .btn{min-height:42px;display:inline-flex;align-items:center;gap:8px;border-radius:9px;padding:10px 15px;font-size:12px;font-weight:650;white-space:nowrap}
.gateway-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,300px),1fr));gap:16px;margin-bottom:20px}
.gateway-card{min-width:0;display:flex;flex-direction:column;background:#fff;border:1px solid #e6eaf1;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(23,43,77,.035);transition:border-color .18s ease,box-shadow .18s ease}
.gateway-card:hover{border-color:#cbd8eb;box-shadow:0 8px 22px rgba(23,43,77,.07)}
.gateway-card--inactive{background:#fbfcfe}
.gateway-card__header{display:flex;align-items:center;gap:12px;padding:19px 20px;border-bottom:1px solid #f0f2f6}
.gateway-icon{width:44px;height:44px;display:grid;place-items:center;flex:none;border-radius:12px;background:#edf3fc;color:#3365af;font-size:20px}
.gateway-card__name{margin:0 0 3px;color:#172b4d;font-size:15px;font-weight:750;letter-spacing:-.015em}
.gateway-card__code{color:#98a2b3;font-size:10px;font-weight:650;letter-spacing:.04em;text-transform:uppercase}
.gateway-card__header .badge{margin-left:auto;padding:6px 9px;border-radius:999px;font-size:10px;font-weight:700}
.gateway-card__body{flex:1;padding:18px 20px}
.gateway-stat{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:15px}
.gateway-stat__label{color:#667085;font-size:12px}
.gateway-stat__value{color:#172b4d;font-size:22px;font-weight:750;line-height:1}
.gateway-config-note{display:flex;align-items:center;gap:8px;padding:10px 11px;border:1px solid #e8edf4;border-radius:9px;background:#f8fafd;font-size:11px;line-height:1.45}
.gateway-config-note i{font-size:14px}
.gateway-desc{margin:12px 0 0;color:#7b8798;font-size:11px;line-height:1.6}
.gateway-card__footer{display:flex;align-items:center;gap:9px;padding:14px 20px;border-top:1px solid #f0f2f6;background:#fcfdff}
.gateway-card__footer .btn{min-height:37px;display:inline-flex;align-items:center;justify-content:center;gap:7px;border-radius:8px;padding:8px 12px;font-size:11px;font-weight:650}
.gateway-card__footer form{margin:0}
.gateway-info-box{display:flex;align-items:flex-start;gap:12px;padding:15px 17px;border:1px solid #dce7f7;border-radius:11px;background:#f6f9fe;color:#52647d}
.gateway-info-box>i{flex:none;margin-top:1px;color:#3365af;font-size:18px}
.gateway-info-box strong{display:block;margin:0 0 4px;color:#263f64;font-size:12px;font-weight:750}
.gateway-info-box p{margin:0;color:#667b98;font-size:11px;line-height:1.65}
@media(max-width:640px){
 .gateway-page-header{flex-direction:column;align-items:stretch;margin-bottom:18px}
 .gateway-page-header h2{font-size:22px}
 .gateway-header-actions,.gateway-header-actions .btn{width:100%}
 .gateway-grid{grid-template-columns:1fr;gap:12px}
 .gateway-card__header{padding:16px}
 .gateway-card__body{padding:15px 16px}
 .gateway-card__footer{padding:12px 16px}
 .gateway-card__footer>a{flex:1}
 .gateway-info-box{padding:13px}
}
</style>
@endpush

