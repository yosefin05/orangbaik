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

            <a href="{{ route('admin.users.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        {{-- ========================================================== --}}
        {{-- FORM                                                      --}}
        {{-- ========================================================== --}}
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="form-wrapper">

            @csrf
            @method('PUT')

            {{-- Row 1: Nama & Email --}}
            <div class="form-row">

                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="{{ old('name', $user->name) }}"
                        placeholder="Masukkan nama lengkap"
                        required
                    />
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email', $user->email) }}"
                        placeholder="Masukkan alamat email"
                        required
                    />
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Row 2: Nomor & Jenis Kelamin --}}
            <div class="form-row">

                <div class="form-group">
                    <label for="nomor">Nomor Telepon</label>
                    <input
                        type="text"
                        id="nomor"
                        name="nomor"
                        class="form-control"
                        value="{{ old('nomor', $user->nomor) }}"
                        placeholder="Masukkan nomor telepon"
                    />
                    @error('nomor')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="form-control">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                            Laki-laki
                        </option>
                        <option value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                            Perempuan
                        </option>
                    </select>
                    @error('jenis_kelamin')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Row 3: Role --}}
            <div class="form-row">

                <div class="form-group">
                    <label for="role">Role <span class="text-danger">*</span></label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>
                            User
                        </option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                            Admin
                        </option>
                    </select>
                    @error('role')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    {{-- Kosong atau bisa diisi dengan info tambahan --}}
                    <label>&nbsp;</label>
                    <div class="text-muted" style="padding-top: 8px;">
                        <i class="bi bi-info-circle"></i>
                        Role menentukan akses user ke sistem
                    </div>
                </div>

            </div>

            {{-- ========================================================== --}}
            {{-- FORM ACTIONS                                               --}}
            {{-- ========================================================== --}}
            <div class="form-actions">

                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Simpan Perubahan
                </button>

                <a href="{{ route('admin.users.index') }}" class="btn-secondary">
                    <i class="bi bi-x-circle"></i>
                    Batal
                </a>

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