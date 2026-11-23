@extends('layouts.admin')

@section('page-title', 'Edit Berita')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/berita/edit.css') }}">
@endpush

@section('content')

    <section class="ob-card ob-card-lg form-card">

        <div class="card-topbar">
            <div>
                <h2>Edit Berita</h2>
                <p class="card-subtitle">
                    Perbarui artikel "{{ $berita->judul }}" yang ditampilkan pada website.
                </p>
            </div>


                </div>
            </div>

        </form>

    </section>

@endsection