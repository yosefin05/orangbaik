@props([
    'campaign',
    'variant' => 'default',
])

@php
    // ========================================
    // STATISTIK DONASI (hanya status settlement)
    // ========================================

    $donasiSettlement = $campaign->donasi->filter(function ($donasi) {
        return $donasi->pembayaran
            && $donasi->pembayaran->transaction_status === 'settlement';
    });

    $terkumpul = $donasiSettlement->sum('nominal');
    $totalDonatur = $donasiSettlement->count();

    $persen = $campaign->target_donasi
        ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
        : 0;

    $tercapai = $persen >= 100;

    // ========================================
    // SISA WAKTU CAMPAIGN
    // ========================================

    $end = $campaign->tanggal_berakhir
        ? \Carbon\Carbon::parse($campaign->tanggal_berakhir)
        : null;

    $diff = $end
        ? (int) now()->diffInDays($end, false)
        : null;

    $labelHari = match (true) {
        $diff === null => null,
        $diff < 0 => 'Selesai',
        $diff === 0 => 'Hari terakhir',
        default => $diff . ' Hari',
    };

    $badgeClass = match (true) {
        $diff === null => '',
        $diff < 0 => 'is-done',
        $diff <= 3 => 'is-urgent',
        default => '',
    };

    // ========================================
    // PENGGALANG DANA
    // Foto profil dipakai bila ada, jika tidak tampil inisial.
    // Sesuaikan nama kolom foto di bawah bila berbeda.
    // ========================================

    $penggalang = $campaign->penggalangDana;
    $namaPenggalang = $penggalang?->nama_penggalang ?? 'OrangBaik';
    $fotoPenggalang = $penggalang?->foto_profil;
    $inisialPenggalang = mb_strtoupper(mb_substr($namaPenggalang, 0, 1));

    // ========================================
    // URL & VARIANT
    // ========================================

    $campaignUrl = route(
        'campaign.show',
        $campaign->custom_slug ?? $campaign->slug
    );

    $isList = $variant === 'list';
@endphp

{{-- Satu struktur untuk dua variant:
default = card vertikal, list = card horizontal (.is-list) --}}
<a class="campaign-card {{ $isList ? 'is-list' : '' }}" href="{{ $campaignUrl }}" aria-label="{{ $campaign->judul }}">
    {{-- THUMBNAIL --}}
    <div class="campaign-thumb">
        <img src="{{ asset('storage/' . $campaign->thumbnail) }}" alt="{{ $campaign->judul }}" loading="lazy">

        <span class="campaign-badge {{ $badgeClass }}">
            @if ($diff === null)
                <i class="bi bi-infinity" title="Tanpa batas" aria-label="Tanpa batas"></i>
            @else
                <i class="bi bi-clock"></i>
                {{ $labelHari }}
            @endif
        </span>
    </div>

    {{-- ISI --}}
    <div class="campaign-body">
        <h3 class="campaign-title">{{ $campaign->judul }}</h3>

        <div class="campaign-amount">
            <strong>Rp {{ number_format($terkumpul, 0, ',', '.') }}</strong>

            <small>
                terkumpul<span class="campaign-target">{{ $campaign->target_donasi
    ? ' dari Rp ' . number_format($campaign->target_donasi, 0, ',', '.')
    : ''
    }}</span>
            </small>
        </div>

        <div class="campaign-progress {{ $tercapai ? 'is-complete' : '' }}">
            <div class="progress">
                <div class="progress-fill" style="width: {{ $persen }}%;"></div>
            </div>

            <span class="campaign-percent">{{ floor($persen) }}%</span>
        </div>

        {{-- FOOTER: donatur (kiri) • penggalang (kanan) --}}
        <div class="campaign-footer">
            <span class="campaign-donors" title="{{ $totalDonatur }} donatur">
                <i class="bi bi-people-fill"></i>
                <b>{{ $totalDonatur }}</b>
                <span class="campaign-donors-label">Donatur</span>
            </span>

            <span class="campaign-org" title="{{ $namaPenggalang }}">
                <span class="campaign-org-avatar">
                    {{ $inisialPenggalang }}
                    @if ($fotoPenggalang)
                        <img src="{{ asset('storage/' . $fotoPenggalang) }}" alt="" loading="lazy" onerror="this.remove()">
                    @endif
                </span>

                <span class="campaign-org-name">{{ $namaPenggalang }}</span>
            </span>
        </div>
    </div>
</a>