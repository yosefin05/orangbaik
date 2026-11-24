@extends('layouts.admin')

@section('page-title', 'Rekening Transfer Manual')

@section('content')
<div class="manual-accounts">
    <a href="{{ route('admin.payment.gateway.index') }}" class="manual-back"><i class="bi bi-arrow-left"></i> Kembali ke Pengaturan Pembayaran</a>

    <header class="manual-header">
        <div>
            <span class="manual-eyebrow">TRANSFER MANUAL</span>
            <h1>Rekening tujuan donasi</h1>
            <p>Kelola rekening milik OrangBaik yang akan ditampilkan kepada donatur saat memilih transfer manual.</p>
        </div>
        <a href="{{ route('admin.payment.channel.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah rekening</a>
    </header>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('warning')) <div class="alert alert-warning">{{ session('warning') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger"><strong>Periksa kembali data rekening.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="manual-notice"><i class="bi bi-info-circle"></i><span>Halaman ini hanya untuk rekening transfer manual. Midtrans dan Flip menggunakan kredensial serta metode pembayaran yang diatur melalui akun masing-masing provider.</span></div>

    <form action="{{ route('admin.payment.channel.batch') }}" method="POST" id="manualAccountForm">
        @csrf
        <div id="deletedIdsContainer"></div>
        <div class="manual-account-list">
            @forelse ($channels as $index => $channel)
                <article class="manual-account" data-account>
                    <input type="hidden" name="channels[{{ $index }}][id]" value="{{ $channel->id }}">
                    <input type="hidden" name="channels[{{ $index }}][channel_code]" value="{{ $channel->channel_code }}">
                    <div class="manual-account__top">
                        <div class="manual-bank-icon"><i class="bi bi-bank"></i></div>
                        <div class="manual-account__identity">
                            <strong>{{ $channel->name }}</strong>
                            <span>{{ $channel->is_active ? 'Ditampilkan kepada donatur' : 'Tidak ditampilkan kepada donatur' }}</span>
                        </div>
                        <label class="manual-toggle">
                            <input type="hidden" name="channels[{{ $index }}][is_active]" value="0">
                            <input type="checkbox" name="channels[{{ $index }}][is_active]" value="1" {{ $channel->is_active ? 'checked' : '' }}>
                            <span>Aktif</span>
                        </label>
                    </div>
                    <div class="manual-fields">
                        <div>
                            <label>Nama bank / rekening</label>
                            <input class="form-control" name="channels[{{ $index }}][name]" value="{{ $channel->name }}" required maxlength="100">
                        </div>
                        <div>
                            <label>Nama pemilik rekening</label>
                            <input class="form-control" name="channels[{{ $index }}][account_name]" value="{{ $channel->account_name }}" required maxlength="100">
                        </div>
                        <div>
                            <label>Nomor rekening</label>
                            <input class="form-control" name="channels[{{ $index }}][account_number]" value="{{ $channel->account_number }}" required maxlength="50">
                        </div>
                    </div>
                    <div class="manual-account__actions">
                        <a href="{{ route('admin.payment.channel.edit', $channel) }}">Edit detail</a>
                        <button type="button" class="manual-remove" data-remove>Hapus rekening</button>
                    </div>
                </article>
            @empty
                <div class="manual-empty">
                    <i class="bi bi-bank"></i>
                    <strong>Belum ada rekening transfer manual</strong>
                    <p>Tambahkan rekening resmi OrangBaik agar donatur dapat melakukan transfer langsung.</p>
                </div>
            @endforelse
        </div>

        @if ($channels->isNotEmpty())
            <div class="manual-savebar">
                <span>Perubahan status dan detail rekening belum disimpan.</span>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Simpan perubahan</button>
            </div>
        @endif
    </form>
</div>
@endsection

@push('styles')
<style>
.manual-accounts{max-width:1000px;color:#172b4d}.manual-back{display:inline-flex;gap:8px;align-items:center;color:#667085;text-decoration:none;font-size:13px;font-weight:600;margin-bottom:20px}.manual-back:hover{color:#3365af}
.manual-header{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:22px}.manual-eyebrow{font-size:10px;letter-spacing:.12em;font-weight:800;color:#8290a5}.manual-header h1{font-size:25px;letter-spacing:-.04em;margin:5px 0;color:#172b4d}.manual-header p{font-size:13px;color:#667085;margin:0;max-width:600px;line-height:1.6}
.manual-notice{display:flex;gap:10px;align-items:flex-start;padding:13px 15px;border:1px solid #dbe7f7;background:#f5f8fd;border-radius:10px;color:#52647d;font-size:12px;line-height:1.6;margin-bottom:16px}.manual-notice i{color:#3365af;font-size:15px}
.manual-account-list{display:grid;gap:12px}.manual-account{background:#fff;border:1px solid #e7ebf2;border-radius:13px;padding:18px 20px}.manual-account__top{display:flex;align-items:center;gap:12px;margin-bottom:17px}.manual-bank-icon{width:40px;height:40px;display:grid;place-items:center;border-radius:10px;background:#edf3fc;color:#3365af;font-size:18px}.manual-account__identity{display:grid;gap:4px;flex:1;min-width:0}.manual-account__identity strong{font-size:14px}.manual-account__identity span{font-size:11px;color:#8994a6}.manual-toggle{display:flex;align-items:center;gap:7px;font-size:12px;font-weight:650;color:#52647d;white-space:nowrap}.manual-toggle input{accent-color:#3365af;width:16px;height:16px}
.manual-fields{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}.manual-fields label{display:block;font-size:11px;font-weight:650;color:#526075;margin-bottom:6px}.manual-fields .form-control{height:41px;border:1px solid #dce2eb;border-radius:8px;font-size:12px;box-shadow:none}.manual-fields .form-control:focus{border-color:#3365af;box-shadow:0 0 0 3px #3365af18}.manual-account__actions{display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:12px;border-top:1px solid #eff1f5}.manual-account__actions a,.manual-remove{font-size:11px;font-weight:650;text-decoration:none}.manual-account__actions a{color:#3365af}.manual-remove{border:0;background:transparent;color:#c03939;padding:0}.manual-savebar{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:16px 2px}.manual-savebar>span{font-size:11px;color:#8994a6}.manual-empty{display:grid;justify-items:center;text-align:center;padding:45px 20px;background:#fff;border:1px dashed #d5dce7;border-radius:12px;color:#667085}.manual-empty i{font-size:28px;color:#3365af;margin-bottom:12px}.manual-empty strong{font-size:14px;color:#172b4d}.manual-empty p{font-size:12px;margin:6px 0 0}
@media(max-width:700px){.manual-header{align-items:stretch;flex-direction:column}.manual-header .btn{width:100%}.manual-fields{grid-template-columns:1fr}.manual-account{padding:15px}.manual-account__top{flex-wrap:wrap}.manual-savebar{align-items:stretch;flex-direction:column}.manual-savebar .btn{width:100%}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('manualAccountForm');
    const deleted = document.getElementById('deletedIdsContainer');
    form?.querySelectorAll('[data-remove]').forEach((button) => {
        button.addEventListener('click', () => {
            const card = button.closest('[data-account]');
            const id = card?.querySelector('input[name$="[id]"]')?.value;
            if (id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleted_ids[]';
                input.value = id;
                deleted.appendChild(input);
            }
            card?.remove();
        });
    });
});
</script>
@endpush
