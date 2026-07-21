@extends('layouts.admin')

@section('page-title', 'Detail Berita')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/berita/show.css') }}">
@endpush

@section('content')

    <section class="ob-card ob-card-lg detail-card">

        <div class="card-topbar">
            <div>
                <h2>{{ $berita->judul }}</h2>
                <p class="card-subtitle">
                    Detail lengkap artikel berita yang ditampilkan pada website.
                </p>
            </div>

            <a href="{{ route('admin.berita.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        @if ($berita->thumbnail)
            <img
                src="{{ asset('storage/' . $berita->thumbnail) }}"
                alt="{{ $berita->judul }}"
                class="detail-thumbnail"
            />
        @else
            <div class="detail-thumbnail detail-thumbnail-placeholder">
                <i class="bi bi-image"></i>
                <span>Tidak ada thumbnail</span>
            </div>
        @endif

        <div class="detail-meta">

            <div class="meta-item">
                <span class="meta-label">Slug</span>
                <span class="badge badge-blue">
                    {{ $berita->slug }}
                </span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Penulis</span>
                <span class="meta-value">
                    {{ $berita->user->name ?? 'User tidak ditemukan' }}
                </span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Tanggal Dibuat</span>
                <span class="meta-value">
                    {{ $berita->created_at->format('d M Y, H:i') }}
                </span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Terakhir Diperbarui</span>
                <span class="meta-value">
                    {{ $berita->updated_at->format('d M Y, H:i') }}
                </span>
            </div>

        </div>

        <div class="detail-section">
            <h3>Isi Berita</h3>

            <div class="detail-content">
                {!! $berita->isi !!}
            </div>
        </div>

        <div class="detail-section">
            <h3>Galeri Berita</h3>

            @if ($berita->gambar->count() > 0)
                <div class="gallery-grid">
                    @foreach ($berita->gambar as $gambar)
                        <div class="gallery-item">
                            <img
                                src="{{ asset('storage/' . $gambar->gambar) }}"
                                alt="Galeri {{ $loop->iteration }}"
                            />
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">
                    Tidak ada galeri.
                </p>
            @endif
        </div>

        <div class="form-actions">

            <a href="{{ route('admin.berita.edit', $berita) }}" class="btn-primary">
                <i class="bi bi-pencil-square"></i>
                <span>Edit Berita</span>
            </a>

            <form
                action="{{ route('admin.berita.destroy', $berita) }}"
                method="POST"
                class="inline-form"
                onsubmit="return confirm('Yakin ingin menghapus berita ini?')"
            >
                @csrf
                @method('DELETE')

                <button type="submit" class="btn-danger">
                    <i class="bi bi-trash"></i>
                    <span>Hapus Berita</span>
                </button>
            </form>

        </div>

    </section>

@endsection