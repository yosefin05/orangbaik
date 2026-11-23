@extends('layouts.admin')

@section('page-title', 'Tambah Berita')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/berita/create.css') }}">
@endpush

@section('content')

    {{-- ========================================================== --}}
    {{-- HEADER CARD --}}
    {{-- ========================================================== --}}
    <section class="ob-card ob-card-lg form-card">

        <div class="card-topbar">
            <div>
                <h2>Tambah Berita</h2>
                <p class="card-subtitle">
                    Buat artikel atau berita baru yang akan ditampilkan pada website OrangBaik.id.
                </p>
            </div>


                </div>
            </div>

        </form>

    </section>

    @push('scripts')
        <script src="{{ asset('js/admin/berita-create.js') }}"></script>
    @endpush

@endsection