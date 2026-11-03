@props([
    'campaign',
    'variant' => 'default',
])

@php
    // ========================================
    // STATISTIK DONASI
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
    // INFORMASI PENGGALANG
    // ========================================

    $namaPenggalang = $campaign->penggalangDana->nama_penggalang;
    $inisialPenggalang = mb_strtoupper(mb_substr($namaPenggalang, 0, 1));

    // ========================================
    // URL CAMPAIGN
    // ========================================

    $campaignUrl = route(
        'campaign.show',
        $campaign->custom_slug ?? $campaign->slug
    );
@endphp


@if ($variant === 'list')

    {{-- ========================================
        LIST CARD (HORIZONTAL)
    ======================================== --}}

    <a
        class="list-card"
        href="{{ $campaignUrl }}"
        aria-label="{{ $campaign->judul }}"
    >
        <div class="list-thumb">
            <img
                src="{{ asset('storage/' . $campaign->thumbnail) }}"
                alt="{{ $campaign->judul }}"
                loading="lazy"
            >

            <span class="list-badge {{ $badgeClass }}">
                @if ($diff === null)
                    <i
                        class="bi bi-infinity"
                        aria-label="Tanpa batas"
                        title="Tanpa batas"
                    ></i>
                @else
                    <i class="bi bi-clock"></i>
                    {{ $labelHari }}
                @endif
            </span>
        </div>

        <div class="list-body">
            <h3>{{ $campaign->judul }}</h3>

            <p class="list-org">
                <span class="list-org-avatar">
                    {{ $inisialPenggalang }}
                </span>

                <span class="list-org-name">
                    {{ $namaPenggalang }}
                </span>
            </p>

            <div class="list-amount">
                <strong>
                    Rp {{ number_format($terkumpul, 0, ',', '.') }}
                </strong>

                <small>
                    terkumpul{{ $campaign->target_donasi
                        ? ' dari Rp ' . number_format($campaign->target_donasi, 0, ',', '.')
                        : ''
                    }}
                </small>
            </div>

            <div class="list-progress">
                <div class="progress">
                    <div
                        class="progress-fill"
                        style="width: {{ $persen }}%;"
                    ></div>
                </div>

                <span class="list-percent">
                    {{ floor($persen) }}%
                </span>
            </div>

            <div class="campaign-meta">
                <span>
                    <i class="bi bi-people-fill"></i>
                    {{ $totalDonatur }} Donatur
                </span>

                <span class="meta-days">
                    @if ($diff === null)
                        <i
                            class="bi bi-infinity"
                            aria-label="Tanpa batas"
                            title="Tanpa batas"
                        ></i>
                    @else
                        {{ $labelHari }}
                    @endif
                </span>

                <span class="list-cta">
                    Lihat detail
                    <i class="bi bi-arrow-right"></i>
                </span>
            </div>
        </div>
    </a>

@else

    {{-- ========================================
        DEFAULT CARD (VERTICAL)
    ======================================== --}}

    <a
        class="campaign-card"
        href="{{ $campaignUrl }}"
        aria-label="{{ $campaign->judul }}"
    >
        <div class="campaign-image-wrapper">
            <img
                class="campaign-image"
                src="{{ asset('storage/' . $campaign->thumbnail) }}"
                alt="{{ $campaign->judul }}"
                loading="lazy"
            >

            <span class="list-badge {{ $badgeClass }}">
                @if ($diff === null)
                    <i
                        class="bi bi-infinity"
                        aria-label="Tanpa batas"
                        title="Tanpa batas"
                    ></i>
                @else
                    <i class="bi bi-clock"></i>
                    {{ $labelHari }}
                @endif
            </span>
        </div>

        <div class="campaign-body">
            <h3>{{ $campaign->judul }}</h3>

            <p class="list-org">
                <span class="list-org-avatar">
                    {{ $inisialPenggalang }}
                </span>

                <span class="list-org-name">
                    {{ $namaPenggalang }}
                </span>
            </p>

            <div class="campaign-price">
                <strong>
                    Rp {{ number_format($terkumpul, 0, ',', '.') }}
                </strong>

                <span>
                    terkumpul{{ $campaign->target_donasi
                        ? ' dari Rp ' . number_format($campaign->target_donasi, 0, ',', '.')
                        : ''
                    }}
                </span>
            </div>

            <div class="list-progress">
                <div class="progress">
                    <div
                        class="progress-fill"
                        style="width: {{ $persen }}%;"
                    ></div>
                </div>

                <span class="list-percent">
                    {{ floor($persen) }}%
                </span>
            </div>

            <div class="campaign-meta">
                <span>
                    <i class="bi bi-people-fill"></i>
                    {{ $totalDonatur }} Donatur
                </span>

                <span class="meta-days">
                    @if ($diff === null)
                        <i
                            class="bi bi-infinity"
                            aria-label="Tanpa batas"
                            title="Tanpa batas"
                        ></i>
                    @else
                        {{ $labelHari }}
                    @endif
                </span>

                <span class="list-cta">
                    Lihat detail
                    <i class="bi bi-arrow-right"></i>
                </span>
            </div>
        </div>
    </a>

@endif