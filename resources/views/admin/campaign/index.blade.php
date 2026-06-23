@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">

        <div>
            <h2>Data Campaign</h2>
            <span class="card-subtitle">
                Kelola seluruh campaign donasi
            </span>
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
                    <th class="text-center">
                        Aksi
                    </th>
                </tr>

            </thead>

            <tbody>

                @forelse($campaign as $item)

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

                        {{ $item->kategori->nama_kategori ?? '-' }}

                    </td>

                    <td>

                        {{ $item->penggalangDana->nama_penggalang ?? '-' }}

                    </td>

                    <td>

                        Rp {{ number_format($item->target_donasi, 0, ',', '.') }}

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

                    <td>

                        <span class="badge badge-blue">

                            {{ $item->campaignGambar->count() }}
                            gambar

                        </span>

                    </td>

                    <td>

                        <span class="badge badge-green">

                            {{ $item->campaignUpdates->count() }}
                            update

                        </span>

                    </td>

                    <td class="text-center">

                        <div
                            class="action-group action-group-center"
                        >

                            <a
                                href="{{ route('admin.campaign.show', $item) }}"
                                class="action-link link-blue"
                            >
                                Detail
                            </a>

                        </div>

                    </td>

                </tr>

                @empty

                <tr>

                    <td
                        colspan="9"
                        class="empty-state"
                    >
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

</div>

@endsection