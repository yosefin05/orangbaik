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

                </div>
            </div>

        </form>

    </section>

@endsection