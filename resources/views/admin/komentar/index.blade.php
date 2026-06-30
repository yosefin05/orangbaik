@extends('layouts.admin')

@section('page-title', 'Kelola Komentar')

@section('content')

    <div class="card">

        <div class="card-header">
            <div>
                <h2>Kelola Komentar</h2>
                <span class="card-subtitle">Daftar seluruh komentar pengguna pada berita</span>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th width="60">No</th>
                        <th>Pengguna</th>
                        <th>Berita</th>
                        <th>Komentar</th>
                        <th width="130">Tanggal</th>
                        <th class="text-center" width="160">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($komentar as $item)

                        <tr>
                            <td>{{ $komentar->firstItem() + $loop->index }}</td>

                            <td>{{ $item->user?->name ?? 'Pengguna' }}</td>

                            <td class="cell-excerpt">{{ $item->berita->judul }}</td>

                            <td class="cell-excerpt">{{ Str::limit($item->komentar, 60) }}</td>

                            <td class="text-muted-strong">{{ $item->created_at->format('d M Y') }}</td>

                            <td class="text-center">
                                <div class="action-group">

                                    <a href="{{ route('berita.show', $item->berita->slug) }}#komentar-{{ $item->id }}"
                                        class="action-link link-blue" target="_blank">

                                        Lihat

                                    </a>

                                    <form action="{{ route('admin.komentar.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus komentar ini?')" class="inline-form">
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
                            <td colspan="6" class="empty-state">
                                Belum ada komentar.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $komentar->links() }}
        </div>

    </div>

@endsection