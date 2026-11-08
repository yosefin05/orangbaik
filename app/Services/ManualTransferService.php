<?php

namespace App\Services;

use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use Illuminate\Http\UploadedFile;
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
     * @throws \Exception jika pembayaran bukan transfer manual atau sudah settlement
     */
    public function saveBuktiTransfer(Pembayaran $pembayaran, UploadedFile $file): string
    {
        if ($pembayaran->transaction_status === 'settlement') {
            throw new \Exception('Pembayaran sudah terverifikasi. Bukti transfer tidak dapat diubah.');
        }

        // Hapus file lama jika ada (cek disk private dan public untuk data migrasi)
        if ($pembayaran->bukti_transfer) {
            if (Storage::disk('private')->exists($pembayaran->bukti_transfer)) {
                Storage::disk('private')->delete($pembayaran->bukti_transfer);
            } elseif (Storage::disk('public')->exists($pembayaran->bukti_transfer)) {
                Storage::disk('public')->delete($pembayaran->bukti_transfer);
            }
        }

        // Simpan file baru di private storage (tidak dapat diakses publik langsung)
        $path = $file->store('bukti-transfer', 'private');

        $pembayaran->update(['bukti_transfer' => $path]);

        Log::info('Bukti transfer uploaded securely to private storage', [
            'order_id' => $pembayaran->order_id,
            'path'     => $path,
        ]);

        return $path;
    }

    /**
     * Admin menerima / memverifikasi pembayaran transfer manual.
     * Status berubah dari pending → settlement.
     *
     * @param  Pembayaran  $pembayaran
     * @throws \Exception jika sudah settlement atau gagal
     */
    public function approve(Pembayaran $pembayaran): void
    {
        if ($pembayaran->transaction_status === 'settlement') {
            throw new \Exception('Pembayaran sudah terverifikasi sebelumnya.');
        }

        $pembayaran->update([
            'transaction_status' => 'settlement',
            'paid_at'            => now(),
            'rejection_reason'   => null,
        ]);

        Log::info('Manual transfer approved', [
            'order_id'   => $pembayaran->order_id,
            'approved_at' => now(),
        ]);
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
        $pembayaran->update([
            'transaction_status' => 'failed',
            'rejection_reason'   => $reason,
        ]);

        Log::info('Manual transfer rejected', [
            'order_id' => $pembayaran->order_id,
            'reason'   => $reason,
        ]);
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
