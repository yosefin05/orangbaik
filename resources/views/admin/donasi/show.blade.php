@extends('layouts.admin')

@section('page-title', 'Detail Donasi #' . $donasi->id)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Detail Donasi #{{ $donasi->id }}</h1>
        <div style="display:flex;gap:0.5rem;">
            <a href="{{ route('admin.donasi.edit', $donasi->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.donasi.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mb-3">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- Informasi Donasi --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Donasi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">ID Donasi</th>
                            <td>#{{ $donasi->id }}</td>
                        </tr>
                        <tr>
                            <th>Campaign</th>
                            <td>
                                <a href="{{ route('admin.campaign.show', $donasi->campaign_id) }}">
                                    {{ $donasi->campaign->judul ?? '-' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Donatur</th>
                            <td>
                                @if($donasi->is_anonim)
                                    <span class="badge bg-secondary">Anonim</span>
                                @else
                                    <strong>{{ $donasi->nama_donatur }}</strong>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $donasi->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>No HP</th>
                            <td>{{ $donasi->no_hp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Nominal</th>
                            <td>
                                <h4 class="text-success mb-0">Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</h4>
                            </td>
                        </tr>
                        <tr>
                            <th>Pesan Doa</th>
                            <td>{{ $donasi->pesan_doa ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Donasi</th>
                            <td>{{ $donasi->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>User Akun</th>
                            <td>
                                @if($donasi->user)
                                    {{ $donasi->user->name }} ({{ $donasi->user->email }})
                                @else
                                    <span class="text-muted">Tamu (Tidak login)</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Informasi Pembayaran --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    @if($donasi->pembayaran)
                        <table class="table table-bordered">
                            <tr>
                                <th width="35%">Order ID</th>
                                <td><code>{{ $donasi->pembayaran->order_id }}</code></td>
                            </tr>
                            <tr>
                                <th>Status Pembayaran</th>
                                <td>
                                    @php
                                        $status = $donasi->pembayaran->transaction_status;
                                        $badgeClass = match($status) {
                                            'settlement' => 'success',
                                            'pending' => 'warning',
                                            'expire', 'expired' => 'secondary',
                                            'cancel', 'deny', 'failed', 'failure' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }} fs-6">
                                        {{ $donasi->pembayaran->status_label ?? ucfirst($status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Channel</th>
                                <td>
                                    @if ($donasi->pembayaran->paymentChannel)
                                        <strong>{{ $donasi->pembayaran->paymentChannel->name }}</strong>
                                        <span class="badge bg-light text-dark border ms-1">
                                            {{ $donasi->pembayaran->paymentChannel->payment_type_label }}
                                        </span>
                                    @else
                                        {{ $donasi->pembayaran->payment_type ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Gateway</th>
                                <td>
                                    @if ($donasi->pembayaran->paymentChannel?->gateway)
                                        <span class="badge bg-primary">
                                            {{ $donasi->pembayaran->paymentChannel->gateway->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Transaction ID / Ref</th>
                                <td><code>{{ $donasi->pembayaran->transaction_id ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <th>Tanggal Bayar</th>
                                <td>
                                    @if($donasi->pembayaran->paid_at)
                                        <span class="text-success font-weight-bold">
                                            <i class="bi bi-check-circle"></i>
                                            {{ $donasi->pembayaran->paid_at->format('d/m/Y H:i:s') }}
                                        </span>
                                    @else
                                        <span class="text-muted">Belum dibayar</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($donasi->pembayaran->rejection_reason)
                                <tr>
                                    <th class="text-danger">Alasan Penolakan</th>
                                    <td class="text-danger">{{ $donasi->pembayaran->rejection_reason }}</td>
                                </tr>
                            @endif
                        </table>

                        {{-- Bukti Transfer & Approval untuk Transfer Manual --}}
                        @if ($donasi->pembayaran->isManualTransfer() || $donasi->pembayaran->bukti_transfer)
                            <div class="mt-3 p-3 bg-light rounded border">
                                <h6 class="font-weight-bold mb-2">
                                    <i class="bi bi-receipt"></i> Bukti Transfer Manual
                                </h6>

                                @if ($donasi->pembayaran->bukti_transfer)
                                    <div class="mb-3">
                                        <a href="{{ asset('storage/' . $donasi->pembayaran->bukti_transfer) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $donasi->pembayaran->bukti_transfer) }}"
                                                alt="Bukti Transfer"
                                                class="img-fluid rounded border"
                                                style="max-height: 220px; object-fit: contain; background: #fff;">
                                        </a>
                                        <div class="mt-1">
                                            <small class="text-muted">Klik gambar untuk melihat ukuran penuh</small>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info py-2 px-3 mb-3">
                                        <i class="bi bi-info-circle"></i> Donatur belum mengunggah bukti transfer.
                                    </div>
                                @endif

                                {{-- Tombol Verifikasi / Tolak --}}
                                @if ($donasi->pembayaran->isPending())
                                    <div class="d-flex gap-2 mt-2">
                                        <form action="{{ route('admin.donasi.approve-manual', $donasi->id) }}" method="POST"
                                            onsubmit="return confirm('Verifikasi dan terima pembayaran donasi Rp {{ number_format($donasi->nominal, 0, ',', '.') }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check-circle-fill"></i> Terima (Approve)
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRejectManual">
                                            <i class="bi bi-x-circle"></i> Tolak Pembayaran
                                        </button>
                                    </div>

                                    {{-- Modal Tolak --}}
                                    <div class="modal fade" id="modalRejectManual" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.donasi.reject-manual', $donasi->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Tolak Pembayaran Manual</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="rejection_reason" class="form-label">Alasan Penolakan</label>
                                                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" placeholder="Contoh: Bukti transfer buram / nominal tidak sesuai / dana belum masuk." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Tolak Pembayaran</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Data pembayaran tidak ditemukan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection