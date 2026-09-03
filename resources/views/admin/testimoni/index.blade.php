@extends('layouts.admin')

@section('page-title', 'Testimoni')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Data Testimoni</h2>
                <p class="card-subtitle">
                    Kelola testimoni yang ditampilkan pada website OrangBaik.id.
                </p>
            </div>

            <a href="{{ route('admin.testimoni.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Testimoni</span>
            </a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Uploader</th>
                        <th>Testimoni</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($testimoni as $item)

                        <tr>
                            <td>
                                @if($item->foto_profil)
                                    <img
                                        src="{{ asset('storage/' . $item->foto_profil) }}"
                                        alt="{{ $item->nama }}"
                                        class="table-avatar">
                                @else
                                    <div class="table-avatar table-avatar-placeholder">
                                        {{ strtoupper(substr($item->nama, 0, 1)) }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <p class="cell-title">
                                    {{ $item->nama }}
                                </p>
                            </td>

                            <td>
                                {{ $item->jabatan }}
                            </td>

                            <td>
                                <span class="badge badge-blue">
                                    {{ $item->user->name ?? 'User tidak ditemukan' }}
                                </span>
                            </td>

                            <td>
                                <p class="cell-excerpt">
                                    {{ \Illuminate\Support\Str::limit($item->isi_testimoni, 60) }}
                                </p>
                            </td>

                            <td class="text-center">
                                <div class="action-group action-group-center">

                                    <a
                                        href="{{ route('admin.testimoni.show', $item) }}"
                                        class="action-link link-blue">
                                        <i class="bi bi-eye"></i>
                                        Detail
                                    </a>

                                    <a
                                        href="{{ route('admin.testimoni.edit', $item) }}"
                                        class="action-link link-yellow">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.testimoni.destroy', $item) }}"
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus testimoni ini?')">

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
                            <td colspan="6" class="empty-state text-center py-4">
                                <div class="empty-state-content">
                                    <i class="bi bi-chat-heart text-muted" style="font-size: 2rem;"></i>
                                    <p class="mt-2 text-muted fw-semibold">Belum ada data testimoni yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $testimoni->firstItem() ?? 0 }}</strong> - <strong>{{ $testimoni->lastItem() ?? 0 }}</strong> dari <strong>{{ $testimoni->total() }}</strong> testimoni
            </div>
            <div class="pagination-links">
                {{ $testimoni->links() }}
            </div>
        </div>

    </section>

@endsection