@extends('layouts.admin')

@section('content')

    <div class="card">

        <div class="card-header">
            <div>
                <h2>Data Penggalang Dana</h2>
                <span class="card-subtitle">
                    Kelola pengajuan penggalang dana
                </span>
            </div>
        </div>

        <div class="table-wrapper">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama Penggalang</th>
                        <th>User</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Tanggal Daftar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($penggalangDana as $item)

                        <tr>

                            <td>

                                <img src="{{ asset('storage/' . $item->foto_profil) }}" alt="{{ $item->nama_penggalang }}"
                                    class="table-thumbnail">

                            </td>

                            <td class="cell-title">
                                {{ $item->nama_penggalang }}
                            </td>

                            <td>
                                {{ $item->user->name }}
                            </td>

                            <td>
                                {{ ucfirst($item->jenis_penggalang) }}
                            </td>

                            <td>

                                @if($item->status == 'pending')

                                    <span class="badge badge-yellow">
                                        Pending
                                    </span>

                                @elseif($item->status == 'approved')

                                    <span class="badge badge-green">
                                        Approved
                                    </span>

                                @else

                                    <span class="badge badge-red">
                                        Rejected
                                    </span>

                                @endif

                            </td>

                            <td class="text-muted-strong">
                                {{ $item->created_at->format('d M Y') }}
                            </td>

                            <td class="text-center">

                                <div class="action-group action-group-center">

                                    <a href="{{ route('admin.penggalang_dana.show', $item) }}" class="action-link link-blue">
                                        Detail
                                    </a>
                                    <form action="{{ route('admin.penggalang_dana.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus penggalang dana ini?')" class="inline-form">
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

                            <td colspan="7" class="empty-state">
                                Belum ada pengajuan penggalang dana.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="pagination-wrapper">
            {{ $penggalangDana->links() }}
        </div>

    </div>

@endsection