@extends('layouts.admin')

@section('content')

<div class="page-header">

```
<div>
    <h2>{{ $campaign->judul }}</h2>
    <p>Detail Campaign</p>
</div>

<a
    href="{{ route('admin.campaign.index') }}"
    class="btn-secondary"
>
    ← Kembali
</a>
```

</div>

<div class="card">

```
<div
    style="
        display:flex;
        gap:24px;
        align-items:center;
    "
>

    <img
        src="{{ asset('storage/' . $campaign->thumbnail) }}"
        alt="{{ $campaign->judul }}"
        class="current-thumbnail"
    >

    <div>

        <h2>
            {{ $campaign->judul }}
        </h2>

        <p>
            {{ $campaign->kategori->nama_kategori }}
        </p>

        @if($campaign->status == 'pending')

            <span class="badge badge-yellow">
                Pending
            </span>

        @elseif($campaign->status == 'approved')

            <span class="badge badge-green">
                Approved
            </span>

        @else

            <span class="badge badge-red">
                Rejected
            </span>

        @endif

    </div>

</div>
```

</div>

<div class="card">

```
<div class="card-header">
    <h3>Informasi Campaign</h3>
</div>

<table class="data-table">

    <tbody>

        <tr>
            <th>Slug</th>
            <td>{{ $campaign->slug }}</td>
        </tr>

        <tr>
            <th>Penggalang Dana</th>
            <td>
                {{ $campaign->penggalangDana->nama_penggalang }}
            </td>
        </tr>

        <tr>
            <th>Target Donasi</th>
            <td>
                Rp {{ number_format($campaign->target_donasi, 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <th>Tanggal Mulai</th>
            <td>
                {{ \Carbon\Carbon::parse($campaign->tanggal_mulai)->format('d M Y') }}
            </td>
        </tr>

        <tr>
            <th>Tanggal Berakhir</th>
            <td>
                {{ \Carbon\Carbon::parse($campaign->tanggal_berakhir)->format('d M Y') }}
            </td>
        </tr>

    </tbody>

</table>
```

</div>

<div class="card">

```
<div class="card-header">
    <h3>Deskripsi Campaign</h3>
</div>

<div>
    {!! nl2br(e($campaign->deskripsi)) !!}
</div>
```

</div>

<div class="card">

```
<div class="card-header">
    <h3>Galeri Campaign</h3>
</div>

@if($campaign->campaignGambar->count())

    <div class="gallery-grid">

        @foreach($campaign->campaignGambar as $gambar)

            <div class="gallery-item">

                <img
                    src="{{ asset('storage/' . $gambar->foto) }}"
                    alt="Campaign"
                >

            </div>

        @endforeach

    </div>

@else

    <p class="text-muted">
        Tidak ada galeri.
    </p>

@endif
```

</div>

<div class="card">

```
<div class="card-header">
    <h3>Filter Campaign</h3>
</div>

@if($campaign->campaignFilter->count())

    @foreach($campaign->campaignFilter as $filter)

        <span
            class="badge badge-blue"
            style="margin-right:6px;"
        >
            {{ $filter->filter->nama_filter }}
        </span>

    @endforeach

@else

    <p class="text-muted">
        Tidak ada filter.
    </p>

@endif
```

</div>

<div class="card">

```
<div class="card-header">
    <h3>Update Campaign</h3>
</div>

<table class="data-table">

    <thead>

        <tr>
            <th>Judul</th>
            <th>Dibuat Oleh</th>
            <th>Tanggal</th>
        </tr>

    </thead>

    <tbody>

        @forelse($campaign->campaignUpdates as $update)

            <tr>

                <td>
                    {{ $update->judul_update }}
                </td>

                <td>
                    {{ $update->user->name ?? '-' }}
                </td>

                <td>
                    {{ $update->created_at->format('d M Y') }}
                </td>

            </tr>

        @empty

            <tr>

                <td colspan="3">
                    Belum ada update.
                </td>

            </tr>

        @endforelse

    </tbody>

</table>
```

</div>

<div class="card">

```
<div class="card-header">
    <h3>Fundraiser Pendukung</h3>
</div>

<table class="data-table">

    <thead>

        <tr>
            <th>Nama User</th>
        </tr>

    </thead>

    <tbody>

        @forelse($campaign->campaignFundraisers as $fundraiser)

            <tr>

                <td>
                    {{ $fundraiser->user->name }}
                </td>

            </tr>

        @empty

            <tr>

                <td>
                    Tidak ada fundraiser pendukung.
                </td>

            </tr>

        @endforelse

    </tbody>

</table>
```

</div>

@if(
$campaign->verified_by ||
$campaign->verified_at
)

<div class="card">

```
<div class="card-header">
    <h3>Riwayat Verifikasi</h3>
</div>

<table class="data-table">

    <tbody>

        <tr>

            <th>Diverifikasi Oleh</th>

            <td>
                {{ optional($campaign->verifier)->name ?? '-' }}
            </td>

        </tr>

        <tr>

            <th>Tanggal Verifikasi</th>

            <td>
                {{ optional($campaign->verified_at)->format('d M Y H:i') ?? '-' }}
            </td>

        </tr>

    </tbody>

</table>
```

</div>

@endif

<div class="card">

```
<div class="card-header">
    <h3>Aksi Verifikasi</h3>
</div>

<div class="form-actions">

    @if($campaign->status != 'approved')

        <form
            action="{{ route('admin.campaign.approve', $campaign) }}"
            method="POST"
        >
            @csrf
            @method('PATCH')

            <button
                type="submit"
                class="btn-primary"
            >
                Approve
            </button>

        </form>

    @endif

    @if($campaign->status != 'rejected')

        <form
            action="{{ route('admin.campaign.reject', $campaign) }}"
            method="POST"
        >
            @csrf
            @method('PATCH')

            <button
                type="submit"
                class="btn-danger"
            >
                Reject
            </button>

        </form>

    @endif

</div>
```

</div>

@endsection
