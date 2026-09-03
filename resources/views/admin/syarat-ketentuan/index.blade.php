@extends('layouts.admin')

@section('page-title', 'Syarat & Ketentuan')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Syarat & Ketentuan</h2>
                <p class="card-subtitle">
                    Kelola bagian konten yang tampil di halaman syarat dan ketentuan.
                </p>
            </div>

            <a href="{{ route('admin.syarat-ketentuan.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Bagian</span>
            </a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Judul</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->urutan }}</td>
                            <td>
                                <p class="cell-title">{{ $item->judul }}</p>
                                <p class="cell-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($item->isi), 80) }}</p>
                            </td>
                            <td>
                                <span class="badge {{ $item->is_active ? 'badge-blue' : 'badge-red' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="action-group">
                                    <a href="{{ route('admin.syarat-ketentuan.edit', $item) }}" class="action-link link-yellow">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit
                                    </a>
                                    <form
                                        action="{{ route('admin.syarat-ketentuan.destroy', $item) }}"
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus bagian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-link link-red">
                                            <i class="bi bi-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state text-center py-4">
                                <div class="empty-state-content">
                                    <i class="bi bi-file-earmark-text text-muted" style="font-size: 2rem;"></i>
                                    <p class="mt-2 text-muted fw-semibold">Belum ada bagian syarat dan ketentuan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $items->firstItem() ?? 0 }}</strong> - <strong>{{ $items->lastItem() ?? 0 }}</strong> dari <strong>{{ $items->total() }}</strong> bagian
            </div>
            <div class="pagination-links">
                {{ $items->links() }}
            </div>
        </div>

    </section>

@endsection
