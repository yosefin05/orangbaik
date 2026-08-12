@extends('layouts.admin')

@section('page-title', 'Kelola Donasi')

@section('content')

    {{-- ========================================================== --}}
    {{-- STATISTIK                                                   --}}
    {{-- ========================================================== --}}
    <section class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon icon-blue">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <p class="stat-label">Total Donasi</p>
                <h3 class="stat-value text-blue">{{ $donasi->total() }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-gold">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <p class="stat-label">Total Terkumpul</p>
                <h3 class="stat-value text-gold">Rp {{ number_format($donasi->sum('nominal'), 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-green">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <p class="stat-label">Settlement</p>
                <h3 class="stat-value text-green">
                    {{ $donasi->filter(fn($d) => $d->pembayaran && $d->pembayaran->transaction_status == 'settlement')->count() }}
                </h3>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-orange">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <p class="stat-label">Pending</p>
                <h3 class="stat-value text-orange">
                    {{ $donasi->filter(fn($d) => $d->pembayaran && $d->pembayaran->transaction_status == 'pending')->count() }}
                </h3>
            </div>
        </div>

    </section>

    {{-- ========================================================== --}}
    {{-- DATA DONASI                                                 --}}
    {{-- ========================================================== --}}
    <section class="ob-card ob-card-lg mt-4">

        <div class="card-topbar">
            <div>
                <h2>Data Donasi</h2>
                <p class="card-subtitle">
                    Kelola seluruh transaksi donasi yang masuk di platform OrangBaik.id.
                </p>
            </div>

            <div class="card-actions">
                <a href="{{ route('admin.donasi.export') }}" class="btn-primary">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        {{-- Filter --}}
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
                            {{ $campaign->judul }}
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
                        <th>Metode</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($donasi as $item)

                        @php
                            $status = $item->pembayaran->transaction_status ?? 'pending';

                            $badgeClass = match ($status) {
                                'settlement' => 'badge-green',
                                'pending' => 'badge-yellow',
                                'cancel'=> 'badge-red',
                                default => 'badge-gray',
                            };

                            $icon = match ($status) {
                                'settlement' => 'bi-check-circle-fill',
                                'pending' => 'bi-hourglass-split',
                                'cancel'=> 'bi-x-circle-fill',
                                default => 'bi-clock-history',
                            };
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration + ($donasi->currentPage() - 1) * $donasi->perPage() }}</td>

                            <td>
                                <a href="{{ route('admin.campaign.show', $item->campaign_id) }}">
                                    {{ Str::limit($item->campaign->judul ?? '-', 30) }}
                                </a>
                            </td>

                            <td>
                                <p class="cell-title">{{ $item->is_anonim ? 'Anonim' : $item->nama_donatur }}</p>
                                <span class="text-muted-strong">{{ $item->email ?? '-' }}</span>
                            </td>

                            <td>
                                <span class="text-muted-strong">{{ $item->nomor ?? '-' }}</span>
                            </td>

                            <td>
                                <span class="text-muted-strong">
                                    Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                </span>
                            </td>

                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    <i class="bi {{ $icon }}"></i>
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td>{{ $item->pembayaran->payment_type ?? '-' }}</td>

                            <td>
                                {{ $item->created_at->format('d/m/Y H:i') }}
                                @if($item->pembayaran->paid_at)
                                    <br>
                                    <span class="badge badge-green">
                                        <i class="bi bi-check-circle-fill"></i>
                                        {{ \Carbon\Carbon::parse($item->pembayaran->paid_at)->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="action-group">

                                    <a href="{{ route('admin.donasi.show', $item->id) }}" class="action-link link-blue">
                                        <i class="bi bi-eye"></i>
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.donasi.edit', $item->id) }}" class="action-link link-yellow">
                                        <i class="bi bi-pencil"></i>
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.donasi.destroy', $item->id) }}" method="POST" class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus donasi ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="action-link link-red">
                                            <i class="bi bi-trash"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="empty-state">
                                Belum ada data donasi.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="pagination-wrapper">
            {{ $donasi->links() }}
        </div>

    </section>

@endsection