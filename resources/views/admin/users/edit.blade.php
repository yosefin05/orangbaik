@extends('layouts.admin')

@section('content')

<div class="page-header">
    <div>
        <h2>Edit User</h2>
        <p>Perbarui informasi akun {{ $user->name }}</p>
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

    <form
        action="{{ route('admin.users.update', $user) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Nama</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                class="form-control"
            >
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                class="form-control"
            >
        </div>

        <div class="form-row">

            <div class="form-group">
                <label for="nomor">Nomor</label>
                <input
                    type="text"
                    id="nomor"
                    name="nomor"
                    value="{{ old('nomor', $user->nomor) }}"
                    class="form-control"
                >
            </div>

            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin</label>
                <select
                    id="jenis_kelamin"
                    name="jenis_kelamin"
                    class="form-control"
                >
                    <option value="Laki-laki" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>
                        Laki-laki
                    </option>
                    <option value="Perempuan" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>
                        Perempuan
                    </option>
                </select>
            </div>

        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select
                id="role"
                name="role"
                class="form-control"
            >
                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>
                    User
                </option>
                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                    Admin
                </option>
            </select>
        </div>

        @if($user->foto_profil)
            <div class="form-group">
                <label>Foto Profil Saat Ini</label>
                <img
                    src="{{ asset('storage/' . $user->foto_profil) }}"
                    alt="Foto Profil"
                    class="current-photo"
                >
            </div>
        @endif

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Simpan Perubahan
            </button>

            <a href="{{ route('admin.users.index') }}" class="btn-secondary">
                Batal
            </a>
        </div>

    </form>

</div>

@endsection