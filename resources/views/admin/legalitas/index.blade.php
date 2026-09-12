@extends('layouts.admin')

@section('page-title', 'Manajemen Legalitas')

@section('content')
    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Legalitas Lembaga</h2>
                <p class="card-subtitle">Kelola dokumen izin dan legalitas yang tampil di halaman Tentang Kami.</p>
            </div>

            <a href="{{ route('admin.legalitas.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Legalitas</span>
            </a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>File / Preview</th>
                        <th>Judul Legalitas</th>
                        <th>Nomor</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($legalitas as $item)
                        <tr>
                            <td>{{ $item->urutan }}</td>
                            <td>
                                @if($item->isImage())
                                    <img src="{{ asset('storage/' . $item->file_path) }}" alt="{{ $item->judul }}" style="height: 48px; width: 64px; object-fit: cover; border-radius: 6px;">
                                @else
                                    <span class="badge badge-blue"><i class="bi bi-file-earmark-pdf"></i> PDF</span>
                                @endif
                            </td>
                            <td>
                                <p class="cell-title">{{ $item->judul }}</p>
                                @if($item->deskripsi)
                                    <p class="cell-excerpt">{{ \Illuminate\Support\Str::limit($item->deskripsi, 60) }}</p>
                                @endif
                            </td>
                            <td>{{ $item->nomor_legalitas ?? '-' }}</td>
                            <td>
                                <form action="{{ route('admin.legalitas.toggle', $item) }}" method="POST" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="badge {{ $item->is_active ? 'badge-blue' : 'badge-red' }}" style="border:none; cursor:pointer;">
                                        {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <div class="action-group">
                                    <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank" class="action-link link-blue">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
                                    <a href="{{ route('admin.legalitas.edit', $item) }}" class="action-link link-yellow">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.legalitas.destroy', $item) }}" method="POST" class="inline-form" onsubmit="return confirm('Yakin ingin menghapus data legalitas ini?')">
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
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data legalitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($legalitas->hasPages())
            <div class="mt-4">
                {{ $legalitas->links() }}
            </div>
        @endif
    </section>
@endsection
