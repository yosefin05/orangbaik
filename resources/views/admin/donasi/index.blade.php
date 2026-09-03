@extends('layouts.admin')

@section('page-title', 'Kelola Donasi')

@section('content')

    {{-- ========================================================== --}}
    {{-- STATISTIK --}}
    {{-- ========================================================== --}}
    <section class="donasi-stats-grid">

    <div class="donasi-stat-card">
        <div class="donasi-stat-icon icon-blue">
            <i class="bi bi-wallet2"></i>
        </div>
        <div>
            <p class="donasi-stat-label">Total Donasi</p>
            <h3 class="donasi-stat-value text-blue">{{ number_format($totalDonasiCount ?? $donasi->total(), 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="donasi-stat-card">
        <div class="donasi-stat-icon icon-gold">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div>
            <p class="donasi-stat-label">Total Terkumpul (Settlement)</p>
            <h3 class="donasi-stat-value text-gold">Rp {{ number_format($totalTerkumpul ?? 0, 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="donasi-stat-card">
        <div class="donasi-stat-icon icon-green">
            <i class="bi bi-check-circle"></i>
        </div>
        <div>
            <p class="donasi-stat-label">Berhasil (Settlement)</p>
            <h3 class="donasi-stat-value text-green">{{ number_format($settlementCount ?? 0, 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="donasi-stat-card">
        <div class="donasi-stat-icon icon-orange">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
            <p class="donasi-stat-label">Menunggu (Pending)</p>
            <h3 class="donasi-stat-value text-orange">{{ number_format($pendingCount ?? 0, 0, ',', '.') }}</h3>
        </div>
    </div>

    <div class="donasi-stat-card">
        <div class="donasi-stat-icon icon-red">
            <i class="bi bi-calendar-x"></i>
        </div>
        <div>
            <p class="donasi-stat-label">Kadaluarsa (Expired)</p>
            <h3 class="donasi-stat-value text-red">{{ number_format($expireCount ?? 0, 0, ',', '.') }}</h3>
        </div>
    </div>

</section>

    {{-- ========================================================== --}}
    {{-- DATA DONASI --}}
    {{-- ========================================================== --}}
    <section class="ob-card ob-card-lg mt-4">

        <div class="card-topbar">
            <div>
                <h2>Data Transaksi Donasi</h2>
                <p class="card-subtitle">
                    Kelola dan pantau seluruh transaksi donasi masuk di platform OrangBaik.id.
                </p>
            </div>

            <div class="card-actions">
                <a href="{{ route('admin.donasi.export', request()->query()) }}" class="btn-primary"
                    title="Export data donasi sesuai filter ke format CSV">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form action="{{ route('admin.donasi.index') }}" method="GET" class="filter-bar">
            <div class="filter-field">
                <label>Cari Donatur</label>
                <input type="text" name="search" placeholder="Nama / Email / No HP" value="{{ request('search') }}">
            </div>

            <div class="filter-field">
                <label>Campaign</label>
                <select name="campaign_id">
                    <option value="">Semua Campaign</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>
                            {{ Str::limit($campaign->judul, 40) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label>Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="filter-field">
                <label>Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-search"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.donasi.index') }}" class="action-link link-blue">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                </a>
            </div>
        </form>

        <div class="table-wrapper">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Campaign</th>
                        <th>Donatur</th>
                        <th>Nomor HP</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Metode / Channel</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($donasi as $item)

                        @php
                            $pembayaran = $item->pembayaran;
                            $status = $pembayaran->transaction_status ?? 'pending';

                            $badgeClass = match ($status) {
                                'settlement' => 'badge-green',
                                'pending' => 'badge-yellow',
                                'failed' => 'badge-red',
                                'expired' => 'badge-gray',
                                default => 'badge-gray',
                            };

                            $icon = match ($status) {
                                'settlement' => 'bi-check-circle-fill',
                                'pending' => 'bi-hourglass-split',
                                'failed' => 'bi-x-circle-fill',
                                'expired' => 'bi-clock-history',
                                default => 'bi-clock-history',
                            };

                            $channelName = $pembayaran?->paymentChannel?->name
                                ?? ($pembayaran?->payment_type ? ucfirst($pembayaran->payment_type) : '-');
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration + ($donasi->currentPage() - 1) * $donasi->perPage() }}</td>

                            <td>
                                @if($item->campaign)
                                    <a href="{{ route('admin.campaign.show', $item->campaign_id) }}" class="fw-semibold">
                                        {{ Str::limit($item->campaign->judul, 35) }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <p class="cell-title mb-0">
                                    {{ $item->is_anonim ? 'Hamba Allah (Anonim)' : ($item->nama_donatur ?: '-') }}</p>
                                <span class="text-muted">{{ $item->email ?? '-' }}</span>
                            </td>

                            <td>
                                <span class="text-muted-strong">{{ $item->no_hp ?? '-' }}</span>
                            </td>

                            <td>
                                <span class="fw-bold text-dark">
                                    Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                </span>
                            </td>

                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    <i class="bi {{ $icon }}"></i>
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge badge-blue">
                                    {{ $channelName }}
                                </span>
                            </td>

                            <td>
                                <span class="d-block">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                @if($pembayaran && $pembayaran->paid_at)
                                    <span class="badge badge-green mt-1" title="Waktu pembayaran berhasil">
                                        <i class="bi bi-check2"></i>
                                        {{ \Carbon\Carbon::parse($pembayaran->paid_at)->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="action-group">

                                    <a href="{{ route('admin.donasi.show', $item->id) }}" class="action-link link-blue"
                                        title="Lihat detail donasi & bukti transfer">
                                        <i class="bi bi-eye"></i>
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.donasi.edit', $item->id) }}" class="action-link link-yellow"
                                        title="Edit status donasi">
                                        <i class="bi bi-pencil"></i>
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.donasi.destroy', $item->id) }}" method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus donasi #{{ $item->id }}?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="action-link link-red" title="Hapus donasi">
                                            <i class="bi bi-trash"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="empty-state text-center py-5">
                                <div class="empty-state-content">
                                    <i class="bi bi-inbox text-muted" style="font-size: 2.5rem;"></i>
                                    <p class="mt-2 text-muted fw-semibold">Belum ada data donasi yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination Footer --}}
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $donasi->firstItem() ?? 0 }}</strong> -
                <strong>{{ $donasi->lastItem() ?? 0 }}</strong> dari <strong>{{ $donasi->total() }}</strong> donasi
            </div>
            <div class="pagination-links">
                {{ $donasi->links() }}
            </div>
        </div>

    </section>

@endsection