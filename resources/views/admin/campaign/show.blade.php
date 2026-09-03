@extends('layouts.admin')

@section('page-title', 'Detail Campaign')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/campaign/show.css') }}">
@endpush

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

            {{-- Status Campaign (aktif berdasarkan tanggal) --}}
            @php
                $isActiveNow = $campaign->is_time_active ?? $campaign->isTimeActive();
                $isActiveAndApproved = $isActiveNow && $campaign->isApproved();
            @endphp

            @if($isActiveAndApproved && $campaign->is_active)
                <span class="badge badge-green">✅ Aktif (Berjalan)</span>
            @elseif($campaign->is_active && !$isActiveNow)
                <span class="badge badge-yellow">⏳ Akan Datang</span>
            @elseif(!$isActiveNow && $campaign->tanggal_berakhir < now())
                <span class="badge badge-red">❌ Berakhir</span>
            @else
                <span class="badge badge-red">Tidak Aktif</span>
            @endif
        </div>
    </section>

    {{-- Approval Section --}}
    <section class="ob-card ob-card-lg"
        style="border-left: 4px solid {{ $campaign->campaign_type == 'emergency' ? '#dc3545' : ($campaign->campaign_type == 'sustainable' ? '#28a745' : '#6c757d') }};">
        <div class="card-topbar">
            <div>
                <h2>
                    @if($campaign->campaign_type == 'emergency')
                        <span class="badge badge-red">🔥 Darurat</span>
                    @elseif($campaign->campaign_type == 'sustainable')
                        <span class="badge badge-green">♻️ Berkelanjutan</span>
                    @else
                        <span class="badge badge-blue">📋 Regular</span>
                    @endif
                    Status Approval
                </h2>
                <p class="card-subtitle">
                    Status persetujuan campaign. Campaign emergency & sustainable perlu approval sebelum tampil di landing page.
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
                        @elseif($campaign->campaign_type == 'sustainable')
                            <span class="badge badge-green">Berkelanjutan</span>
                        @else
                            <span class="badge badge-blue">Regular</span>
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Status Approval</th>
                    <td>
                        @if($campaign->approval_status == 'pending')
                            <span class="badge badge-yellow">⏳ Menunggu Persetujuan</span>
                        @elseif($campaign->approval_status == 'approved')
                            <span class="badge badge-green">✅ Disetujui</span>
                        @elseif($campaign->approval_status == 'rejected')
                            <span class="badge badge-red">❌ Ditolak</span>
                        @else
                            <span class="badge badge-blue">ℹ️ Tidak Perlu Approval</span>
                        @endif
                    </td>
                </tr>

                @if($campaign->approval_status == 'approved')
                    <tr>
                        <th>Disetujui Oleh</th>
                        <td>{{ $campaign->approvedBy->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Disetujui</th>
                        <td>{{ $campaign->approved_at ? \Carbon\Carbon::parse($campaign->approved_at)->format('d M Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status Tampil di Landing Page</th>
                        <td>
                            @php
                                $canShow = $campaign->is_active && $campaign->isApproved() && $campaign->isTimeActive();
                            @endphp
                            @if($canShow)
                                <span class="badge badge-green">✅ Tampil</span>
                            @elseif($campaign->is_active && !$campaign->isApproved())
                                <span class="badge badge-yellow">⏳ Menunggu Approval</span>
                            @elseif(!$campaign->isTimeActive())
                                <span class="badge badge-yellow">⏳ Belum Dimulai / Sudah Berakhir</span>
                            @else
                                <span class="badge badge-red">❌ Tidak Tampil</span>
                            @endif
                        </td>
                    </tr>
                @endif

                @if($campaign->approval_status == 'rejected' && $campaign->rejection_reason)
                    <tr>
                        <th>Alasan Penolakan</th>
                        <td>
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle"></i>
                                {{ $campaign->rejection_reason }}
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- ACTION BUTTONS UNTUK EMERGENCY & SUSTAINABLE --}}
        @if(in_array($campaign->campaign_type, ['emergency', 'sustainable']))

            @if($campaign->approval_status == 'pending')
                <div class="approval-actions">
                    <form action="{{ route('admin.campaign.approve', $campaign->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-approve">
                            <i class="bi bi-check-circle-fill"></i>
                            Setujui & Tampilkan di Landing Page
                        </button>
                        <small class="btn-helper">
                            Campaign akan langsung tampil di landing page setelah disetujui.
                        </small>
                    </form>

                    <button type="button" class="btn btn-reject" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle-fill"></i>
                        Tolak Campaign
                    </button>
                </div>

            @elseif($campaign->approval_status == 'approved')
                <div class="alert-approval success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div class="alert-content">
                        <div class="alert-title">✅ Campaign sudah disetujui</div>
                        <div class="alert-text">
                            Campaign akan tampil di landing page.
                            @if(!$campaign->is_active)
                                <br>
                                <span class="text-warning">⚠️ Namun campaign sedang tidak aktif.</span>
                            @endif
                            @if(!$campaign->isTimeActive())
                                <br>
                                <span class="text-warning">⏳ Campaign belum dimulai atau sudah berakhir.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.campaign.unapprove', $campaign->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-unapprove" onclick="return confirm('Yakin ingin membatalkan approval?')">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Batalkan Approval
                    </button>
                </form>

            @elseif($campaign->approval_status == 'rejected')
                <div class="alert-approval danger">
                    <i class="bi bi-x-circle-fill"></i>
                    <div class="alert-content">
                        <div class="alert-title">❌ Campaign ditolak</div>
                        <div class="alert-text">
                            Campaign ini telah ditolak.
                            @if($campaign->rejection_reason)
                                <div class="rejection-reason-box">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Alasan:</strong> {{ $campaign->rejection_reason }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.campaign.approve', $campaign->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-approve">
                        <i class="bi bi-check-circle-fill"></i>
                        Setujui Ulang Campaign
                    </button>
                </form>
            @endif

        @else
            <div class="alert-approval info">
                <i class="bi bi-info-circle-fill"></i>
                <div class="alert-content">
                    <div class="alert-title">ℹ️ Campaign Regular</div>
                    <div class="alert-text">
                        Campaign regular tidak memerlukan approval dan langsung dapat tampil di landing page.
                    </div>
                </div>
            </div>
        @endif
    </section>

    {{-- Informasi Campaign --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Informasi Campaign</h2>
                <p class="card-subtitle">Detail utama campaign donasi.</p>
            </div>

            <a href="{{ route('admin.campaign.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> <span>Kembali</span>
            </a>
        </div>

        <table class="data-table data-table-kv">
            <tbody>
                <tr><th>Slug</th><td><span class="badge badge-blue">{{ $campaign->slug }}</span></td></tr>
                <tr><th>Penggalang Dana</th><td>{{ $campaign->penggalangDana->nama_penggalang ?? '-' }}</td></tr>
                <tr><th>Target Donasi</th><td><span class="text-muted-strong">Rp {{ number_format($campaign->target_donasi, 0, ',', '.') }}</span></td></tr>
                <tr><th>Minimal Donasi</th><td><span class="text-muted-strong">Rp {{ number_format($campaign->minimal_donasi, 0, ',', '.') }}</span></td></tr>
                <tr><th>Tanggal Mulai</th><td>{{ $campaign->tanggal_mulai ? \Carbon\Carbon::parse($campaign->tanggal_mulai)->format('d M Y') : '-' }}</td></tr>
                <tr><th>Tanggal Berakhir</th><td>{{ $campaign->tanggal_berakhir ? \Carbon\Carbon::parse($campaign->tanggal_berakhir)->format('d M Y') : '-' }}</td></tr>
                <tr>
                    <th>Status Campaign</th>
                    <td>
                        @php
                            $isTimeActive = $campaign->isTimeActive();
                        @endphp
                        @if($campaign->is_active && $isTimeActive)
                            <span class="badge badge-green">✅ Aktif (Berjalan)</span>
                        @elseif($campaign->is_active && !$isTimeActive)
                            <span class="badge badge-yellow">⏳ Akan Datang</span>
                        @elseif(!$isTimeActive && $campaign->tanggal_berakhir < now())
                            <span class="badge badge-red">❌ Berakhir</span>
                        @else
                            <span class="badge badge-red">Tidak Aktif</span>
                        @endif
                    </td>
                </tr>
                @if($campaign->verified_at)
                    <tr><th>Diverifikasi Oleh</th><td>{{ $campaign->verifier->name ?? '-' }}</td></tr>
                    <tr><th>Tanggal Verifikasi</th><td>{{ $campaign->verified_at ? \Carbon\Carbon::parse($campaign->verified_at)->format('d M Y H:i') : '-' }}</td></tr>
                @endif
            </tbody>
        </table>
    </section>

    {{-- Deskripsi --}}
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Deskripsi Campaign</h2>
                <p class="card-subtitle">Penjelasan lengkap tentang campaign.</p>
            </div>
        </div>
        <div class="detail-content">
            {!! $campaign->deskripsi !!}
        </div>
    </section>

    {{-- Filter --}}
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Filter Campaign</h2>
                <p class="card-subtitle">Filter atau kategori tambahan yang terhubung dengan campaign.</p>
            </div>
        </div>
        @if($campaign->campaignFilter->count())
            <div class="badge-group">
                @foreach($campaign->campaignFilter as $campaignFilter)
                    <span class="badge badge-blue">{{ $campaignFilter->filter->nama_filter ?? '-' }}</span>
                @endforeach
            </div>
        @else
            <p class="text-muted">Tidak ada filter.</p>
        @endif
    </section>

    {{-- Fitur Tambahan --}}
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Fitur Tambahan</h2>
                <p class="card-subtitle">Fitur opsional yang diaktifkan untuk campaign ini.</p>
            </div>
        </div>
        <table class="data-table data-table-kv">
            <tbody>
                <tr><th>Jumlah Package</th><td>@if($campaign->enable_quantity) <span class="badge badge-green">Aktif</span> @else <span class="badge badge-red">Nonaktif</span> @endif</td></tr>
                <tr><th>Nama Donatur</th><td>@if($campaign->enable_nama_donatur) <span class="badge badge-green">Aktif</span> @else <span class="badge badge-red">Nonaktif</span> @endif</td></tr>
                <tr><th>Nominal Lainnya</th><td>@if($campaign->enable_custom_nominal) <span class="badge badge-green">Aktif</span> @else <span class="badge badge-red">Nonaktif</span> @endif</td></tr>
            </tbody>
        </table>
    </section>

    {{-- Update Campaign --}}
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Update Campaign</h2>
                <p class="card-subtitle">Riwayat update yang dibuat untuk campaign ini.</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr><th>Judul</th><th>Dibuat Oleh</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                    @forelse($campaign->campaignUpdates as $update)
                        <tr>
                            <td><p class="cell-title">{{ $update->judul_update }}</p></td>
                            <td>{{ $campaign->penggalangDana->nama_penggalang ?? $update->user->name ?? '-' }}</td>
                            <td class="text-muted-strong">{{ $update->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">Belum ada update.</td></tr>
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
                <p class="card-subtitle">Daftar user yang ikut menjadi fundraiser pendukung.</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Nama User</th><th>Total Donasi</th></tr></thead>
                <tbody>
                    @forelse($campaign->campaignFundraisers as $fundraiser)
                        <tr>
                            <td><p class="cell-title">{{ $fundraiser->user->name ?? 'User tidak ditemukan' }}</p></td>
                            <td>Rp {{ number_format($fundraiser->total_donasi_settlement, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty-state">Tidak ada fundraiser pendukung.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Packages --}}
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Package Campaign</h2>
                <p class="card-subtitle">Daftar package donasi yang tersedia untuk campaign ini.</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr><th>Nama Package</th><th>Nominal</th><th>Deskripsi</th></tr></thead>
                <tbody>
                    @forelse($campaign->packages as $package)
                        <tr>
                            <td><p class="cell-title">{{ $package->judul ?? $package->nama_package ?? '-' }}</p></td>
                            <td><span class="text-muted-strong">Rp {{ number_format($package->nominal, 0, ',', '.') }}</span></td>
                            <td>{{ $package->deskripsi ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">Belum ada package.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.campaign.reject', $campaign->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-danger"></i> Tolak Campaign</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" placeholder="Tulis alasan mengapa campaign ini ditolak..." required></textarea>
                            <small class="text-muted">Alasan ini akan ditampilkan kepada penggalang dana.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-reject">
                            <i class="bi bi-x-circle-fill"></i> Tolak Campaign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection