@extends('layouts.admin')

@section('page-title', 'Payment Gateway')

@section('content')

    {{-- ================================================================ --}}
    {{-- HEADER + BREADCRUMB                                              --}}
    {{-- ================================================================ --}}
    <section class="page-header">
        <div>
            <h2>Payment Gateway</h2>
            <p>Kelola integrasi pembayaran dan metode pembayaran dari satu tempat. Setiap provider memiliki pengaturan yang terpisah.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.payment.channel.index') }}" class="btn btn-primary">
                <i class="bi bi-grid-1x2"></i>
                Metode pembayaran
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
                        <span class="gateway-stat__label">Total Channel</span>
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
    <div class="info-box">
        <i class="bi bi-shield-lock-fill"></i>
        <div>
            <strong>Konfigurasi Fleksibel & Modular (Driver Pattern)</strong>
            <p>Atur kredensial melalui panel ini. Nilai rahasia tidak ditampilkan kembali setelah disimpan. Status kredensial bukan bukti bahwa transaksi sudah berhasil; lakukan pengujian Sandbox sebelum mengaktifkan pembayaran.</p>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .header-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    .page-header h2 {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0 0 0.25rem;
        color: #111827;
    }
    .page-header p {
        margin: 0;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .gateway-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .gateway-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .gateway-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }
    .gateway-card--inactive {
        opacity: 0.65;
        background: #f8fafc;
    }

    .gateway-card__header {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .gateway-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #2563eb;
        flex-shrink: 0;
    }
    .gateway-card__name {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0 0 0.125rem;
        color: #1e293b;
    }
    .gateway-card__code {
        font-size: 0.75rem;
        color: #64748b;
        font-family: monospace;
    }

    .gateway-card__body {
        padding: 1.25rem;
        flex: 1;
    }
    .gateway-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.875rem;
    }
    .gateway-stat__label {
        font-size: 0.8125rem;
        color: #64748b;
    }
    .gateway-stat__value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }
    .gateway-config-note {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.625rem 0.875rem;
        font-size: 0.8125rem;
    }
    .gateway-desc {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 0.75rem 0 0;
        line-height: 1.4;
    }

    .gateway-card__footer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.25rem;
        border-top: 1px solid #f1f5f9;
        background: #fafafa;
        flex-wrap: wrap;
    }

    .info-box {
        display: flex;
        gap: 0.875rem;
        align-items: flex-start;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        font-size: 0.875rem;
        color: #1e40af;
        margin-top: 1rem;
    }
    .info-box i {
        font-size: 1.35rem;
        margin-top: 1px;
        flex-shrink: 0;
    }
    .info-box strong {
        display: block;
        margin-bottom: 0.25rem;
    }
    .info-box p {
        margin: 0;
        color: #1d4ed8;
    }
    .info-box code {
        background: #dbeafe;
        padding: 1px 5px;
        border-radius: 4px;
        font-size: 0.8125rem;
    }

</style>
@endpush

