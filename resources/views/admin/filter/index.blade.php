@extends('layouts.admin')

@section('page-title', 'Filter')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Data Filter</h2>
                <p class="card-subtitle">
                    Kelola kategori filter campaign yang ditampilkan pada platform OrangBaik.id.
                </p>
            </div>

            <a href="{{ route('admin.filter.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Filter</span>
            </a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Nama Filter</th>
                        <th>Slug</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($filters as $filter)

                        <tr>
                            <td>
                                <p class="cell-title">
                                    {{ $filter->nama_filter }}
                                </p>
                            </td>

                            <td>
                                <span class="badge badge-blue">
                                    {{ $filter->slug }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-group">

                                    <a
                                        href="{{ route('admin.filter.edit', $filter) }}"
                                        class="action-link link-yellow">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.filter.destroy', $filter) }}"
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus filter ini?')">

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
                            <td colspan="3" class="empty-state text-center py-4">
                                <div class="empty-state-content">
                                    <i class="bi bi-funnel text-muted" style="font-size: 2rem;"></i>
                                    <p class="mt-2 text-muted fw-semibold">Belum ada data filter yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $filters->firstItem() ?? 0 }}</strong> - <strong>{{ $filters->lastItem() ?? 0 }}</strong> dari <strong>{{ $filters->total() }}</strong> filter
            </div>
            <div class="pagination-links">
                {{ $filters->links() }}
            </div>
        </div>

    </section>

@endsection