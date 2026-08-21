@extends('layouts.admin')

@section('page-title', 'User')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Data User</h2>
                <p class="card-subtitle">
                    Kelola seluruh pengguna terdaftar di platform OrangBaik.id.
                </p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Nomor</th>
                        <th>Jenis Kelamin</th>
                        <th>Role</th>
                        <th>Status Verifikasi</th>  <!-- TAMBAHAN -->
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($users as $user)

                        <tr>
                            <td>
                                @if($user->foto_profil)
                                    <img src="{{ asset('storage/' . $user->foto_profil) }}" alt="{{ $user->name }}"
                                        class="table-avatar">
                                @else
                                    <div class="table-avatar table-avatar-placeholder">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <p class="cell-title">
                                    {{ $user->name }}
                                </p>
                            </td>

                            <td>
                                {{ $user->email }}
                            </td>

                            <td>
                                {{ $user->nomor ?? '-' }}
                            </td>

                            <td>
                                @if($user->jenis_kelamin === 'L')
                                    Laki-laki
                                @elseif($user->jenis_kelamin === 'P')
                                    Perempuan
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $user->role === 'admin' ? 'badge-red' : 'badge-blue' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <!-- STATUS VERIFIKASI -->
                            <td>
                                @if($user->email_verified_at)
                                    <span class="badge badge-green">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Terverifikasi
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $user->email_verified_at->format('d/m/Y H:i') }}
                                    </small>
                                @else
                                    <span class="badge badge-warning">
                                        <i class="bi bi-clock-fill"></i>
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="action-group action-group-center">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="action-link link-blue">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus user ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="action-link link-red">
                                            <i class="bi bi-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="empty-state">  <!-- Ubah colspan dari 7 ke 8 -->
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

    </section>

@endsection