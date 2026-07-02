@extends('layouts.admin')

@section('page-title', 'Edit Filter')

@section('content')

    <section class="ob-card ob-card-lg form-card">

        <div class="card-topbar">
            <div>
                <h2>Edit Filter</h2>
                <p class="card-subtitle">
                    Perbarui data filter "{{ $filter->nama_filter }}" yang digunakan pada campaign.
                </p>
            </div>

            <a href="{{ route('admin.filter.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

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

            <div class="form-wrapper">

                <div class="form-group">
                    <label for="nama_filter">Nama Filter</label>

                    <input
                        type="text"
                        id="nama_filter"
                        name="nama_filter"
                        value="{{ old('nama_filter', $filter->nama_filter) }}"
                        placeholder="Masukkan nama filter"
                        class="form-control"
                        required>
                </div>

                <div class="form-group">
                    <label for="slug">Slug Saat Ini</label>

                    <input
                        type="text"
                        id="slug"
                        value="{{ $filter->slug }}"
                        class="form-control"
                        disabled>
                </div>

            </div>

            <div class="form-footer">
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save"></i>
                        <span>Simpan Perubahan</span>
                    </button>

                    <a href="{{ route('admin.filter.index') }}" class="btn-secondary">
                        Batal
                    </a>
                </div>
            </div>

        </form>

    </section>

@endsection