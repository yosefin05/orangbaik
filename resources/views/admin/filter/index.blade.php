@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">
        <div>
            <h2>Data Filter</h2>
            <span class="card-subtitle">Kelola kategori filter campaign</span>
        </div>

        <a href="{{ route('admin.filter.create') }}" class="btn-primary">
            + Tambah Filter
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
                    <td>{{ $filter->nama_filter }}</td>
                    <td><span class="badge badge-blue">{{ $filter->slug }}</span></td>

                    <td class="text-center">
                        <div class="action-group">

                            <a href="{{ route('admin.filter.edit', $filter) }}" class="action-link link-yellow">
                                Edit
                            </a>

                            <form
                                action="{{ route('admin.filter.destroy', $filter) }}"
                                method="POST"
                                onsubmit="return confirm('Yakin hapus filter ini?')"
                                class="inline-form"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-link link-red">
                                    Hapus
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>

                @empty

                <tr>
                    <td colspan="3" class="empty-state">
                        Belum ada data filter.
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $filters->links() }}
    </div>

</div>

@endsection