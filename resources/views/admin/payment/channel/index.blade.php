@extends('layouts.admin')

@section('page-title', 'Bank Account / Payment Channel')

@section('content')

    <div class="channel-admin-wrapper">

        <div class="channel-page-header">
            <div>
                <h1 class="channel-title">Bank Account</h1>
                <p class="channel-subtitle">Konfigurasi metode pembayaran, kode channel, payment gateway/provider, dan tipe pembayaran.</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.payment.gateway.index') }}" class="btn-top-gateway">
                    <i class="bi bi-gear-wide-connected"></i> Gateway Status
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert-box alert-success">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="alert-box alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-box alert-danger">
                <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.payment.channel.batch') }}" method="POST" id="channelBatchForm">
            @csrf

            <div id="deletedIdsContainer"></div>

            <div class="channel-repeater-container" id="channelList">

                @forelse ($channels as $index => $channel)
                    <div class="channel-row-item" data-id="{{ $channel->id }}">
                        <input type="hidden" name="channels[{{ $index }}][id]" value="{{ $channel->id }}" class="channel-id-input">

                        {{-- 1. Nama Payment Method / Bank --}}
                        <div class="field-wrap field-name">
                            <select name="channels[{{ $index }}][name]" class="form-input custom-select">
                                @php
                                    $commonNames = [
                                        'Gopay', 'ShopeePay', 'LinkAja', 'QRIS', 'DANA', 'OVO',
                                        'Bank BCA', 'Bank Syariah (BSI)', 'Bank BNI', 'Bank Mandiri', 'Bank BRI',
                                        'Bank Mega Syariah', 'Bank Jatim Syariah', 'Permata Bank', 'Bank Muamalat',
                                        'Bank CIMB Niaga', 'Bank Artha Graha', 'Bank Danamon', 'Flip Transfer'
                                    ];
                                @endphp
                                @foreach ($commonNames as $cName)
                                    <option value="{{ $cName }}" {{ strcasecmp($channel->name, $cName) === 0 ? 'selected' : '' }}>
                                        {{ $cName }}
                                    </option>
                                @endforeach
                                @if (!in_array($channel->name, $commonNames))
                                    <option value="{{ $channel->name }}" selected>{{ $channel->name }}</option>
                                @endif
                            </select>
                        </div>

                        {{-- 2. Code / No Rekening --}}
                        <div class="field-wrap field-code">
                            <input
                                type="text"
                                name="channels[{{ $index }}][channel_code]"
                                value="{{ $channel->channel_code }}"
                                class="form-input"
                                placeholder="Code / No Rekening"
                                required
                            >
                        </div>

                        {{-- 3. Provider / Payment Gateway --}}
                        <div class="field-wrap field-provider">
                            <select name="channels[{{ $index }}][payment_gateway_id]" class="form-input custom-select">
                                @foreach ($gateways as $gateway)
                                    <option value="{{ $gateway->id }}" {{ $channel->payment_gateway_id == $gateway->id ? 'selected' : '' }}>
                                        {{ $gateway->code === 'manual' ? ($channel->account_name ?: "Dompet Al Qur'an") : $gateway->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 4. Payment Type (Instant / Transfer / VA) --}}
                        <div class="field-wrap field-type">
                            <select name="channels[{{ $index }}][payment_type]" class="form-input custom-select">
                                <option value="instant" {{ $channel->payment_type === 'instant' ? 'selected' : '' }}>Instant</option>
                                <option value="transfer" {{ $channel->payment_type === 'transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="va" {{ $channel->payment_type === 'va' ? 'selected' : '' }}>VA</option>
                            </select>
                        </div>

                        {{-- 5. Drag Handle --}}
                        <div class="drag-handle-wrap" title="Drag untuk mengubah urutan">
                            <svg viewBox="0 0 24 24" class="drag-icon" width="22" height="22">
                                <path fill="#5b9bd5" d="M10 9h4V6h3l-5-5-5 5h3v3zm-1 1H6V7l-5 5 5 5v-3h3v-4zm14 2l-5-5v3h-3v4h3v3l5-5zm-9 3h-4v3H7l5 5 5-5h-3v-3z"/>
                            </svg>
                        </div>

                        {{-- 6. Delete Row Button --}}
                        <div class="delete-btn-wrap">
                            <button type="button" class="btn-row-delete" title="Hapus channel">
                                <span>&minus;</span>
                            </button>
                        </div>
                    </div>
                @empty
                    {{-- Row default jika kosong --}}
                @endforelse

            </div>

            {{-- Bottom Actions --}}
            <div class="bottom-actions-row">
                <button type="button" id="btnAddBank" class="btn-add-bank">
                    + Add Bank
                </button>

                <div class="submit-wrap">
                    <span id="saveStatusIndicator" class="save-status-indicator"></span>
                    <button type="submit" class="btn-save-all" id="btnSaveAll">
                        <i class="bi bi-check-lg"></i> Simpan Konfigurasi
                    </button>
                </div>
            </div>

        </form>

    </div>

@endsection

@push('styles')
<style>
    .channel-admin-wrapper {
        max-width: 900px;
        margin: 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .channel-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .channel-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #212529;
        margin: 0;
    }
    .channel-subtitle {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0.25rem 0 0;
    }
    .btn-top-gateway {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f8fafc;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        color: #374151;
        text-decoration: none;
        font-size: 0.8125rem;
        font-weight: 600;
    }
    .btn-top-gateway:hover {
        background: #e2e8f0;
    }

    .alert-box {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1.25rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Repeater List */
    .channel-repeater-container {
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        margin-bottom: 1.25rem;
    }

    .channel-row-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        background: #ffffff;
        padding: 0.25rem 0;
        transition: background 0.15s ease;
    }
    .channel-row-item.dragging {
        opacity: 0.45;
        background: #f1f5f9;
    }
    .channel-row-item.drag-over {
        border-top: 2px solid #3b82f6;
    }

    .field-wrap {
        flex: 1;
    }
    .field-name {
        flex: 1.25;
        min-width: 140px;
    }
    .field-code {
        flex: 1;
        min-width: 110px;
    }
    .field-provider {
        flex: 1.25;
        min-width: 130px;
    }
    .field-type {
        flex: 0.9;
        min-width: 95px;
    }

    .form-input {
        width: 100%;
        box-sizing: border-box;
        height: 38px;
        padding: 0.375rem 0.625rem;
        font-size: 0.875rem;
        color: #334155;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .form-input:focus {
        border-color: #5b9bd5;
        box-shadow: 0 0 0 2px rgba(91, 155, 213, 0.2);
    }

    .custom-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.6rem center;
        background-size: 11px 9px;
        padding-right: 1.75rem;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        cursor: pointer;
    }

    /* Drag Handle */
    .drag-handle-wrap {
        width: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: grab;
        user-select: none;
        flex-shrink: 0;
    }
    .drag-handle-wrap:active {
        cursor: grabbing;
    }
    .drag-icon {
        opacity: 0.85;
    }
    .drag-handle-wrap:hover .drag-icon {
        opacity: 1;
    }

    /* Delete Button (Red box with minus) */
    .delete-btn-wrap {
        width: 38px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-row-delete {
        width: 38px;
        height: 38px;
        background-color: #ea4335;
        border: 1px solid #d93025;
        border-radius: 4px;
        color: #ffffff;
        font-size: 1.35rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        transition: background-color 0.15s ease;
    }
    .btn-row-delete:hover {
        background-color: #d93025;
    }

    /* Bottom Actions */
    .bottom-actions-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    .btn-add-bank {
        background: #ffffff;
        border: 1.5px solid #4a6cf7;
        color: #4a6cf7;
        padding: 0.45rem 1.125rem;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-add-bank:hover {
        background: #4a6cf7;
        color: #ffffff;
    }

    .submit-wrap {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .save-status-indicator {
        font-size: 0.8125rem;
        font-weight: 500;
    }
    .save-status-indicator.saving { color: #f59e0b; }
    .save-status-indicator.saved  { color: #16a34a; }
    .save-status-indicator.error  { color: #dc2626; }

    .btn-save-all {
        background: #16a34a;
        color: #ffffff;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    .btn-save-all:hover {
        background: #15803d;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const channelList = document.getElementById('channelList');
    const btnAddBank  = document.getElementById('btnAddBank');
    const deletedContainer = document.getElementById('deletedIdsContainer');

    const gatewaysOptions = `
        @foreach ($gateways as $g)
            <option value="{{ $g->id }}">{{ $g->name }}</option>
        @endforeach
    `;

    const commonNames = [
        'Gopay', 'ShopeePay', 'LinkAja', 'QRIS', 'DANA', 'OVO',
        'Bank BCA', 'Bank Syariah (BSI)', 'Bank BNI', 'Bank Mandiri', 'Bank BRI',
        'Bank Mega Syariah', 'Bank Jatim Syariah', 'Permata Bank', 'Bank Muamalat',
        'Bank CIMB Niaga', 'Bank Artha Graha', 'Bank Danamon', 'Flip Transfer'
    ];

    let namesOptions = '';
    commonNames.forEach(n => {
        namesOptions += `<option value="${n}">${n}</option>`;
    });

    // 1. Tambah Row Baru (+ Add Bank)
    btnAddBank.addEventListener('click', function () {
        const nextIndex = channelList.querySelectorAll('.channel-row-item').length;

        const row = document.createElement('div');
        row.className = 'channel-row-item';
        row.innerHTML = `
            <input type="hidden" name="channels[${nextIndex}][id]" value="" class="channel-id-input">

            <div class="field-wrap field-name">
                <select name="channels[${nextIndex}][name]" class="form-input custom-select">
                    ${namesOptions}
                </select>
            </div>

            <div class="field-wrap field-code">
                <input type="text" name="channels[${nextIndex}][channel_code]" value="" class="form-input" placeholder="Code / No Rekening" required>
            </div>

            <div class="field-wrap field-provider">
                <select name="channels[${nextIndex}][payment_gateway_id]" class="form-input custom-select">
                    ${gatewaysOptions}
                </select>
            </div>

            <div class="field-wrap field-type">
                <select name="channels[${nextIndex}][payment_type]" class="form-input custom-select">
                    <option value="instant">Instant</option>
                    <option value="transfer">Transfer</option>
                    <option value="va">VA</option>
                </select>
            </div>

            <div class="drag-handle-wrap" title="Drag untuk mengubah urutan">
                <svg viewBox="0 0 24 24" class="drag-icon" width="22" height="22">
                    <path fill="#5b9bd5" d="M10 9h4V6h3l-5-5-5 5h3v3zm-1 1H6V7l-5 5 5 5v-3h3v-4zm14 2l-5-5v3h-3v4h3v3l5-5zm-9 3h-4v3H7l5 5 5-5h-3v-3z"/>
                </svg>
            </div>

            <div class="delete-btn-wrap">
                <button type="button" class="btn-row-delete" title="Hapus channel">
                    <span>&minus;</span>
                </button>
            </div>
        `;

        channelList.appendChild(row);
        initRowEvents(row);
        reindexRows();
        row.querySelector('.field-code input').focus();
    });

    // 2. Event Handler untuk setiap baris (Delete & Drag)
    function initRowEvents(row) {
        // Delete button
        const deleteBtn = row.querySelector('.btn-row-delete');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                const idInput = row.querySelector('.channel-id-input');
                if (idInput && idInput.value) {
                    const hiddenDel = document.createElement('input');
                    hiddenDel.type = 'hidden';
                    hiddenDel.name = 'deleted_ids[]';
                    hiddenDel.value = idInput.value;
                    deletedContainer.appendChild(hiddenDel);
                }
                row.remove();
                reindexRows();
            });
        }

        // Drag and Drop
        row.draggable = true;

        row.addEventListener('dragstart', function (e) {
            dragSrc = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        row.addEventListener('dragend', function () {
            this.classList.remove('dragging');
            channelList.querySelectorAll('.channel-row-item').forEach(r => r.classList.remove('drag-over'));
        });

        row.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            channelList.querySelectorAll('.channel-row-item').forEach(r => r.classList.remove('drag-over'));
            if (this !== dragSrc) this.classList.add('drag-over');
        });

        row.addEventListener('drop', function (e) {
            e.preventDefault();
            if (this === dragSrc) return;

            const rows = [...channelList.querySelectorAll('.channel-row-item')];
            const srcIdx  = rows.indexOf(dragSrc);
            const destIdx = rows.indexOf(this);

            if (srcIdx < destIdx) {
                this.after(dragSrc);
            } else {
                this.before(dragSrc);
            }

            reindexRows();
        });
    }

    let dragSrc = null;
    channelList.querySelectorAll('.channel-row-item').forEach(initRowEvents);

    // 3. Re-index nama array inputs
    function reindexRows() {
        channelList.querySelectorAll('.channel-row-item').forEach((row, idx) => {
            row.querySelectorAll('input, select').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/channels\[\d+\]/, `channels[${idx}]`);
                }
            });
        });
    }
});
</script>
@endpush
