@extends('layouts.admin')

@section('page-title', 'Tambah Payment Channel')

@section('content')

    <section class="page-header">
        <div>
            <h2>Tambah Payment Channel</h2>
            <p>Tambahkan metode pembayaran baru untuk donatur.</p>
        </div>
        <a href="{{ route('admin.payment.channel.index') }}" class="btn btn-outline">
            <i class="bi bi-arrow-left"></i>
            Kembali
        </a>
    </section>

    <div class="form-card">
        <form action="{{ route('admin.payment.channel.store') }}" method="POST" id="channel-form">
            @csrf

            <div class="form-grid">

                {{-- Payment Method Name --}}
                <div class="form-group form-group--full">
                    <label for="name" class="form-label">
                        Nama Metode Pembayaran <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="Contoh: QRIS, GoPay, Bank BCA"
                        required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Payment Gateway --}}
                <div class="form-group">
                    <label for="payment_gateway_id" class="form-label">
                        Payment Gateway <span class="required">*</span>
                    </label>
                    <select
                        id="payment_gateway_id"
                        name="payment_gateway_id"
                        class="form-control @error('payment_gateway_id') is-invalid @enderror"
                        required>
                        <option value="">— Pilih Gateway —</option>
                        @foreach ($gateways as $gateway)
                            <option value="{{ $gateway->id }}"
                                data-code="{{ $gateway->code }}"
                                {{ old('payment_gateway_id') == $gateway->id ? 'selected' : '' }}>
                                {{ $gateway->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_gateway_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Payment Type --}}
                <div class="form-group">
                    <label for="payment_type" class="form-label">
                        Tipe Pembayaran <span class="required">*</span>
                    </label>
                    <select
                        id="payment_type"
                        name="payment_type"
                        class="form-control @error('payment_type') is-invalid @enderror"
                        required>
                        <option value="">— Pilih Tipe —</option>
                        <option value="instant"  {{ old('payment_type') === 'instant' ? 'selected' : '' }}>
                            Instant (QRIS, E-Wallet)
                        </option>
                        <option value="va"       {{ old('payment_type') === 'va' ? 'selected' : '' }}>
                            Virtual Account
                        </option>
                        <option value="transfer" {{ old('payment_type') === 'transfer' ? 'selected' : '' }}>
                            Transfer Manual
                        </option>
                    </select>
                    @error('payment_type')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Channel Code --}}
                <div class="form-group">
                    <label for="channel_code" class="form-label">
                        Channel Code <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="channel_code"
                        name="channel_code"
                        class="form-control @error('channel_code') is-invalid @enderror"
                        value="{{ old('channel_code') }}"
                        placeholder="Contoh: qris, gopay, bca, bni"
                        required>
                    <small class="form-hint">Kode harus sesuai dengan channel yang tersedia di provider.</small>
                    @error('channel_code')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Account Name --}}
                <div class="form-group" id="account-name-group">
                    <label for="account_name" class="form-label">
                        Nama Rekening / Akun
                    </label>
                    <input
                        type="text"
                        id="account_name"
                        name="account_name"
                        class="form-control @error('account_name') is-invalid @enderror"
                        value="{{ old('account_name') }}"
                        placeholder="Contoh: OrangBaik">
                    <small class="form-hint">Opsional. Diisi untuk Virtual Account dan Transfer Manual.</small>
                    @error('account_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Account Number --}}
                <div class="form-group" id="account-number-group">
                    <label for="account_number" class="form-label">
                        Nomor Rekening
                    </label>
                    <input
                        type="text"
                        id="account_number"
                        name="account_number"
                        class="form-control @error('account_number') is-invalid @enderror"
                        value="{{ old('account_number') }}"
                        placeholder="Contoh: 1234567890">
                    <small class="form-hint">Opsional. Diisi hanya untuk Transfer Manual.</small>
                    @error('account_number')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Sort Order --}}
                <div class="form-group">
                    <label for="sort_order" class="form-label">Urutan Tampilan</label>
                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        class="form-control @error('sort_order') is-invalid @enderror"
                        value="{{ old('sort_order', 0) }}"
                        min="0"
                        placeholder="0">
                    <small class="form-hint">Semakin kecil, semakin atas posisinya. Kosongkan untuk otomatis.</small>
                    @error('sort_order')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div class="toggle-field">
                        <label class="toggle-switch">
                            <input
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                value="1"
                                {{ old('is_active', 1) ? 'checked' : '' }}>
                            <span class="toggle-track"></span>
                        </label>
                        <span class="toggle-label" id="status-label">Aktif</span>
                    </div>
                </div>

            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <a href="{{ route('admin.payment.channel.index') }}" class="btn btn-outline">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" id="btn-simpan">
                    <i class="bi bi-check-lg"></i>
                    Simpan Channel
                </button>
            </div>
        </form>
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
    }
    .page-header p {
        margin: 0;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .form-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.75rem;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; }
    }
    .form-group { display: flex; flex-direction: column; gap: 0.375rem; }
    .form-group--full { grid-column: 1 / -1; }
    .form-label { font-size: 0.875rem; font-weight: 600; color: #374151; }
    .required { color: #dc2626; }
    .form-control {
        padding: 0.625rem 0.875rem;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #111827;
        transition: border-color 0.15s;
        background: #fff;
        width: 100%;
        box-sizing: border-box;
    }
    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    .form-control.is-invalid { border-color: #dc2626; }
    .invalid-feedback { color: #dc2626; font-size: 0.8125rem; }
    .form-hint { color: #6b7280; font-size: 0.8rem; }

    .toggle-field { display: flex; align-items: center; gap: 0.75rem; height: 38px; }
    .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-track {
        position: absolute; inset: 0;
        background: #d1d5db;
        border-radius: 24px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .toggle-track::before {
        content: '';
        position: absolute;
        width: 18px; height: 18px;
        left: 3px; top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.2s;
    }
    input:checked + .toggle-track { background: #16a34a; }
    input:checked + .toggle-track::before { transform: translateX(20px); }
    .toggle-label { font-size: 0.875rem; font-weight: 500; color: #374151; }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding-top: 1.25rem;
        border-top: 1px solid #f3f4f6;
    }
</style>
@endpush

@push('scripts')
<script>
    // Update label toggle status
    const toggle = document.getElementById('is_active');
    const label  = document.getElementById('status-label');
    if (toggle && label) {
        toggle.addEventListener('change', function () {
            label.textContent = this.checked ? 'Aktif' : 'Nonaktif';
        });
    }
</script>
@endpush
