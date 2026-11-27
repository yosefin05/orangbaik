<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ManualTransferService
{
    /**
     * Membuat transaksi transfer manual.
     * Status awal: pending — menunggu donatur mengirim uang dan upload bukti.
     *
     * @param  Donasi          $donasi
     * @param  PaymentChannel  $channel  Channel transfer manual yang dipilih
     * @return Pembayaran
     */
    public function createTransaction(Donasi $donasi, PaymentChannel $channel): Pembayaran
    {
        $orderId = $this->generateOrderId($donasi);

        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => $orderId,
            'payment_type'       => 'transfer',
            'transaction_status' => 'pending',
        ]);

        Log::info('Manual transfer transaction created', [
            'order_id'   => $orderId,
            'donasi_id'  => $donasi->id,
            'channel'    => $channel->name,
            'nominal'    => $donasi->nominal,
        ]);

        return $pembayaran;
    }

    /**
     * Simpan file bukti transfer yang diupload donatur.
     * File disimpan di private storage (storage/app/private/bukti-transfer/).
     *
     * @param  Pembayaran     $pembayaran
     * @param  UploadedFile   $file
     * @return string  Path file tersimpan
     * @throws \DomainException jika pembayaran bukan transfer manual atau bukan pending
     */
    public function saveBuktiTransfer(Pembayaran $pembayaran, UploadedFile $file): string
    {
        $result = DB::transaction(function () use ($pembayaran, $file): array {
            $lockedPayment = $this->lockPendingManualPayment($pembayaran);

            // Keep the old proof intact until the replacement file and database
            // pointer have both been committed successfully.
            $oldPrivatePath = $lockedPayment->bukti_transfer
                && Storage::disk('private')->exists($lockedPayment->bukti_transfer)
                    ? $lockedPayment->bukti_transfer
                    : null;

            // Simpan file baru di private storage (tidak dapat diakses publik langsung)
            $path = $file->store('bukti-transfer', 'private');
            if (!$path) {
                throw new \RuntimeException('Gagal menyimpan file bukti transfer.');
            }

            $lockedPayment->update(['bukti_transfer' => $path]);

            Log::info('Bukti transfer uploaded securely to private storage', [
                'order_id' => $lockedPayment->order_id,
                'path'     => $path,
            ]);

            return [
                'path' => $path,
                'old_private_path' => $oldPrivatePath,
            ];
        });

        // Filesystem changes are not rolled back with the SQL transaction.
        // Delete the superseded private file only after the new DB pointer commits.
        if ($result['old_private_path'] && $result['old_private_path'] !== $result['path']) {
            Storage::disk('private')->delete($result['old_private_path']);
        }

        return $result['path'];
    }

    /**
     * Admin menerima / memverifikasi pembayaran transfer manual.
     * Status berubah dari pending → settlement.
     *
     * @param  Pembayaran  $pembayaran
     * @throws \DomainException jika pembayaran bukan manual-pending atau bukti belum tersedia
     */
    public function approve(Pembayaran $pembayaran): void
    {
        DB::transaction(function () use ($pembayaran): void {
            $lockedPayment = $this->lockPendingManualPayment($pembayaran);
            $proofPath = $lockedPayment->bukti_transfer;

            if (
                !$proofPath ||
                (!Storage::disk('private')->exists($proofPath) && !Storage::disk('public')->exists($proofPath))
            ) {
                throw new \DomainException('Pembayaran tidak dapat disetujui sebelum bukti transfer tersedia.');
            }

            $paidAt = now();
            $lockedPayment->update([
                'transaction_status' => 'settlement',
                'paid_at'            => $paidAt,
                'rejection_reason'   => null,
            ]);

            Log::info('Manual transfer approved', [
                'order_id'    => $lockedPayment->order_id,
                'approved_at' => $paidAt,
            ]);
        });
    }

    /**
     * Admin menolak pembayaran transfer manual.
     * Status berubah dari pending → failed.
     *
     * @param  Pembayaran  $pembayaran
     * @param  string      $reason  Alasan penolakan
     */
    public function reject(Pembayaran $pembayaran, string $reason = ''): void
    {
        DB::transaction(function () use ($pembayaran, $reason): void {
            $lockedPayment = $this->lockPendingManualPayment($pembayaran);
            $lockedPayment->update([
                'transaction_status' => 'failed',
                'rejection_reason'   => $reason,
            ]);

            Log::info('Manual transfer rejected', [
                'order_id' => $lockedPayment->order_id,
                'reason'   => $reason,
            ]);
        });
    }

    /**
     * Menandai transfer manual pending sebagai kedaluwarsa setelah donatur tidak merespons.
     *
     * @throws \\DomainException jika transaksi bukan transfer manual atau tidak lagi pending
     */
    public function expire(Pembayaran $pembayaran, ?int $adminId = null, string $reason = ''): void
    {
        DB::transaction(function () use ($pembayaran, $adminId, $reason): void {
            $lockedPayment = $this->lockPendingManualPayment($pembayaran);
            $expiredAt = now();

            $lockedPayment->update([
                'transaction_status' => 'expired',
                'expired_at' => $expiredAt,
                'expired_by' => $adminId,
                'expiration_reason' => $reason !== ''
                    ? $reason
                    : 'Donatur tidak merespons setelah tindak lanjut admin.',
            ]);

            Log::info('Manual transfer marked as expired by admin', [
                'order_id' => $lockedPayment->order_id,
                'expired_by' => $adminId,
                'expired_at' => $expiredAt,
            ]);
        });
    }

    private function lockPendingManualPayment(Pembayaran $pembayaran): Pembayaran
    {
        $lockedPayment = Pembayaran::with('paymentChannel.gateway')
            ->whereKey($pembayaran->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        if (!$lockedPayment->isManualTransfer()) {
            throw new \DomainException('Transaksi ini bukan pembayaran transfer manual.');
        }

        if (in_array($lockedPayment->transaction_status, ['settlement', 'failed', 'expired', 'expire'], true)) {
            throw new \DomainException('Pembayaran berstatus terminal dan tidak dapat diubah.');
        }

        if (!$lockedPayment->isPending()) {
            throw new \DomainException('Hanya pembayaran pending yang dapat diproses.');
        }

        return $lockedPayment;
    }

    /**
     * Generate order ID unik.
     * Format: OB-{YYYYMMDD}-{donasi_id_padded}
     */
    protected function generateOrderId(Donasi $donasi): string
    {
        $prefix = config('payment.order_id_prefix', 'OB');
        $date   = now()->format('Ymd');
        $id     = str_pad($donasi->id, 5, '0', STR_PAD_LEFT);
        $random = strtoupper(\Illuminate\Support\Str::random(3));

        return "{$prefix}-{$date}-{$id}-{$random}";
    }
}
