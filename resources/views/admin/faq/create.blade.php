@extends('layouts.admin')

@section('page-title', 'Tambah FAQ')

@section('content')

    <section class="ob-card ob-card-lg form-card">

        <div class="card-topbar">
            <div>
                <h2>Tambah FAQ</h2>
                <p class="card-subtitle">
                    Tambahkan pertanyaan yang akan tampil di halaman pusat bantuan, syarat & ketentuan, dan tentang.
                </p>
            </div>

            <a href="{{ route('admin.faq.index') }}" class="btn-secondary">
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

        <form action="{{ route('admin.faq.store') }}" method="POST">
            @csrf

            <div class="form-wrapper">
                <div class="form-group">
                    <label for="pertanyaan">Pertanyaan</label>
                    <input
                        type="text"
                        id="pertanyaan"
                        name="pertanyaan"
                        value="{{ old('pertanyaan') }}"
                        placeholder="Masukkan pertanyaan"
                        class="form-control"
                        required>
                </div>

                <div class="form-group">
                    <label for="jawaban">Jawaban</label>
                    <textarea
                        id="jawaban"
                        name="jawaban"
                        rows="6"
                        placeholder="Masukkan jawaban"
                        class="form-control"
                        required>{{ old('jawaban') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="urutan">Urutan</label>
                    <input
                        type="number"
                        id="urutan"
                        name="urutan"
                        min="0"
                        value="{{ old('urutan') }}"
                        placeholder="Kosongkan untuk otomatis"
                        class="form-control">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        Tampilkan di halaman publik
                    </label>
                </div>
            </div>

            <div class="form-footer">
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-save"></i>
                        <span>Simpan FAQ</span>
                    </button>
                    <a href="{{ route('admin.faq.index') }}" class="btn-secondary">Batal</a>
                </div>
            </div>
        </form>

    </section>

@endsection
