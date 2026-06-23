@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <h2>{{ $berita->judul }}</h2>
        <p>Detail lengkap artikel berita</p>
    </div>

    <a href="{{ route('admin.berita.index') }}" class="btn-secondary">
        ← Kembali
    </a>
</div>

<div class="card detail-card">

    <img
        src="{{ asset('storage/' . $berita->thumbnail) }}"
        alt="{{ $berita->judul }}"
        class="detail-thumbnail"
    >

    <div class="detail-meta">

        <div class="meta-item">
            <span class="meta-label">Slug</span>
            <span class="badge badge-blue">{{ $berita->slug }}</span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Penulis</span>
            <span class="meta-value">{{ $berita->user->name }}</span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Tanggal Dibuat</span>
            <span class="meta-value">{{ $berita->created_at->format('d M Y, H:i') }}</span>
        </div>

        <div class="meta-item">
            <span class="meta-label">Terakhir Diperbarui</span>
            <span class="meta-value">{{ $berita->updated_at->format('d M Y, H:i') }}</span>
        </div>

    </div>

    <div class="detail-section">
        <h3>Isi Berita</h3>
        <div class="detail-content">
            {!! $berita->isi !!}
        </div>
    </div>

    @if($berita->gambar->count() > 0)
    <div class="detail-section">
        <h3>Galeri ({{ $berita->gambar->count() }} gambar)</h3>
        <div class="gallery-grid">

            @foreach($berita->gambar as $gambar)
            <div class="gallery-item">
                <img
                    src="{{ asset('storage/' . $gambar->gambar) }}"
                    alt="Galeri {{ $loop->iteration }}"
                >
            </div>
            @endforeach

        </div>
    </div>
    @endif

    <div class="form-actions">
        <a href="{{ route('admin.berita.edit', $berita) }}" class="btn-primary">
            Edit Berita
        </a>

        <form
            action="{{ route('admin.berita.destroy', $berita) }}"
            method="POST"
            onsubmit="return confirm('Yakin ingin menghapus berita ini?')"
            class="inline-form"
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger">
                Hapus Berita
            </button>
        </form>
    </div>

</div>

@endsection