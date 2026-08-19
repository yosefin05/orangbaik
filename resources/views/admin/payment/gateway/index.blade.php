@extends('layouts.admin')

@section('page-title', 'Payment Gateway')

@section('content')

    {{-- ================================================================ --}}
    {{-- HEADER + BREADCRUMB                                              --}}
    {{-- ================================================================ --}}
    <section class="page-header">
        <div>
            <h2>Payment Gateway</h2>
            <p>Kelola provider pembayaran yang digunakan OrangBaik.</p>
        </div>
        <a href="{{ route('admin.payment.channel.index') }}" class="btn btn-primary">
            <i class="bi bi-diagram-3"></i>
            Kelola Channel
        </a>
    </section>

    {{-- ================================================================ --}}
    {{-- ALERT MESSAGES                                                   --}}
    {{-- ================================================================ --}}
    @if (session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            {{ session('warning') }}
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- GATEWAY CARDS                                                    --}}
    {{-- ================================================================ --}}
    <section class="gateway-grid">

        @foreach ($gateways as $gateway)
            <div class="gateway-card {{ $gateway->is_active ? '' : 'gateway-card--inactive' }}">

                <div class="gateway-card__header">
                    <div class="gateway-icon">
                        @if ($gateway->code === 'midtrans')
                            <i class="bi bi-lightning-charge-fill"></i>
                        @elseif ($gateway->code === 'flip')
                            <i class="bi bi-arrow-left-right"></i>
                        @else
                            <i class="bi bi-bank"></i>
                        @endif
                    </div>
                    <div>
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

                    {{-- Keterangan konfigurasi --}}
                    <div class="gateway-config-note">
                        @if ($gateway->code === 'midtrans')
                            <i class="bi bi-shield-check text-success"></i>
                            <small>API Key tersedia di <code>.env</code></small>
                        @elseif ($gateway->code === 'flip')
                            <i class="bi bi-exclamation-circle text-warning"></i>
                            <small>API Key belum dikonfigurasi</small>
                        @else
                            <i class="bi bi-info-circle text-blue"></i>
                            <small>Tidak memerlukan API Key</small>
                        @endif
                    </div>
                </div>

                <div class="gateway-card__footer">
                    <form action="{{ route('admin.payment.gateway.toggle', $gateway) }}"
                        method="POST"
                        onsubmit="return confirm('{{ $gateway->is_active ? 'Nonaktifkan' : 'Aktifkan' }} gateway {{ $gateway->name }}?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="btn {{ $gateway->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} btn-sm">
                            <i class="bi bi-{{ $gateway->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                            {{ $gateway->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>

                    <a href="{{ route('admin.payment.channel.index') }}?gateway={{ $gateway->id }}"
                        class="btn btn-outline btn-sm">
                        <i class="bi bi-list-ul"></i>
                        Lihat Channel
                    </a>
                </div>

            </div>
        @endforeach

    </section>

    {{-- ================================================================ --}}
    {{-- INFO NOTE                                                        --}}
    {{-- ================================================================ --}}
    <div class="info-box">
        <i class="bi bi-shield-lock"></i>
        <div>
            <strong>Keamanan Credential</strong>
            <p>API Key payment gateway tersimpan di file <code>.env</code> dan tidak pernah disimpan di database maupun ditampilkan di halaman ini. Untuk mengubah API Key, edit file <code>.env</code> di server.</p>
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
    }
    .page-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 0.25rem;
        color: var(--text-primary, #1a1a1a);
    }
    .page-header p {
        margin: 0;
        color: var(--text-secondary, #6b7280);
        font-size: 0.875rem;
    }

    .gateway-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .gateway-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .gateway-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        border-color: #d1d5db;
    }
    .gateway-card--inactive {
        opacity: 0.65;
    }

    .gateway-card__header {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 1.25rem 1.25rem 0.875rem;
        border-bottom: 1px solid #f3f4f6;
    }
    .gateway-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #f0f4ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #3b82f6;
        flex-shrink: 0;
    }
    .gateway-card__name {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 0.125rem;
        color: #111827;
    }
    .gateway-card__code {
        font-size: 0.75rem;
        color: #9ca3af;
        font-family: monospace;
    }

    .gateway-card__body {
        padding: 1rem 1.25rem;
    }
    .gateway-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    .gateway-stat__label {
        font-size: 0.8125rem;
        color: #6b7280;
    }
    .gateway-stat__value {
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
    }
    .gateway-config-note {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f9fafb;
        border-radius: 8px;
        padding: 0.625rem 0.875rem;
        font-size: 0.8125rem;
    }
    .gateway-config-note code {
        background: #e5e7eb;
        padding: 1px 4px;
        border-radius: 4px;
        font-size: 0.75rem;
    }

    .gateway-card__footer {
        display: flex;
        gap: 0.75rem;
        padding: 0.875rem 1.25rem;
        border-top: 1px solid #f3f4f6;
        background: #fafafa;
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
        font-size: 1.25rem;
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

    /* Reuse global badges */
    .ms-auto { margin-left: auto; }
    .text-success { color: #16a34a !important; }
    .text-warning { color: #d97706 !important; }
    .text-blue { color: #3b82f6 !important; }
</style>
@endpush
