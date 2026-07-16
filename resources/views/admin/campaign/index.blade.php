@extends('layouts.admin')

@section('page-title', 'Campaign')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Data Campaign</h2>
                <p class="card-subtitle">
                    Kelola seluruh campaign donasi yang ada di platform OrangBaik.id.
                </p>
            </div>
        </div>

        <div class="table-wrapper">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Penggalang Dana</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Galeri</th>
                        <th>Update</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($campaign as $item)

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
                                {{ $item->kategori->nama_kategori ?? '-' }}
                            </td>

                            <td>
                                {{ $item->penggalangDana->nama_penggalang ?? '-' }}
                            </td>

                            <td>
                                <span class="text-muted-strong">
                                    Rp {{ number_format($item->target_donasi, 0, ',', '.') }}
                                </span>
                            </td>

                            <td>
                                {{--
                                PHP
                                --}}
                                @php
                                    $hariIni = now();
                                    $tanggalBerakhir = \Carbon\Carbon::parse($item->tanggal_berakhir);
                                @endphp

                                @if($hariIni->lte($tanggalBerakhir))
                                    <span class="badge badge-green">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge badge-red">
                                        Berakhir
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="badge badge-blue">
                                    {{ $item->campaignGambar->count() }} gambar
                                </span>
                            </td>

                            <td>
                                <span class="badge badge-green">
                                    {{ $item->campaignUpdates->count() }} update
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-group action-group-center">

                                    <a href="{{ route('admin.campaign.show', $item) }}" class="action-link link-blue">
                                        <i class="bi bi-eye"></i>
                                        Detail
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
                            <td colspan="9" class="empty-state">
                                Belum ada campaign.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="pagination-wrapper">
            {{ $campaign->links() }}
        </div>

    </section>

@endsection