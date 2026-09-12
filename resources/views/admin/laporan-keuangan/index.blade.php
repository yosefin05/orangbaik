@extends('layouts.admin')

@section('page-title', 'Laporan Keuangan')

@section('content')
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Laporan Keuangan Lembaga</h2>
                <p class="card-subtitle">Kelola file laporan keuangan tahunan yang dapat diakses publik di halaman Tentang Kami.</p>
            </div>

            <a href="{{ route('admin.laporan-keuangan.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Laporan</span>
            </a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Judul Laporan</th>
                        <th>File Laporan</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $item)
                        <tr>
                            <td><strong class="text-primary" style="font-size:1.1rem;">{{ $item->tahun }}</strong></td>
                            <td>
                                <p class="cell-title">{{ $item->judul }}</p>
                                @if($item->deskripsi)
                                    <p class="cell-excerpt">{{ \Illuminate\Support\Str::limit($item->deskripsi, 60) }}</p>
                                @endif
                            </td>
                            <td>
                                <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank" class="btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> Unduh/Lihat
                                </a>
                            </td>
                            <td>
                                <form action="{{ route('admin.laporan-keuangan.toggle', $item) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="badge {{ $item->is_active ? 'badge-blue' : 'badge-red' }}" style="border:none; cursor:pointer;">
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <div class="action-group">
                                    <a href="{{ route('admin.laporan-keuangan.edit', $item) }}" class="action-link link-yellow">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.laporan-keuangan.destroy', $item) }}" method="POST" class="inline-form" onsubmit="return confirm('Yakin ingin menghapus laporan tahun {{ $item->tahun }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-link link-red">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada laporan keuangan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="mt-4">
                {{ $reports->links() }}
            </div>
        @endif
    </section>
@endsection
