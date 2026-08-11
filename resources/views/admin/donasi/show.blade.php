@extends('layouts.admin')

@section('title', 'Detail Donasi #' . $donasi->id)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Detail Donasi #{{ $donasi->id }}</h1>
        <div>
            <a href="{{ route('admin.donasi.edit', $donasi->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.donasi.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Informasi Donasi --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Donasi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">ID Donasi</th>
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
                                <h4 class="text-success">Rp {{ number_format($donasi->nominal, 0, ',', '.') }}</h4>
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
                            <th>User</th>
                            <td>
                                @if($donasi->user)
                                    {{ $donasi->user->name }} ({{ $donasi->user->email }})
                                @else
                                    <span class="text-muted">Tidak login</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Informasi Pembayaran --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    @if($donasi->pembayaran)
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Order ID</th>
                                <td><code>{{ $donasi->pembayaran->order_id }}</code></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @php
                                        $status = $donasi->pembayaran->transaction_status;
                                        $badgeClass = match($status) {
                                            'settlement' => 'success',
                                            'pending' => 'warning',
                                            'expire' => 'secondary',
                                            'cancel' => 'danger',
                                            'deny' => 'danger',
                                            'failure' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }} fs-6">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Metode Pembayaran</th>
                                <td>{{ $donasi->pembayaran->payment_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Transaction ID</th>
                                <td><code>{{ $donasi->pembayaran->transaction_id ?? '-' }}</code></td>
                            </tr>
                            <tr>
                                <th>Snap Token</th>
                                <td>
                                    @if($donasi->pembayaran->snap_token)
                                        <code class="text-wrap">{{ $donasi->pembayaran->snap_token }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Bayar</th>
                                <td>
                                    @if($donasi->pembayaran->paid_at)
                                        {{ $donasi->pembayaran->paid_at->format('d/m/Y H:i:s') }}
                                    @else
                                        <span class="text-muted">Belum dibayar</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
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