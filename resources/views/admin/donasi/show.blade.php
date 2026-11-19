@extends('layouts.admin')

@section('page-title', 'Detail Donasi #' . $donasi->id)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/donasi/show.css') }}">
@endpush

@section('content')
    @php
        $p  = $donasi->pembayaran;
        $st = $p?->transaction_status;

        [$badgeClass, $badgeLabel, $badgeIcon] = match ($st) {
            'settlement'                          => ['badge-green',  'Berhasil',       'bi-check-circle-fill'],
            'pending'                             => ['badge-yellow', 'Menunggu',       'bi-hourglass-split'],
            'expire', 'expired'                   => ['badge-gray',   'Kedaluwarsa',    'bi-clock-history'],
            'cancel', 'deny', 'failed', 'failure' => ['badge-red',    'Gagal',          'bi-x-circle-fill'],
            default                               => ['badge-gray',   'Belum ada data', 'bi-dash-circle'],
        };

        $showProof = $p && ($p->isManualTransfer() || $p->bukti_transfer);
        $canVerify = $showProof && $p->isPending();
        $nominal   = 'Rp ' . number_format($donasi->nominal, 0, ',', '.');
    @endphp

    <div class="donasi-show">

        {{-- HEADER --}}
        <header class="page-header">
            <div>
                <h2>Detail Donasi #{{ $donasi->id }}</h2>
                <p>Periksa data donatur dan pembayaran sebelum mengambil tindakan.</p>
            </div>
        </header>

        {{-- ALERT --}}
        @if (session('success'))
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger"><i class="bi bi-x-circle"></i> {{ session('error') }}</div>
        @endif

        @if ($canVerify)
            <div class="alert alert-warning">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu verifikasi Anda.</strong>
                Cocokkan nominal di bukti transfer dengan {{ $nominal }}, lalu terima atau tolak pembayaran ini di bagian bawah halaman.
            </div>
        @endif

        <section class="ob-card detail-card">

            {{-- RINGKASAN --}}
            <div class="detail-meta">
                <div class="meta-item">
                    <span class="meta-label">Nominal</span>
                    <span class="meta-value meta-value-lg">{{ $nominal }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status</span>
                    <span class="meta-value">
                        <span class="badge {{ $badgeClass }}">
                            <i class="bi {{ $badgeIcon }}"></i> {{ $badgeLabel }}
                        </span>
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Tanggal Donasi</span>
                    <span class="meta-value">{{ $donasi->created_at->format('d/m/Y H:i') }} WIB</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Tanggal Bayar</span>
                    <span class="meta-value">
                        @if ($p?->paid_at)
                            {{ $p->paid_at->format('d/m/Y H:i') }} WIB
                        @else
                            <span class="donasi-muted">Belum dibayar</span>
                        @endif
                    </span>
                </div>
            </div>

            {{-- DATA DONASI & DONATUR --}}
            <div class="detail-section">
                <h3><i class="bi bi-person-heart"></i> Data Donasi &amp; Donatur</h3>
                <div class="table-wrapper">
                    <table class="data-table data-table-kv">
                        <tr>
                            <th>Campaign</th>
                            <td>
                                <a href="{{ route('admin.campaign.show', $donasi->campaign_id) }}" class="link-blue">
                                    {{ $donasi->campaign->judul ?? '-' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Nama Donatur</th>
                            <td>
                                <strong>{{ $donasi->nama_donatur }}</strong>
                                @if ($donasi->is_anonim)
                                    <span class="badge badge-gray"><i class="bi bi-incognito"></i> Anonim di halaman publik</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $donasi->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>No HP</th>
                            <td>
                                @if($donasi->donor_phone)
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <strong>{{ $donasi->donor_phone }}</strong>
                                        @if($donasi->whatsapp_url)
                                            <a href="{{ $donasi->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                                               class="btn-wa-action"
                                               style="background:#25D366; color:#fff; font-weight:600; padding:4px 12px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; font-size:13px; text-decoration:none;"
                                               title="Buka WhatsApp untuk menghubungi donatur ({{ $donasi->normalized_whatsapp_number }})">
                                                <i class="bi bi-whatsapp"></i> Hubungi via WhatsApp
                                            </a>
                                        @else
                                            <span class="badge badge-gray text-muted" title="Format nomor tidak valid untuk WhatsApp">
                                                <i class="bi bi-exclamation-circle"></i> Format nomor tidak valid untuk WhatsApp
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="donasi-muted">Tidak ada nomor HP terdaftar</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Akun Pengguna</th>
                            <td>
                                @if ($donasi->user)
                                    {{ $donasi->user->name }} ({{ $donasi->user->email }})
                                @else
                                    <span class="donasi-muted">Tamu (tidak login)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Pesan &amp; Doa</th>
                            <td>{{ $donasi->pesan_doa ?: '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- PEMBAYARAN --}}
            <div class="detail-section">
                <h3><i class="bi bi-credit-card-2-front"></i> Informasi Pembayaran</h3>

                @if ($p)
                    <div class="table-wrapper">
                        <table class="data-table data-table-kv">
                            <tr>
                                <th>Metode</th>
                                <td>
                                    @if ($p->paymentChannel)
                                        <strong>{{ $p->paymentChannel->name }}</strong>
                                        <span class="badge badge-blue">{{ $p->paymentChannel->payment_type_label }}</span>
                                    @else
                                        {{ $p->payment_type ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Gateway</th>
                                <td>{{ $p->paymentChannel?->gateway?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Order ID</th>
                                <td><code class="donasi-code">{{ $p->order_id }}</code></td>
                            </tr>
                            <tr>
                                <th>ID Transaksi</th>
                                <td><code class="donasi-code">{{ $p->transaction_id ?? '-' }}</code></td>
                            </tr>
                            @if ($p->rejection_reason)
                                <tr>
                                    <th>Alasan Penolakan</th>
                                    <td class="donasi-danger">{{ $p->rejection_reason }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Data pembayaran tidak ditemukan.
                    </div>
                @endif
            </div>

            {{-- BUKTI TRANSFER --}}
            @if ($showProof)
                <div class="detail-section">
                    <h3><i class="bi bi-receipt"></i> Bukti Transfer</h3>

                    @if ($p->bukti_transfer)
                        <a href="{{ route('donasi.bayar.bukti_transfer', $p->id) }}"
                           target="_blank" rel="noopener noreferrer" class="donasi-proof">
                            <img src="{{ route('donasi.bayar.bukti_transfer', $p->id) }}" alt="Bukti Transfer">
                        </a>
                        <p class="donasi-proof-hint">Klik gambar untuk melihat ukuran penuh.</p>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Donatur belum mengunggah bukti transfer.
                        </div>
                    @endif
                </div>
            @endif

            {{-- AKSI --}}
            <div class="form-actions">
                @if ($canVerify)
                    <button type="button" class="btn-primary" data-dialog-open="dialogApprove">
                        <i class="bi bi-check-circle-fill"></i> Terima Pembayaran
                    </button>
                    <button type="button" class="btn-danger" data-dialog-open="dialogReject">
                        <i class="bi bi-x-circle"></i> Tolak
                    </button>
                @endif

                <div class="form-actions-end">
                    @if($donasi->whatsapp_url)
                        <a href="{{ $donasi->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                           class="btn-outline" style="border-color:#25D366; color:#16a34a;"
                           title="Hubungi donatur via WhatsApp">
                            <i class="bi bi-whatsapp"></i> WhatsApp Donatur
                        </a>
                    @endif
                    @if($donasi->pembayaran && in_array($donasi->pembayaran->transaction_status, ['settlement', 'capture']))
                        <a href="{{ route('riwayat-donasi.kwitansi', $donasi->id) }}" target="_blank" class="btn-outline"
                           title="Lihat / Cetak E-Kwitansi Resmi">
                            <i class="bi bi-receipt"></i> E-Kwitansi
                        </a>
                    @endif
                    <a href="{{ route('admin.donasi.edit', $donasi->id) }}" class="btn-outline">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="{{ route('admin.donasi.index') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

        </section>
    </div>

    {{-- DIALOG TERIMA & TOLAK --}}
    @if ($canVerify)
        <dialog id="dialogApprove" class="donasi-dialog">
            <form action="{{ route('admin.donasi.approve-manual', $donasi->id) }}" method="POST" data-once>
                @csrf
                @method('PATCH')
                <div class="donasi-dialog-body">
                    <div class="donasi-dialog-icon is-success"><i class="bi bi-check-circle-fill"></i></div>
                    <h3>Terima pembayaran ini?</h3>
                    <p>
                        Donasi <strong>{{ $nominal }}</strong> dari <strong>{{ $donasi->nama_donatur }}</strong>
                        akan ditandai berhasil. Pastikan dana sudah benar-benar masuk ke rekening.
                    </p>
                </div>
                <div class="donasi-dialog-footer">
                    <button type="button" class="btn-secondary" data-dialog-close>Batal</button>
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle-fill"></i> Ya, Terima
                    </button>
                </div>
            </form>
        </dialog>

        <dialog id="dialogReject" class="donasi-dialog">
            <form action="{{ route('admin.donasi.reject-manual', $donasi->id) }}" method="POST" data-once>
                @csrf
                @method('PATCH')
                <div class="donasi-dialog-body">
                    <div class="donasi-dialog-icon is-danger"><i class="bi bi-x-circle-fill"></i></div>
                    <h3>Tolak pembayaran ini?</h3>
                    <p>Tulis alasan penolakan agar donatur tahu apa yang perlu diperbaiki.</p>

                    <div class="donasi-dialog-field">
                        <label for="rejection_reason">Alasan Penolakan</label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                  placeholder="Contoh: Bukti transfer buram / nominal tidak sesuai / dana belum masuk.">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')
                            <span class="donasi-field-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="donasi-dialog-footer">
                    <button type="button" class="btn-secondary" data-dialog-close>Batal</button>
                    <button type="submit" class="btn-danger">
                        <i class="bi bi-x-circle"></i> Tolak Pembayaran
                    </button>
                </div>
            </form>
        </dialog>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-dialog-open]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var dlg = document.getElementById(btn.dataset.dialogOpen);
                        if (dlg) dlg.showModal();
                    });
                });

                document.querySelectorAll('[data-dialog-close]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        btn.closest('dialog').close();
                    });
                });

                document.querySelectorAll('dialog.donasi-dialog').forEach(function (dlg) {
                    dlg.addEventListener('click', function (e) {
                        if (e.target === dlg) dlg.close();
                    });
                });

                document.querySelectorAll('form[data-once]').forEach(function (form) {
                    form.addEventListener('submit', function () {
                        form.querySelectorAll('button[type="submit"]').forEach(function (b) {
                            b.disabled = true;
                        });
                    });
                });

                @if ($errors->has('rejection_reason'))
                    document.getElementById('dialogReject')?.showModal();
                @endif
            });
        </script>
    @endif
@endsection