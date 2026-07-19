@extends('layouts.admin')

@section('page-title', 'Detail Campaign')

@section('content')

    {{-- Header Campaign --}}
    <section class="ob-card ob-card-lg profile-card">

        @if($campaign->thumbnail)
            <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}" class="current-thumbnail">
        @else
            <div class="current-thumbnail table-avatar-placeholder">
                <i class="bi bi-image"></i>
            </div>
        @endif

        <div class="profile-info">
            <h2>{{ $campaign->judul }}</h2>

            <p class="profile-type">
                {{ $campaign->kategori->nama_kategori ?? 'Kategori tidak ditemukan' }}
            </p>
            @php
                $hariIni = now();
                $tanggalBerakhir = \Carbon\Carbon::parse($campaign->tanggal_berakhir);
            @endphp

            @if($hariIni->lte($tanggalBerakhir))
                <span class="badge badge-green">
                    Aktif
                </span>
            @else
                <span class="badge badge-red">
                    Berakhir
                </span>
            @endif
        </div>

    </section>

    {{-- Emergency Approval Section --}}
    @if(in_array($campaign->campaign_type, ['emergency', 'sustainable']))
        <section class="ob-card ob-card-lg"
            style="border-left: 4px solid {{ $campaign->campaign_type == 'emergency' ? '#dc3545' : '#28a745' }};">
            <div class="card-topbar">
                <div>
                    <h2>
                        @if($campaign->campaign_type == 'emergency')
                            <span class="badge badge-red">🔥 Darurat</span>
                        @else
                            <span class="badge badge-green">♻️ Berkelanjutan</span>
                        @endif
                        Approval Status
                    </h2>
                    <p class="card-subtitle">
                        Status persetujuan untuk campaign
                        {{ $campaign->campaign_type == 'emergency' ? 'darurat' : 'berkelanjutan' }}.
                    </p>
                </div>
            </div>

            <table class="data-table data-table-kv">
                <tbody>
                    <tr>
                        <th>Tipe Campaign</th>
                        <td>
                            @if($campaign->campaign_type == 'emergency')
                                <span class="badge badge-red">Darurat</span>
                            @else
                                <span class="badge badge-green">Berkelanjutan</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Status Approval</th>
                        <td>
                            @if($campaign->emergency_approval == 'pending')
                                <span class="badge badge-yellow">Menunggu Persetujuan</span>
                            @elseif($campaign->emergency_approval == 'approved')
                                <span class="badge badge-green">Disetujui</span>
                            @elseif($campaign->emergency_approval == 'rejected')
                                <span class="badge badge-red">Ditolak</span>
                            @else
                                <span class="badge badge-blue">Tidak Perlu Approval</span>
                            @endif
                        </td>
                    </tr>

                    @if($campaign->emergency_approval == 'approved')
                        <tr>
                            <th>Disetujui Oleh</th>
                            <td>{{ $campaign->emergencyApprovedBy->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Disetujui</th>
                            <td>{{ $campaign->emergency_approved_at ? \Carbon\Carbon::parse($campaign->emergency_approved_at)->format('d M Y H:i') : '-' }}
                            </td>
                        </tr>
                    @endif

                    @if($campaign->emergency_approval == 'rejected' && $campaign->emergency_rejection_reason)
                        <tr>
                            <th>Alasan Penolakan</th>
                            <td>
                                <div class="alert alert-danger mb-0">
                                    {{ $campaign->emergency_rejection_reason }}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            {{-- Action Buttons --}}
            @if($campaign->emergency_approval == 'pending')
                <div class="mt-3 d-flex gap-2">
                    <form action="{{ route('admin.campaign.emergency.approve', $campaign->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Setujui Campaign
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle"></i> Tolak Campaign
                    </button>
                </div>

                <!-- Modal Reject -->
                <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.campaign.emergency.reject', $campaign->id) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Tolak Campaign</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Alasan Penolakan <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="rejection_reason" 
                                                  class="form-control" 
                                                  rows="4" 
                                                  placeholder="Masukkan alasan penolakan campaign ini..."
                                                  required></textarea>
                                        <small class="text-muted">Alasan ini akan ditampilkan kepada penggalang dana.</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-x-circle"></i> Tolak
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    @endif

    {{-- Informasi Campaign --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Informasi Campaign</h2>
                <p class="card-subtitle">
                    Detail utama campaign donasi.
                </p>
            </div>

            <a href="{{ route('admin.campaign.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <table class="data-table data-table-kv">
            <tbody>
                <tr>
                    <th>Slug</th>
                    <td>
                        <span class="badge badge-blue">
                            {{ $campaign->slug }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Penggalang Dana</th>
                    <td>{{ $campaign->penggalangDana->nama_penggalang ?? '-' }}</td>
                </tr>

                <tr>
                    <th>Target Donasi</th>
                    <td>
                        <span class="text-muted-strong">
                            Rp {{ number_format($campaign->target_donasi, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Minimal Donasi</th>
                    <td>
                        <span class="text-muted-strong">
                            Rp {{ number_format($campaign->minimal_donasi, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Tanggal Mulai</th>
                    <td>
                        {{ $campaign->tanggal_mulai ? \Carbon\Carbon::parse($campaign->tanggal_mulai)->format('d M Y') : '-' }}
                    </td>
                </tr>

                <tr>
                    <th>Tanggal Berakhir</th>
                    <td>
                        {{ $campaign->tanggal_berakhir ? \Carbon\Carbon::parse($campaign->tanggal_berakhir)->format('d M Y') : '-' }}
                    </td>
                </tr>
            </tbody>
        </table>

    </section>

    {{-- Deskripsi --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Deskripsi Campaign</h2>
                <p class="card-subtitle">
                    Penjelasan lengkap tentang campaign.
                </p>
            </div>
        </div>

        <div class="detail-content">
            {!! nl2br(e($campaign->deskripsi)) !!}
        </div>

    </section>

    {{-- Galeri --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Galeri Campaign</h2>
                <p class="card-subtitle">
                    Kumpulan gambar pendukung campaign.
                </p>
            </div>
        </div>

        @if($campaign->campaignGambar->count())
            <div class="gallery-grid">
                @foreach($campaign->campaignGambar as $gambar)
                    <div class="gallery-item">
                        <img src="{{ asset('storage/' . $gambar->foto) }}" alt="{{ $campaign->judul }}">
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted">
                Tidak ada galeri.
            </p>
        @endif

    </section>

    {{-- Filter --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Filter Campaign</h2>
                <p class="card-subtitle">
                    Filter atau kategori tambahan yang terhubung dengan campaign.
                </p>
            </div>
        </div>

        @if($campaign->campaignFilter->count())
            <div class="badge-group">
                @foreach($campaign->campaignFilter as $filter)
                    <span class="badge badge-blue">
                        {{ $filter->filter->nama_filter ?? '-' }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-muted">
                Tidak ada filter.
            </p>
        @endif

    </section>

    {{-- Fitur Tambahan --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Fitur Tambahan</h2>
                <p class="card-subtitle">
                    Fitur opsional yang diaktifkan untuk campaign ini.
                </p>
            </div>
        </div>

        <table class="data-table data-table-kv">
            <tbody>
                <tr>
                    <th>Jumlah Package</th>
                    <td>
                        @if($campaign->enable_quantity)
                            <span class="badge badge-green">Aktif</span>
                        @else
                            <span class="badge badge-red">Nonaktif</span>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Nama Pekurban</th>
                    <td>
                        @if($campaign->enable_nama_donatur)
                            <span class="badge badge-green">Aktif</span>
                        @else
                            <span class="badge badge-red">Nonaktif</span>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Nominal Lainnya</th>
                    <td>
                        @if($campaign->enable_custom_nominal)
                            <span class="badge badge-green">Aktif</span>
                        @else
                            <span class="badge badge-red">Nonaktif</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

    </section>

    {{-- Update Campaign --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Update Campaign</h2>
                <p class="card-subtitle">
                    Riwayat update yang dibuat untuk campaign ini.
                </p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($campaign->campaignUpdates as $update)
                        <tr>
                            <td>
                                <p class="cell-title">
                                    {{ $update->judul_update }}
                                </p>
                            </td>

                            <td>
                                {{ $update->user->name ?? '-' }}
                            </td>

                            <td class="text-muted-strong">
                                {{ $update->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">
                                Belum ada update.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </section>

    {{-- Fundraiser Pendukung --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Fundraiser Pendukung</h2>
                <p class="card-subtitle">
                    Daftar user yang ikut menjadi fundraiser pendukung.
                </p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Nama User</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($campaign->campaignFundraisers as $fundraiser)
                        <tr>
                            <td>
                                <p class="cell-title">
                                    {{ $fundraiser->user->name ?? 'User tidak ditemukan' }}
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty-state">
                                Tidak ada fundraiser pendukung.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </section>

@endsection

@push('styles')
<style>
    /* Emergency Section */
    .emergency-section {
        border-left: 4px solid var(--primary);
        transition: all 0.3s ease;
    }

    .emergency-section.emergency {
        border-left-color: var(--danger);
    }

    .emergency-section.sustainable {
        border-left-color: var(--success);
    }

    /* Alert */
    .alert-danger {
        background-color: var(--danger-light);
        color: var(--danger);
        padding: var(--space-3) var(--space-4);
        border-radius: var(--radius-sm);
        border-left: 3px solid var(--danger);
    }

    /* Modal */
    .modal-content {
        border-radius: var(--radius);
        border: none;
        box-shadow: var(--shadow-hover);
    }

    .modal-header {
        border-bottom: 1px solid var(--border);
        padding: var(--space-4) var(--space-5);
    }

    .modal-header .modal-title {
        font-size: var(--fs-sm);
        font-weight: var(--fw-semibold);
        color: var(--text-dark);
    }

    .modal-body {
        padding: var(--space-5);
    }

    .modal-footer {
        border-top: 1px solid var(--border);
        padding: var(--space-4) var(--space-5);
    }

    .btn-close {
        background: none;
        border: none;
        font-size: var(--fs-lg);
        color: var(--muted);
        cursor: pointer;
        padding: 0;
        line-height: 1;
        transition: color 0.2s ease;
    }

    .btn-close:hover {
        color: var(--text);
    }

    /* Form */
    .form-label {
        font-size: var(--fs-xs);
        font-weight: var(--fw-medium);
        color: var(--text);
        margin-bottom: var(--space-2);
        display: block;
    }

    .form-label .text-danger {
        color: var(--danger);
    }

    .form-control {
        width: 100%;
        padding: var(--space-2) var(--space-3);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: var(--fs-xs);
        color: var(--text);
        background: var(--bg);
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .form-control::placeholder {
        color: var(--muted-light);
    }

    .text-muted {
        font-size: var(--fs-xxs);
        color: var(--muted);
        margin-top: var(--space-1);
        display: block;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-5);
        border: none;
        border-radius: var(--radius-sm);
        font-size: var(--fs-xs);
        font-weight: var(--fw-medium);
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-success {
        background-color: var(--success);
        color: #fff;
    }
    .btn-success:hover {
        background-color: #16a34a;
        transform: translateY(-1px);
        box-shadow: var(--shadow-soft);
    }

    .btn-danger {
        background-color: var(--danger);
        color: #fff;
    }
    .btn-danger:hover {
        background-color: #dc2626;
        transform: translateY(-1px);
        box-shadow: var(--shadow-soft);
    }

    .btn-secondary {
        background-color: var(--bg-soft);
        color: var(--text);
        border: 1px solid var(--border);
    }
    .btn-secondary:hover {
        background-color: var(--border);
        transform: translateY(-1px);
        box-shadow: var(--shadow-soft);
    }

    /* Utility */
    .d-flex {
        display: flex;
    }
    .d-inline {
        display: inline;
    }
    .gap-2 {
        gap: var(--space-2);
    }
    .mt-3 {
        margin-top: var(--space-3);
    }
    .mb-0 {
        margin-bottom: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .d-flex.gap-2 {
            flex-direction: column;
        }
        
        .d-flex.gap-2 .btn {
            width: 100%;
            justify-content: center;
        }
        
        .modal-dialog {
            margin: var(--space-3);
        }
    }
</style>
@endpush