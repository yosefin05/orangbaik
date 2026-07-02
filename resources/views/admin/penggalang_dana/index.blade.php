@extends('layouts.admin')

@section('page-title', 'Penggalang Dana')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Data Penggalang Dana</h2>
                <p class="card-subtitle">
                    Kelola pengajuan penggalang dana yang masuk ke platform OrangBaik.id.
                </p>
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
                                @if($item->foto_profil)
                                    <img
                                        src="{{ asset('storage/' . $item->foto_profil) }}"
                                        alt="{{ $item->nama_penggalang }}"
                                        class="table-avatar">
                                @else
                                    <div class="table-avatar table-avatar-placeholder">
                                        {{ strtoupper(substr($item->nama_penggalang, 0, 1)) }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <p class="cell-title">
                                    {{ $item->nama_penggalang }}
                                </p>
                            </td>

                            <td>
                                {{ $item->user->name ?? 'User tidak ditemukan' }}
                            </td>

                            <td>
                                {{ ucfirst($item->jenis_penggalang) }}
                            </td>

                            <td>
                                @if($item->status === 'pending')
                                    <span class="badge badge-yellow">
                                        Pending
                                    </span>
                                @elseif($item->status === 'approved')
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

                                    <a
                                        href="{{ route('admin.penggalang_dana.show', $item) }}"
                                        class="action-link link-blue">
                                        <i class="bi bi-eye"></i>
                                        Detail
                                    </a>

                                    <form
                                        action="{{ route('admin.penggalang_dana.destroy', $item) }}"
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus penggalang dana ini?')">

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

    </section>

@endsection