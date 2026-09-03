@extends('layouts.admin')
@section('page-title', 'Berita')
@section('content')

    <section class="ob-card ob-card-lg">
        <div class="card-topbar">
            <div>
                <h2>Data Berita</h2>
                <p class="card-subtitle">
                    Kelola artikel dan berita yang ditampilkan pada platform OrangBaik.id.
                </p>
            </div>
            <a href="{{ route('admin.berita.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Berita</span>
            </a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Judul</th>
                        <th>Slug</th>
                        <th>Isi</th>
                        <th>Galeri</th>
                        <th>Penulis</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($berita as $item)
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
                                <span class="badge badge-blue">
                                    {{ $item->slug }}
                                </span>
                            </td>
                            <td>
                                <p class="cell-excerpt">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($item->isi), 60) }}
                                </p>
                            </td>
                            <td>
                                <span class="badge badge-green">
                                    {{ $item->gambar->count() }} gambar
                                </span>
                            </td>
                            <td>
                                {{ $item->user->name ?? 'User tidak ditemukan' }}
                            </td>
                            <td class="text-muted-strong">
                                {{ $item->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="{{ route('admin.berita.show', $item) }}" class="action-link link-blue">
                                        <i class="bi bi-eye"></i>
                                        <span>Detail</span>
                                    </a>
                                    <a href="{{ route('admin.berita.edit', $item) }}" class="action-link link-yellow">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form action="{{ route('admin.berita.destroy', $item) }}" method="POST" class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus berita ini?')">
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
                            <td colspan="8" class="empty-state text-center py-4">
                                <div class="empty-state-content">
                                    <i class="bi bi-newspaper text-muted" style="font-size: 2rem;"></i>
                                    <p class="mt-2 text-muted fw-semibold">Belum ada data berita yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $berita->firstItem() ?? 0 }}</strong> - <strong>{{ $berita->lastItem() ?? 0 }}</strong> dari <strong>{{ $berita->total() }}</strong> berita
            </div>
            <div class="pagination-links">
                {{ $berita->links() }}
            </div>
        </div>
    </section>
@endsection