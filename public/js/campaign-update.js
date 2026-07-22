
    // Data updates (dari server)
    const updatesData = @json($campaign->campaignUpdates->map(function($update) {
        return [
            'id' => $update->id,
            'judul' => $update->judul_update,
            'isi' => $update->isi_update,
            'tanggal' => $update->created_at->translatedFormat('d F Y'),
            'gambar' => $update->campaign_update_gambar->map(function($gambar) {
                return asset('storage/' . $gambar->gambar_update);
            }),
            'isOwner' => {{ auth()->check() && $campaign->isOwner(Auth::id()) ? 'true' : 'false' }},
            'slug' => '{{ $campaign->slug }}',
            'deleteUrl' => '{{ route('campaign.update.destroy', ['slug' => $campaign->slug, 'id' => ':id']) }}'
        ];
    }));

    const modal = document.getElementById('updateModal');
    const modalBody = document.getElementById('updateModalBody');

    function openUpdateModal(id) {
        const update = updatesData.find(u => u.id === id);
        if (!update) return;

        modalBody.innerHTML = `
            <div class="update-detail">
                <h2 class="update-detail-title">${update.judul}</h2>
                <div class="update-detail-meta">
                    <span><i class="bi bi-calendar3"></i> ${update.tanggal}</span>
                </div>
                <div class="update-detail-body">
                    ${update.isi.replace(/\n/g, '<br>')}
                </div>
                ${update.gambar.length > 0 ? `
                    <div class="update-detail-gallery">
                        ${update.gambar.map(img => `
                            <div class="update-detail-gallery-item">
                                <img src="${img}" alt="Gambar update">
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
                ${update.isOwner ? `
                    <div class="update-detail-footer">
                        <form action="${update.deleteUrl.replace(':id', update.id)}" method="POST" onsubmit="return confirm('Yakin ingin menghapus update ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete-update">
                                <i class="bi bi-trash"></i>
                                Hapus Update
                            </button>
                        </form>
                    </div>
                ` : ''}
            </div>
        `;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeUpdateModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeUpdateModal();
        }
    });
