@extends('layouts.admin')

@section('page-title', 'Edit User: ' . $user->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/users/show.css') }}">
@endpush

@section('content')

    {{-- ========================================================== --}}
    {{-- HEADER CARD                                                --}}
    {{-- ========================================================== --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Edit User</h2>
                <p class="card-subtitle">
                    Edit data user <strong>{{ $user->name }}</strong>
                </p>
            </div>


            </div>

        </form>

    </section>

    {{-- ========================================================== --}}
    {{-- INFO TAMBAHAN (Opsional)                                   --}}
    {{-- ========================================================== --}}
    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Informasi Akun</h2>
                <p class="card-subtitle">
                    Detail tambahan tentang akun user.
                </p>
            </div>
        </div>

        <table class="data-table data-table-kv">
            <tbody>
                <tr>
                    <th>ID User</th>
                    <td>#{{ $user->id }}</td>
                </tr>

                <tr>
                    <th>Bergabung Sejak</th>
                    <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                </tr>

                <tr>
                    <th>Terakhir Diperbarui</th>
                    <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                </tr>

                @if($user->email_verified_at)
                    <tr>
                        <th>Email Diverifikasi</th>
                        <td>
                            <span class="badge badge-green">
                                <i class="bi bi-check-circle-fill"></i>
                                {{ $user->email_verified_at->format('d M Y H:i') }}
                            </span>
                        </td>
                    </tr>
                @else
                    <tr>
                        <th>Email Diverifikasi</th>
                        <td>
                            <span class="badge badge-red">
                                <i class="bi bi-x-circle-fill"></i>
                                Belum Diverifikasi
                            </span>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

    </section>

@endsection