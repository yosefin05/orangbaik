@extends('layouts.admin')

@section('page-title', 'Payment Gateway')

@section('content')

    {{-- ================================================================ --}}
    {{-- HEADER + BREADCRUMB                                              --}}
    {{-- ================================================================ --}}
    <section class="page-header">
        <div>
            <h2>Payment Gateway</h2>
            <p>Kelola provider pembayaran, atur API Key langsung dari panel, dan tambahkan gateway baru secara fleksibel.</p>
        </div>
        <div class="header-actions">
            <button type="button" class="btn btn-success" onclick="openAddGatewayModal()">
                <i class="bi bi-plus-lg"></i>
                Tambah Gateway Baru
            </button>
            <a href="{{ route('admin.payment.channel.index') }}" class="btn btn-primary">
                <i class="bi bi-diagram-3"></i>
                Kelola Channel
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
                            <small class="text-success fw-semibold">API Key Terkonfigurasi & Siap</small>
                        @else
                            <i class="bi bi-exclamation-circle text-warning"></i>
                            <small class="text-warning fw-semibold">API Key Belum Diisi</small>
                        @endif
                    </div>

                    @if ($gateway->description)
                        <p class="gateway-desc">{{ $gateway->description }}</p>
                    @endif
                </div>

                <div class="gateway-card__footer">
                    @if ($gateway->code !== 'manual')
                        <button type="button" 
                            class="btn btn-outline-primary btn-sm"
                            onclick='openEditConfigModal(@json($gateway))'
                            title="Atur API Key & Pengaturan Gateway">
                            <i class="bi bi-gear-fill"></i>
                            Pengaturan API
                        </button>
                    @endif

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

                    <a href="{{ route('admin.payment.channel.index') }}"
                        class="btn btn-outline btn-sm">
                        <i class="bi bi-list-ul"></i>
                        Channel
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
            <p>Anda dapat mengedit API Key langsung dari modal pengaturan di atas atau melalui file <code>.env</code>. Webhook universal untuk semua gateway tersedia di: <code>POST /payment/{gateway}/webhook</code>.</p>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL TAMBAH GATEWAY BARU                                        --}}
    {{-- ================================================================ --}}
    <div id="addGatewayModal" class="custom-modal-backdrop" style="display:none;">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Payment Gateway Baru</h3>
                <button type="button" class="btn-close-modal" onclick="closeAddGatewayModal()">&times;</button>
            </div>

            <form action="{{ route('admin.payment.gateway.store') }}" method="POST">
                @csrf
                <div class="custom-modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Nama Gateway <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Xendit, Doku, Faspay, Mayar" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Kode Unik Gateway <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: xendit, doku, mayar (huruf kecil tanpa spasi)" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Driver Type</label>
                        <select name="driver" class="form-control">
                            <option value="generic">Generic REST API (Standar)</option>
                            <option value="custom">Custom Driver</option>
                        </select>
                        <small class="text-muted">Gunakan 'Generic REST API' untuk gateway berbasis endpoint callback standar.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">API Key / Secret Key</label>
                        <input type="text" name="api_key" class="form-control" placeholder="Masukkan API Key / Secret Key">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Webhook Token / Signature Key</label>
                        <input type="text" name="webhook_token" class="form-control" placeholder="Token verifikasi webhook (opsional)">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">API Endpoint URL (Opsional)</label>
                        <input type="url" name="endpoint_url" class="form-control" placeholder="https://api.gateway.com/v1/payment">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Keterangan / Deskripsi</label>
                        <input type="text" name="description" class="form-control" placeholder="Keterangan singkat gateway">
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_production" value="1" id="addProdCheck" class="form-check-input">
                        <label for="addProdCheck" class="form-check-label">Mode Production (Live)</label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" id="addActiveCheck" class="form-check-input" checked>
                        <label for="addActiveCheck" class="form-check-label">Langsung Aktifkan Gateway</label>
                    </div>
                </div>

                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddGatewayModal()">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Simpan Gateway</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL EDIT PENGATURAN API GATEWAY                                --}}
    {{-- ================================================================ --}}
    <div id="editConfigModal" class="custom-modal-backdrop" style="display:none;">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3 class="modal-title"><i class="bi bi-gear-fill"></i> Pengaturan API: <span id="modalGatewayName"></span></h3>
                <button type="button" class="btn-close-modal" onclick="closeEditConfigModal()">&times;</button>
            </div>

            <form id="editConfigForm" method="POST">
                @csrf
                @method('PUT')
                <div class="custom-modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Nama Gateway</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>

                    {{-- Fields khusus Midtrans --}}
                    <div id="midtransFields" style="display:none;">
                        <div class="form-group mb-3">
                            <label class="form-label">Midtrans Server Key <span class="text-danger">*</span></label>
                            <input type="text" name="server_key" id="editServerKey" class="form-control" placeholder="SB-Mid-server-xxxx">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Midtrans Client Key</label>
                            <input type="text" name="client_key" id="editClientKey" class="form-control" placeholder="SB-Mid-client-xxxx">
                        </div>
                    </div>

                    {{-- Fields khusus Flip --}}
                    <div id="flipFields" style="display:none;">
                        <div class="form-group mb-3">
                            <label class="form-label">Flip Secret API Key <span class="text-danger">*</span></label>
                            <input type="text" name="api_key" id="editFlipApiKey" class="form-control" placeholder="Masukkan Secret Key Flip">
                            <small class="text-muted">Didapatkan dari Dashboard Big Flip > API Integration.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Flip Webhook Token (X-CALLBACK-TOKEN)</label>
                            <input type="text" name="webhook_token" id="editFlipWebhookToken" class="form-control" placeholder="Token verifikasi webhook Flip">
                        </div>
                    </div>

                    {{-- Fields umum / custom gateway --}}
                    <div id="genericFields" style="display:none;">
                        <div class="form-group mb-3">
                            <label class="form-label">API Key / Secret Key</label>
                            <input type="text" name="api_key" id="editGenericApiKey" class="form-control" placeholder="API Secret Key">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Webhook Token / Signature Key</label>
                            <input type="text" name="webhook_token" id="editGenericWebhookToken" class="form-control" placeholder="Token Webhook">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">API Endpoint URL</label>
                            <input type="url" name="endpoint_url" id="editEndpointUrl" class="form-control" placeholder="https://api.gateway.com/v1/charge">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" id="editDesc" class="form-control">
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_production" value="1" id="editProdCheck" class="form-check-input">
                        <label for="editProdCheck" class="form-check-label">Mode Production (Live)</label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" id="editActiveCheck" class="form-check-input">
                        <label for="editActiveCheck" class="form-check-label">Status Gateway Aktif</label>
                    </div>
                </div>

                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditConfigModal()">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                </div>
            </form>
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

    /* Modal Backdrop & Popup */
    .custom-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    .custom-modal {
        background: #ffffff;
        border-radius: 14px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        animation: modalFadeIn 0.2s ease;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    .custom-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.125rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .modal-title {
        font-size: 1.125rem;
        font-weight: 700;
        margin: 0;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #94a3b8;
        cursor: pointer;
        line-height: 1;
    }
    .btn-close-modal:hover { color: #0f172a; }

    .custom-modal-body {
        padding: 1.5rem;
        max-height: 75vh;
        overflow-y: auto;
    }
    .custom-modal-footer {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .form-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.375rem;
        display: block;
    }
    .form-control {
        width: 100%;
        box-sizing: border-box;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        color: #1e293b;
    }
    .form-control:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }
</style>
@endpush

@push('scripts')
<script>
function openAddGatewayModal() {
    document.getElementById('addGatewayModal').style.display = 'flex';
}
function closeAddGatewayModal() {
    document.getElementById('addGatewayModal').style.display = 'none';
}

function openEditConfigModal(gateway) {
    document.getElementById('modalGatewayName').textContent = gateway.name;
    document.getElementById('editConfigForm').action = `/admin/payment/gateway/${gateway.id}`;
    
    document.getElementById('editName').value = gateway.name || '';
    document.getElementById('editDesc').value = gateway.description || '';
    document.getElementById('editActiveCheck').checked = !!gateway.is_active;

    const config = gateway.config || {};
    document.getElementById('editProdCheck').checked = !!config.is_production;

    // Toggle fields based on gateway code
    const midtransFields = document.getElementById('midtransFields');
    const flipFields     = document.getElementById('flipFields');
    const genericFields  = document.getElementById('genericFields');

    midtransFields.style.display = 'none';
    flipFields.style.display     = 'none';
    genericFields.style.display  = 'none';

    if (gateway.code === 'midtrans') {
        midtransFields.style.display = 'block';
        document.getElementById('editServerKey').value = config.server_key || '';
        document.getElementById('editClientKey').value = config.client_key || '';
    } else if (gateway.code === 'flip') {
        flipFields.style.display = 'block';
        document.getElementById('editFlipApiKey').value = config.api_key || '';
        document.getElementById('editFlipWebhookToken').value = config.webhook_token || '';
    } else {
        genericFields.style.display = 'block';
        document.getElementById('editGenericApiKey').value = config.api_key || config.server_key || '';
        document.getElementById('editGenericWebhookToken').value = config.webhook_token || '';
        document.getElementById('editEndpointUrl').value = config.endpoint_url || '';
    }

    document.getElementById('editConfigModal').style.display = 'flex';
}

function closeEditConfigModal() {
    document.getElementById('editConfigModal').style.display = 'none';
}

// Close modal on backdrop click
window.onclick = function(e) {
    const addM  = document.getElementById('addGatewayModal');
    const editM = document.getElementById('editConfigModal');
    if (e.target === addM) closeAddGatewayModal();
    if (e.target === editM) closeEditConfigModal();
};
</script>
@endpush
