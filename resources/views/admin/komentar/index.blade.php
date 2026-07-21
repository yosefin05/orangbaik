@extends('layouts.admin')

@section('page-title', 'Kelola Komentar')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Kelola Komentar</h2>
                <p class="card-subtitle">
                    Daftar seluruh komentar pengguna pada berita OrangBaik.id.
                </p>
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
                            <td class="text-muted-strong">
                                {{ $komentar->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <p class="cell-title">
                                    {{ $item->user?->name ?? 'Pengguna' }}
                                </p>
                            </td>

                            <td>
                                <p class="cell-excerpt">
                                    {{ $item->berita?->judul ?? 'Berita tidak ditemukan' }}
                                </p>
                            </td>

                            <td>
                                <p class="cell-excerpt">
                                    {{ \Illuminate\Support\Str::limit($item->komentar, 60) }}
                                </p>
                            </td>

                            <td class="text-muted-strong">
                                {{ $item->created_at->format('d M Y') }}
                            </td>

                            <td class="text-center">
                                <div class="action-group">

                                    @if($item->berita)
                                        <a
                                            href="{{ route('berita.show', $item->berita->slug) }}#komentar-{{ $item->id }}"
                                            class="action-link link-blue"
                                            target="_blank">
                                            <i class="bi bi-eye"></i>
                                            Lihat
                                        </a>
                                    @else
                                        <span class="text-muted">
                                            Tidak tersedia
                                        </span>
                                    @endif

                                    <form
                                        action="{{ route('admin.komentar.destroy', $item) }}"
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus komentar ini?')">

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

    </section>

@endsection