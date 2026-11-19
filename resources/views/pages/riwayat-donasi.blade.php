<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Donasi - OrangBaik.id</title>

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/riwayat-donasi.css') }}?v=2">
</head>

<body>
    @php
        // Data dikirim dari controller: $formattedDonations, $totalDonasi, $totalNominal, $totalSelesai
        $donations = $formattedDonations ?? [];
        $formatRupiah = fn($value) => 'Rp' . number_format($value, 0, ',', '.');

        // presentasi saja: hitung dari data yang sudah ada
        $totalMenunggu = collect($donations)->where('status_key', 'menunggu')->count();
        $backUrl = url()->previous() && !str_contains(url()->previous(), 'riwayat-donasi')
            ? url()->previous()
            : route('profile.user');
    @endphp

    {{-- TOPBAR (sama dengan halaman donasi-bayar & instruksi; tidak ikut print) --}}
    <header class="history-topbar" id="history-topbar">
        <div class="history-topbar-inner">
            <a class="history-topbar-back" href="{{ $backUrl }}" aria-label="Kembali">
                <i class="bi bi-chevron-left"></i>
            </a>
            <div class="history-topbar-text">
                <h1 class="history-topbar-title">Riwayat Donasi</h1>
            </div>
        </div>
    </header>

    <main class="history-page page-wrapper">
        <div class="container">

            <section class="history-hero">
                <div class="history-hero-content">
                    <span class="section-label">
                        Donasi Saya
                    </span>

                    <h1 class="section-title">
                        Riwayat Donasi
                    </h1>

                    <p class="section-description">
                        Pantau semua donasi yang sudah kamu lakukan, lihat status transaksi,
                        dan akses bukti donasi dengan mudah.
                    </p>
                </div>

                <div class="history-hero-card">
                    <span>Total Donasi</span>
                    <strong>{{ $formatRupiah($totalNominal ?? 0) }}</strong>
                    <p>Dari {{ $totalDonasi ?? 0 }} transaksi donasi</p>
                </div>
            </section>

            <section class="history-summary" aria-label="Ringkasan riwayat donasi">
                <article class="summary-card">
                    <span class="summary-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 7H20V17H4V7Z" />
                            <path d="M7 10H7.01" />
                            <path d="M17 14H12" />
                        </svg>
                    </span>

                    <div>
                        <span>Transaksi</span>
                        <strong>{{ $totalDonasi ?? 0 }}</strong>
                    </div>
                </article>

                <article class="summary-card">
                    <span class="summary-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 6L9 17L4 12" />
                        </svg>
                    </span>

                    <div>
                        <span>Selesai</span>
                        <strong>{{ $totalSelesai ?? 0 }}</strong>
                    </div>
                </article>

                <article class="summary-card">
                    <span class="summary-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7V12L15 14" />
                        </svg>
                    </span>

                    <div>
                        <span>Menunggu</span>
                        <strong>{{ $totalMenunggu }}</strong>
                    </div>
                </article>
            </section>

            <section class="history-panel">
                <div class="history-toolbar">
                    <div>
                        <h2>Daftar Transaksi</h2>
                        <p>Riwayat donasi terbaru kamu di OrangBaik.id.</p>
                    </div>

                    <div class="history-filter" aria-label="Filter riwayat donasi">
                        <button class="filter-button active" type="button" data-filter="semua">
                            Semua
                        </button>

                        <button class="filter-button" type="button" data-filter="selesai">
                            Selesai
                        </button>

                        <button class="filter-button" type="button" data-filter="menunggu">
                            Menunggu
                        </button>

                        <button class="filter-button" type="button" data-filter="gagal">
                            Gagal
                        </button>
                    </div>
                </div>

                @if (!empty($donations))
                    <div class="history-list">
                        @foreach ($donations as $donation)
                            <article class="history-item" data-status="{{ $donation['status_key'] }}">
                                <div class="history-item-main">
                                    <div class="history-image-wrap">
                                        <img src="{{ asset('storage/' . $donation['image']) }}" alt="{{ $donation['title'] }}"
                                            class="history-image">
                                    </div>

                                    <div class="history-info">
                                        <div class="history-info-top">
                                            <span class="history-type">
                                                {{ $donation['type'] }}
                                            </span>

                                            <span class="status-badge status-{{ $donation['status_key'] }}">
                                                {{ $donation['status'] }}
                                            </span>
                                        </div>

                                        <h3>{{ $donation['title'] }}</h3>

                                        <p class="history-organizer">
                                            {{ $donation['organizer'] }}
                                        </p>

                                        <div class="history-detail">
                                            <span>{{ $donation['date'] }}</span>
                                            <span>{{ $donation['method'] }}</span>
                                            <span>{{ $donation['invoice'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="history-history">
                                    <span>Nominal</span>
                                    <strong>{{ $donation['amount'] }}</strong>

                                    @if ($donation['status_key'] === 'selesai')
                                        <a href="{{ route('riwayat-donasi.kwitansi', ['donasi' => $donation['id']]) }}"
                                            class="receipt-button" target="_blank">
                                            Lihat Bukti
                                        </a>
                                    @elseif ($donation['status_key'] === 'menunggu' && !empty($donation['resume_url']))
                                        <a href="{{ $donation['resume_url'] }}"
                                            class="resume-button btn-resume"
                                            data-resume-url="{{ $donation['resume_url'] }}">
                                            Lanjutkan Bayar
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="history-empty-filter" data-empty-filter>
                        <h3>Belum ada data pada filter ini</h3>
                        <p>Coba pilih filter lain untuk melihat riwayat donasi kamu.</p>
                    </div>
                @else
                    <div class="history-empty">
                        <h2>Belum ada riwayat donasi</h2>
                        <p>
                            Donasi yang kamu lakukan akan muncul di halaman ini.
                        </p>

                        <a href="{{ url('/donasi') }}" class="empty-button">
                            Mulai Donasi
                        </a>
                    </div>
                @endif
            </section>

        </div>
    </main>

    @include('components.footer')

    @php
        $isMidtransProd = config('history.midtrans.is_production', config('midtrans.isProduction', false));
        $midtransClientKey = config('history.midtrans.client_key', config('midtrans.clientKey', ''));
    @endphp
    @if (!empty($midtransClientKey))
        <script src="{{ $isMidtransProd ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ $midtransClientKey }}"></script>
    @endif

    <script src="{{ asset('js/riwayat.js') }}"></script>

    <script>
    document.querySelectorAll('.btn-resume').forEach(function (btn) {
        btn.addEventListener('click', async function (e) {
            const resumeUrl = this.getAttribute('data-resume-url');

            if (!resumeUrl) {
                return;
            }

            e.preventDefault();

            const originalText = this.textContent;

            this.textContent = 'Memproses...';
            this.style.pointerEvents = 'none';

            try {
                const res = await fetch(resumeUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await res.json();

                if (!res.ok) {
                    throw new Error(
                        data.message || 'Gagal melanjutkan pembayaran.'
                    );
                }

                /*
                 * MIDTRANS
                 *
                 * Resume mengambil snap_token langsung
                 * dari database melalui DonasiController@resume.
                 */
                if (
                    data.type === 'midtrans' &&
                    data.snap_token &&
                    typeof snap !== 'undefined'
                ) {
                    snap.pay(data.snap_token, {

                        onSuccess: function () {
                            window.location.href =
                                '{{ route("donasi.status", "sukses") }}';
                        },

                        onPending: function () {
                            window.location.href =
                                '{{ route("donasi.status", "pending") }}';
                        },

                        onError: function () {
                            window.location.href =
                                '{{ route("donasi.status", "gagal") }}';
                        },

                        onClose: function () {
                            btn.textContent = originalText;
                            btn.style.pointerEvents = 'auto';
                        }
                    });

                    return;
                }

                /*
                 * FLIP / MANUAL / GATEWAY LAIN
                 */
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }

                /*
                 * Fallback
                 */
                window.location.href = resumeUrl;

            } catch (err) {
                console.error('Resume history error:', err);

                btn.textContent = originalText;
                btn.style.pointerEvents = 'auto';

                alert(
                    err.message ||
                    'Terjadi kesalahan saat melanjutkan pembayaran.'
                );
            }
        });
    });
</script>

</body>

</html>