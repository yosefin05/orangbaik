@extends('layouts.admin')

@section('content')

    <div class="page-header">
        <div>
            <h2>Data User</h2>
            <p>Kelola seluruh pengguna terdaftar di platform OrangBaik.id</p>
        </div>
    </div>

    <div class="card">

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Nomor</th>
                        <th>Jenis Kelamin</th>
                        <th>Foto Profil</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($users as $user)

                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->nomor }}</td>
                            <td>
                                {{ $user->jenis_kelamin == 'L' ? 'Laki-laki' : ($user->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}
                            </td>

                            <td>
                                @if($user->foto_profil)
                                    <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="Foto Profil" class="table-avatar">
                                @else
                                    <span class="text-muted">Tidak ada</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge badge-{{ $user->role === 'admin' ? 'red' : 'blue' }}">
                                    {{ $user->role }}
                                </span>
                            </td>

                            <td>
                                <div class="action-group">
                                    <a href="{{ route('profile.edit', $user) }}" class="action-link link-yellow">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                        onsubmit="return confirm('Hapus user ini?')" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-link link-red">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="empty-state">
                                Belum ada data user.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>

    </div>

@endsection