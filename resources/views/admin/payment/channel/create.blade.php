@extends('layouts.admin')

@section('page-title', 'Tambah Payment Channel')

@section('content')

    <section class="page-header">
        <div>
            <h2>Tambah Payment Channel</h2>
            <p>Tambahkan metode pembayaran baru untuk donatur.</p>
        </div>
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
