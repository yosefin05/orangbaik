@extends('layouts.admin')

@section('content')

    <div class="card">

        <div class="card-header">
            <div>
                <h2>Data Testimoni</h2>
                <span class="card-subtitle">
                    Kelola testimoni yang ditampilkan pada website.
                </span>
            </div>

            <a href="{{ route('admin.testimoni.create') }}" class="btn-primary">
                + Tambah Testimoni
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
                                <img src="{{ asset('storage/' . $item->foto_profil) }}" alt="{{ $item->nama }}" width="60"
                                    height="60" style="
                                                border-radius:50%;
                                                object-fit:cover;
                                            ">
                            </td>

                            <td>
                                {{ $item->nama }}
                            </td>

                            <td>
                                {{ $item->jabatan }}
                            </td>

                            <td>
                                <span class="badge badge-blue">
                                    {{ $item->user->name }}
                                </span>
                            </td>

                            <td>
                                {{ Str::limit($item->isi_testimoni, 50) }}
                            </td>

                            <td class="text-center">
                                <div class="action-group">

                                    <a href="{{ route('admin.testimoni.show', $item) }}" class="action-link link-blue">
                                        Detail
                                    </a>

                                    <a href="{{ route('admin.testimoni.edit', $item) }}" class="action-link link-yellow">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.testimoni.destroy', $item) }}" method="POST"
                                        class="inline-form" onsubmit="return confirm('Yakin hapus testimoni ini?')">

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
                            <td colspan="5" class="empty-state">
                                Belum ada data testimoni.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $testimoni->links() }}
        </div>

    </div>

@endsection