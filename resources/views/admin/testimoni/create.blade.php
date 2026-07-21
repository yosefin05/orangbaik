@extends('layouts.admin')

@section('page-title', 'Tambah Testimoni')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/testimoni/create.css') }}">
@endpush

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>Tambah Testimoni</h2>
                <p class="card-subtitle">
                    Tambahkan testimoni baru yang akan ditampilkan pada website OrangBaik.id.
                </p>
            </div>

            <a href="{{ route('admin.testimoni.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <form
            action="{{ route('admin.testimoni.store') }}"
            method="POST"
            enctype="multipart/form-data"
            id="testimoniForm"
        >

            @csrf

            <div class="form-wrapper">

                {{-- FOTO PROFIL --}}
                <div class="form-group">
                    <label for="foto_profil">Foto Profil <span class="text-danger">*</span></label>

                    <div class="upload-dropzone" id="foto_profilDropzone">
                        
                        {{-- Placeholder --}}
                        <div class="upload-placeholder" id="foto_profilPlaceholder">
                            <i class="bi bi-person-circle"></i>
                            <p>Klik atau seret foto profil</p>
                            <span class="text-muted">Format: JPG, PNG, JPEG • Maks: 2MB</span>
                        </div>

                        {{-- PREVIEW - PAKAI avatar-preview --}}
                        <div class="upload-preview avatar-preview" id="foto_profilPreview" style="display: none;">
                            <div class="avatar-wrapper">
                                <img id="foto_profilPreviewImg" src="#" alt="Foto Preview" />
                            </div>
                            <button type="button" class="btn-remove-avatar" id="foto_profilRemoveBtn" title="Hapus foto">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        {{-- Tombol Upload --}}
                        <div class="upload-btn-wrapper" id="uploadBtnWrapper">
                            <button type="button" class="upload-btn" onclick="document.getElementById('foto_profil').click()">
                                <i class="bi bi-cloud-upload"></i>
                                <span>Pilih Foto</span>
                            </button>
                            <span class="upload-hint">atau seret gambar ke sini</span>
                        </div>

                        <input
                            type="file"
                            id="foto_profil"
                            name="foto_profil"
                            class="upload-input-hidden"
                            accept="image/*"
                            required
                        />
                    </div>

                    @error('foto_profil')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ====================================================== --}}
                {{-- NAMA --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="nama">Nama <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="{{ old('nama') }}"
                        placeholder="Masukkan nama"
                        class="form-control"
                        required
                    />
                    @error('nama')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ====================================================== --}}
                {{-- JABATAN --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="jabatan">Jabatan <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="jabatan"
                        name="jabatan"
                        value="{{ old('jabatan') }}"
                        placeholder="Contoh: Donatur Tetap"
                        class="form-control"
                        required
                    />
                    @error('jabatan')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ====================================================== --}}
                {{-- ISI TESTIMONI --}}
                {{-- ====================================================== --}}
                <div class="form-group">
                    <label for="isi_testimoni">Isi Testimoni <span class="text-danger">*</span></label>
                    <textarea
                        id="isi_testimoni"
                        name="isi_testimoni"
                        rows="5"
                        placeholder="Masukkan isi testimoni"
                        class="form-control"
                        required
                    >{{ old('isi_testimoni') }}</textarea>
                    @error('isi_testimoni')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- ========================================================== --}}
            {{-- FORM ACTIONS --}}
            {{-- ========================================================== --}}
            <div class="form-footer">
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save"></i>
                        <span>Simpan Testimoni</span>
                    </button>

                    <a href="{{ route('admin.testimoni.index') }}" class="btn-secondary">
                        <i class="bi bi-x-circle"></i>
                        Batal
                    </a>
                </div>
            </div>

        </form>

    </section>

@endsection
