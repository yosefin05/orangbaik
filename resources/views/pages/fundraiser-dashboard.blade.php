@extends('layouts.app')

@section('content')
<main class="container py-5">
    <h1>Dashboard Fundraiser</h1>
    <div class="row g-3 my-4">
        <div class="col-md-6"><div class="card p-4"><strong>{{ $totalDonatur }}</strong><span>Transaksi donasi berhasil</span></div></div>
        <div class="col-md-6"><div class="card p-4"><strong>Rp {{ number_format($totalDonasi, 0, ',', '.') }}</strong><span>Total donasi (settlement)</span></div></div>
    </div>
    <div class="card p-4">
        <h2>Campaign yang Diikuti</h2>
        @forelse($fundraisers as $fundraiser)
            <div class="d-flex justify-content-between border-bottom py-3">
                <a href="{{ $fundraiser->campaign?->public_url }}">{{ $fundraiser->campaign?->judul ?? '-' }}</a>
                <span>Rp {{ number_format($fundraiser->donasis->sum('nominal'), 0, ',', '.') }}</span>
            </div>
        @empty
            <p>Belum ada campaign yang diikuti sebagai fundraiser.</p>
        @endforelse
    </div>
</main>
@endsection
