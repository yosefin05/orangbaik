@extends('layouts.admin')

@section('page-title', 'Campaign')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Data Campaign</h2>
                <p class="card-subtitle">
                    Kelola seluruh campaign donasi yang ada di platform OrangBaik.id.
                </p>
            </div>

            {{-- Cek apakah admin terdaftar sebagai penggalang dana --}}
            @php
                $isPenggalangDana = auth()->user()->penggalangDana()->exists();
            @endphp

            @if($isPenggalangDana)
                <a href="{{ route('campaign.create') }}" class="btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Campaign</span>
                </a>
            @else
                <button type="button" class="btn-primary btn-disabled" onclick="openPenggalangModal()">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Campaign</span>
                </button>
            @endif
        </div>

        <div class="table-wrapper">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Penggalang Dana</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Tipe Campaign</th>
                        <th>Galeri</th>
                        <th>Update</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($campaign as $item)

                        <tr>
                            <td>
                                @if($item->thumbnail)
                                    <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->judul }}"
                                        class="table-thumbnail">
                                @else
                                    <span class="text-muted">Tidak ada</span>
                                @endif
                            </td>

                            <td>
                                <p class="cell-title">
                                    {{ $item->judul }}
                                </p>
                            </td>

                            <td>
                                {{ $item->kategori->nama_kategori ?? '-' }}
                            </td>

                            <td>
                                {{ $item->penggalangDana->nama_penggalang ?? '-' }}
                            </td>

                            <td>
                                <span class="text-muted-strong">
                                    Rp {{ number_format($item->target_donasi, 0, ',', '.') }}
                                </span>
                            </td>

                            <td>
                                @if($item->is_active)
                                    <span class="badge badge-green">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge badge-red">
                                        <i class="bi bi-x-circle-fill"></i>
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $badgeClass = match ($item->campaign_type) {
                                        'emergency' => 'badge-red',
                                        'sustainable' => 'badge-green',
                                        default => 'badge-blue',
                                    };

                                    $icon = match ($item->campaign_type) {
                                        'emergency' => 'bi-exclamation-triangle-fill',
                                        'sustainable' => 'bi-recycle',
                                        default => 'bi-file-text',
                                    };

                                    $label = match ($item->campaign_type) {
                                        'emergency' => 'Darurat',
                                        'sustainable' => 'Berkelanjutan',
                                        default => 'Regular',
                                    };
                                @endphp

                                <span class="badge {{ $badgeClass }}">
                                    <i class="bi {{ $icon }}"></i>
                                    {{ $label }}
                                </span>
                            </td>

                            <td>
                                <span class="badge badge-green">
                                    {{ $item->campaignUpdates->count() }} update
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-group">
                                    <a href="{{ route('admin.campaign.show', $item) }}" class="action-link link-blue"
                                        title="Lihat detail & review campaign">
                                        <i class="bi bi-eye"></i>
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="10" class="empty-state text-center py-4">
                                <div class="empty-state-content">
                                    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                    <p class="mt-2 text-muted fw-semibold">Belum ada campaign yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $campaign->firstItem() ?? 0 }}</strong> -
                <strong>{{ $campaign->lastItem() ?? 0 }}</strong> dari <strong>{{ $campaign->total() }}</strong> campaign
            </div>
            <div class="pagination-links">
                {{ $campaign->links() }}
            </div>
        </div>

    </section>

    <!-- MODAL DAFTAR PENGGALANG DANA -->
    <div class="modal-overlay" id="penggalangModal">
        <div class="penggalang-modal">
            <h2>Daftar Penggalang Dana</h2>
            <p>Silakan pilih jenis akun penggalang dana yang ingin didaftarkan.</p>
            <div class="modal-buttons">
                <a href="{{ route('verifikasi.penggalang') }}" class="btn-individu">Individu</a>
                <a href="{{ route('penggalang_dana.organisasi.create') }}" class="btn-organisasi">Organisasi</a>
            </div>
            <button type="button" class="btn-close" id="closePenggalangModal">Batal</button>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Function to open modal
        function openPenggalangModal() {
            const modal = document.getElementById('penggalangModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        // Function to close modal
        function closePenggalangModal() {
            const modal = document.getElementById('penggalangModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('penggalangModal');
            const closeBtn = document.getElementById('closePenggalangModal');

            // Close button
            if (closeBtn) {
                closeBtn.addEventListener('click', closePenggalangModal);
            }

            // Click outside modal content
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closePenggalangModal();
                }
            });

            // Close with ESC key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closePenggalangModal();
                }
            });
        });
    </script>
@endpush