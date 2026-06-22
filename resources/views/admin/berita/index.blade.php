@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">
        <div>
            <h2>Data Berita</h2>
            <span class="card-subtitle">Kelola artikel dan berita platform</span>
        </div>

        <a href="{{ route('admin.berita.create') }}" class="btn-primary">
            + Tambah Berita
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
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>

    @forelse($berita as $item)

    <tr>

        <td>
            <img
                src="{{ asset('storage/' . $item->thumbnail) }}"
                alt="{{ $item->judul }}"
                class="table-thumbnail"
            >
        </td>

        <td class="cell-title">
            {{ $item->judul }}
        </td>

        <td>
            <span class="badge badge-blue">
                {{ $item->slug }}
            </span>
        </td>

        <td>
            {{ Str::limit(strip_tags($item->isi), 60) }}
        </td>

        <td>
            <span class="badge badge-green">
                {{ $item->gambar->count() }} gambar
            </span>
        </td>

        <td>
            {{ $item->user->name }}
        </td>

        <td class="text-muted-strong">
            {{ $item->created_at->format('d M Y') }}
        </td>

        <td class="text-center">

            <div class="action-group action-group-center">

                <a
                    href="{{ route('admin.berita.show', $item) }}"
                    class="action-link link-blue"
                >
                    Detail
                </a>

                <a
                    href="{{ route('admin.berita.edit', $item) }}"
                    class="action-link link-yellow"
                >
                    Edit
                </a>

                <form
                    action="{{ route('admin.berita.destroy', $item) }}"
                    method="POST"
                    onsubmit="return confirm('Yakin ingin menghapus berita ini?')"
                    class="inline-form"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit" class="action-link link-red">
                                    Hapus
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>

                @empty

                <tr>
                    <td colspan="6" class="empty-state">
                        Belum ada data berita.
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $berita->links() }}
    </div>

</div>

@endsection