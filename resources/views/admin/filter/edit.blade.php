@extends('layouts.admin')

@section('page-title', 'Edit Filter')

@section('content')

<div class="page-header">
    <div>
        <h2>Edit Filter</h2>
        <p>Perbarui data filter "{{ $filter->nama_filter }}"</p>
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

    <form action="{{ route('admin.filter.update', $filter) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="nama_filter">Nama Filter</label>
            <input
                type="text"
                id="nama_filter"
                name="nama_filter"
                value="{{ old('nama_filter', $filter->nama_filter) }}"
                class="form-control"
            >
        </div>

        <div class="form-group">
            <label>Slug Saat Ini</label>
            <input
                type="text"
                value="{{ $filter->slug }}"
                class="form-control"
                disabled
            >
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Simpan Perubahan
            </button>

            <a href="{{ route('admin.filter.index') }}" class="btn-secondary">
                Batal
            </a>
        </div>

    </form>

</div>

@endsection