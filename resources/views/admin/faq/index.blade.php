@extends('layouts.admin')

@section('page-title', 'FAQ')

@section('content')

    <section class="ob-card ob-card-lg">

        <div class="card-topbar">
            <div>
                <h2>FAQ</h2>
                <p class="card-subtitle">
                    Kelola pertanyaan yang sering diajukan di halaman publik OrangBaik.id.
                </p>
            </div>

            <a href="{{ route('admin.faq.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah FAQ</span>
            </a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Pertanyaan</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                        <tr>
                            <td>{{ $faq->urutan }}</td>
                            <td>
                                <p class="cell-title">{{ $faq->pertanyaan }}</p>
                                <p class="cell-excerpt">{{ \Illuminate\Support\Str::limit($faq->jawaban, 80) }}</p>
                            </td>
                            <td>
                                <span class="badge {{ $faq->is_active ? 'badge-blue' : 'badge-red' }}">
                                    {{ $faq->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="action-group">
                                    <a href="{{ route('admin.faq.edit', $faq) }}" class="action-link link-yellow">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit
                                    </a>
                                    <form
                                        action="{{ route('admin.faq.destroy', $faq) }}"
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm('Yakin ingin menghapus FAQ ini?')">
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
                            <td colspan="4" class="empty-state text-center py-4">
                                <div class="empty-state-content">
                                    <i class="bi bi-question-circle text-muted" style="font-size: 2rem;"></i>
                                    <p class="mt-2 text-muted fw-semibold">Belum ada FAQ yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $faqs->firstItem() ?? 0 }}</strong> - <strong>{{ $faqs->lastItem() ?? 0 }}</strong> dari <strong>{{ $faqs->total() }}</strong> FAQ
            </div>
            <div class="pagination-links">
                {{ $faqs->links() }}
            </div>
        </div>

    </section>

@endsection
