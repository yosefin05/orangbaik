@extends('layouts.admin')

@section('page-title', 'Tambah Filter')

@section('content')

<div class="page-header">
    <div>
        <h2>Tambah Filter</h2>
        <p>Buat kategori filter baru untuk campaign</p>
    </div>
</div>

<div class="card form-card">

    @if ($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.filter.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="nama_filter">Nama Filter</label>
            <input
                type="text"
                id="nama_filter"
                name="nama_filter"
                value="{{ old('nama_filter') }}"
                class="form-control"
                placeholder="Contoh: Bencana Alam"
            >
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Simpan
            </button>

            <a href="{{ route('admin.filter.index') }}" class="btn-secondary">
                Batal
            </a>
        </div>

    </form>

</div>

@endsection