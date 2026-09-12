<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penggalang Dana - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        .dashboard-page {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 4rem;
        }
        .dashboard-hero {
            background: linear-gradient(135deg, #3365af 0%, #1e40af 100%);
            color: #ffffff;
            padding: 2.5rem 0 4rem;
            position: relative;
        }
        .dashboard-hero h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .dashboard-hero p {
            font-size: 0.95rem;
            opacity: 0.9;
        }
        .dashboard-body {
            margin-top: -2.5rem;
            position: relative;
            z-index: 10;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 1.25rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .stat-icon.blue { background: #eff6ff; color: #2563eb; }
        .stat-icon.green { background: #f0fdf4; color: #16a34a; }
        .stat-icon.emerald { background: #ecfdf5; color: #059669; }
        .stat-icon.purple { background: #faf5ff; color: #9333ea; }
        .stat-icon.orange { background: #fff7ed; color: #ea580c; }
        .stat-val {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }
        .stat-lbl {
            font-size: 0.8125rem;
            color: #64748b;
        }
        .action-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .action-btn-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
        }
        .action-btn-card:hover {
            border-color: #3365af;
            background: #f0f7ff;
            color: #3365af;
            transform: translateY(-2px);
        }
        .action-btn-card i {
            font-size: 1.25rem;
            color: #3365af;
        }
        .section-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }
        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .campaign-list-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .dash-campaign-item {
            display: flex;
            gap: 1.25rem;
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            align-items: center;
        }
        @media (max-width: 768px) {
            .dash-campaign-item {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        .dash-campaign-thumb {
            width: 110px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .dash-campaign-info {
            flex-grow: 1;
        }
        .dash-campaign-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.35rem;
            text-decoration: none;
        }
        .dash-campaign-title:hover {
            color: #3365af;
        }
        .dash-campaign-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            font-size: 0.8125rem;
            color: #64748b;
        }
        .badge-status {
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge-status.active { background: #dcfce7; color: #15803d; }
        .badge-status.pending { background: #fef3c7; color: #b45309; }
        .badge-status.rejected { background: #fee2e2; color: #b91c1c; }
        .badge-status.ended { background: #f1f5f9; color: #475569; }
        
        .dash-progress-wrap {
            width: 100%;
            max-width: 200px;
        }
        .dash-progress-bar {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 4px;
        }
        .dash-progress-fill {
            height: 100%;
            background: #3365af;
            border-radius: 3px;
        }
        .dash-action-btns {
            display: flex;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        .btn-dash-sm {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }
        .btn-dash-primary {
            background: #3365af;
            color: #ffffff;
        }
        .btn-dash-primary:hover {
            background: #254a85;
            color: #ffffff;
        }
        .btn-dash-outline {
            border: 1px solid #cbd5e1;
            color: #334155;
            background: #ffffff;
        }
        .btn-dash-outline:hover {
            border-color: #3365af;
            color: #3365af;
            background: #f8fafc;
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .table-custom th, .table-custom td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-custom th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
        }
    </style>
</head>
<body class="dashboard-page">

@include('components.header')

<header class="dashboard-hero">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1>Dashboard {{ $penggalang->nama_penggalang }}</h1>
                <p>Kelola campaign, pantau donasi terkumpul, dan perbarui kabar terbaru secara real-time.</p>
            </div>
            <a href="{{ route('profil.penggalang', $penggalang->id) }}" class="btn-dash-sm btn-dash-outline" style="background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.3);">
                <i class="bi bi-person-bounding-box"></i> Lihat Profil Publik
            </a>
        </div>
    </div>
</header>

<main class="container dashboard-body">

    <!-- STATS OVERVIEW -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-megaphone-fill"></i></div>
            <div>
                <div class="stat-val">{{ $totalCampaign }}</div>
                <div class="stat-lbl">Total Campaign</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
            <div>
                <div class="stat-val">{{ $campaignAktif }}</div>
                <div class="stat-lbl">Campaign Aktif</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon emerald"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-val">Rp {{ number_format($totalDanaTerkumpul, 0, ',', '.') }}</div>
                <div class="stat-lbl">Total Dana Terkumpul</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-val">{{ $totalDonatur }}</div>
                <div class="stat-lbl">Total Donatur</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-person-hearts"></i></div>
            <div>
                <div class="stat-val">{{ $totalFundraiser }}</div>
                <div class="stat-lbl">Total Fundraiser</div>
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="action-cards-grid">
        <a href="{{ route('campaign.create') }}" class="action-btn-card">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Buat Campaign Baru</span>
        </a>
        <a href="{{ route('profil.penggalang', $penggalang->id) }}" class="action-btn-card">
            <i class="bi bi-pencil-square"></i>
            <span>Kelola Profil Penggalang</span>
        </a>
        <a href="{{ route('profile.user') }}" class="action-btn-card">
            <i class="bi bi-person-gear"></i>
            <span>Pengaturan Akun</span>
        </a>
    </div>

    <!-- CAMPAIGN SAYA -->
    <section class="section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="bi bi-grid-fill text-primary"></i> Campaign Saya
            </h2>
            <a href="{{ route('campaign.create') }}" class="btn-dash-sm btn-dash-primary">
                <i class="bi bi-plus-lg"></i> Buat Campaign
            </a>
        </div>

        @if($campaigns->count() > 0)
            <div class="campaign-list-grid">
                @foreach($campaigns as $cmp)
                    @php
                        $terkumpul = $cmp->getTotalDonasiSuccess();
                        $donatur = $cmp->getDonaturCount();
                        $progress = $cmp->getProgressPercentage();
                        $statusText = $cmp->getStatusText();
                    @endphp
                    <div class="dash-campaign-item">
                        <img src="{{ asset('storage/' . $cmp->thumbnail) }}" alt="{{ $cmp->judul }}" class="dash-campaign-thumb">
                        <div class="dash-campaign-info">
                            <a href="{{ route('campaign.show', $cmp->getRouteSlug()) }}" class="dash-campaign-title">
                                {{ $cmp->judul }}
                            </a>
                            <div class="dash-campaign-meta my-1">
                                <span class="badge-status {{ $statusText === 'Aktif' ? 'active' : ($statusText === 'Menunggu Persetujuan' ? 'pending' : 'ended') }}">
                                    {{ $statusText }}
                                </span>
                                <span><i class="bi bi-tag-fill"></i> {{ ucfirst($cmp->campaign_type) }}</span>
                                <span><i class="bi bi-people-fill"></i> {{ $donatur }} donatur</span>
                                <span><i class="bi bi-calendar-event"></i> {{ $cmp->tanggal_berakhir ? $cmp->tanggal_berakhir->format('d M Y') : 'Tanpa batas' }}</span>
                            </div>
                            <div class="dash-progress-wrap mt-2">
                                <div class="d-flex justify-content-between fs-7 text-muted mb-1" style="font-size: 0.75rem;">
                                    <span><strong>Rp {{ number_format($terkumpul, 0, ',', '.') }}</strong></span>
                                    <span>{{ number_format($progress, 1) }}% dari Rp {{ number_format($cmp->target_donasi, 0, ',', '.') }}</span>
                                </div>
                                <div class="dash-progress-bar">
                                    <div class="dash-progress-fill" style="width: {{ min($progress, 100) }}%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="dash-action-btns">
                            <a href="{{ route('campaign.show', $cmp->getRouteSlug()) }}" class="btn-dash-sm btn-dash-outline" title="Lihat Detail">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                            <a href="{{ route('campaign.update.create', $cmp->getRouteSlug()) }}" class="btn-dash-sm btn-dash-outline" title="Tambah Kabar Terbaru">
                                <i class="bi bi-newspaper"></i> Kabar
                            </a>
                            <a href="{{ route('campaign.edit', $cmp->id) }}" class="btn-dash-sm btn-dash-outline" title="Edit">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-megaphone text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3 text-muted">Belum ada campaign yang dibuat.</p>
                <a href="{{ route('campaign.create') }}" class="btn-dash-sm btn-dash-primary mt-2">
                    <i class="bi bi-plus-lg"></i> Buat Campaign Pertama
                </a>
            </div>
        @endif
    </section>

    <!-- DONASI TERBARU (SETTLEMENT ONLY) -->
    <section class="section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="bi bi-clock-history text-primary"></i> Donasi Terbaru (Lunas)
            </h2>
        </div>

        @if($recentDonations->count() > 0)
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Donatur</th>
                            <th>Campaign</th>
                            <th>Nominal</th>
                            <th>Nomor HP</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentDonations as $d)
                            <tr>
                                <td>
                                    <strong>{{ $d->is_anonim ? 'Hamba Allah (Anonim)' : $d->nama_donatur }}</strong>
                                </td>
                                <td>
                                    <a href="{{ route('campaign.show', $d->campaign?->getRouteSlug() ?? '') }}" style="color: #3365af; text-decoration: none;">
                                        {{ Str::limit($d->campaign?->judul ?? '-', 35) }}
                                    </a>
                                </td>
                                <td><strong class="text-success">Rp {{ number_format($d->nominal, 0, ',', '.') }}</strong></td>
                                <td>{{ $d->no_hp ?? '-' }}</td>
                                <td>{{ $d->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted text-center py-3 mb-0">Belum ada donasi yang masuk.</p>
        @endif
    </section>

</main>

@include('components.footer')

<script src="{{ asset('js/header.js') }}"></script>
</body>
</html>
