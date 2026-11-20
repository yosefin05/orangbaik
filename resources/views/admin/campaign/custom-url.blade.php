@extends('layouts.admin')

@section('page-title', 'Custom URL Campaign')

@section('content')
<section class="ob-card ob-card-lg">
    <div class="card-topbar">
        <div>
            <h2>Custom URL Campaign</h2>
            <p class="card-subtitle">Kelola URL aktif dan kembalikan campaign ke URL otomatis.</p>
        </div>
        <a href="{{ route('admin.campaign.index') }}" class="btn-primary">Kembali ke Campaign</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Campaign</th><th>URL otomatis</th><th>URL aktif</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->judul }}</td>
                    <td><code>/campaign/{{ $campaign->slug }}</code></td>
                    <td><code>{{ $campaign->custom_slug ? '/' . $campaign->custom_slug : '/campaign/' . $campaign->slug }}</code></td>
                    <td>{{ $campaign->custom_slug ? 'Custom URL aktif' : 'URL otomatis' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.campaign.custom-url.update', $campaign) }}" class="inline-form">
                            @csrf @method('PATCH')
                            <input name="custom_slug" value="{{ $campaign->custom_slug }}" placeholder="contoh: bantu-anak" pattern="[A-Za-z0-9_-]+" maxlength="120" required>
                            <button type="submit" class="action-link link-blue">Simpan</button>
                        </form>
                        @if($campaign->custom_slug)
                            <form method="POST" action="{{ route('admin.campaign.custom-url.destroy', $campaign) }}" class="inline-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-link link-red">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada campaign.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $campaigns->links() }}
</section>
@endsection
