@php
    // Ambil hanya donasi yang pembayarannya sudah settlement
    $donasiSettlement = $campaign->donasi->filter(function ($donasi) {
        return $donasi->pembayaran
            && $donasi->pembayaran->transaction_status === 'settlement';
    });

    $terkumpul = $donasiSettlement->sum('nominal');
    $totalDonatur = $donasiSettlement->count();

    $persen = $campaign->target_donasi
        ? min(100, ($terkumpul / $campaign->target_donasi) * 100)
        : 0;

    // Hitung sisa hari campaign
    $now = \Carbon\Carbon::now();

    $end = $campaign->tanggal_berakhir
        ? \Carbon\Carbon::parse($campaign->tanggal_berakhir)
        : null;

    $diff = $end
        ? (int) $now->diffInDays($end, false)
        : null;

    if ($diff === null) {
        $labelHari = 'Tanpa batas';
    } elseif ($diff < 0) {
        $labelHari = 'Selesai';
    } elseif ($diff === 0) {
        $labelHari = 'Hari terakhir';
    } else {
        $labelHari = $diff . ' Hari';
    }
@endphp

<article class="campaign-card">
    <a
        href="{{ route('campaign.show', $campaign->custom_slug ?? $campaign->slug) }}"
        class="campaign-card-link"
    >
        <img
            class="campaign-image"
            src="{{ asset('storage/' . $campaign->thumbnail) }}"
            alt="{{ $campaign->judul }}"
            loading="lazy"
        >
    </a>

    <div class="campaign-body">
        <h3>{{ $campaign->judul }}</h3>

        <p>
            {{ $campaign->penggalangDana->nama_penggalang }}
            <span>●</span>
        </p>

        <div class="campaign-price">
            <strong>
                Rp {{ number_format($terkumpul, 0, ',', '.') }}
            </strong>
            <span>Terkumpul</span>
        </div>

        <div class="progress">
            <div
                class="progress-fill"
                style="width: {{ $persen }}%;"
            ></div>
        </div>

        <div class="campaign-meta">
            <span>{{ $totalDonatur }} Donatur</span>
            <span>{{ $labelHari }}</span>
        </div>
    </div>
</article>
