<?php

namespace App\Services;

use App\Models\Donasi;
use Illuminate\Support\Facades\URL;

class DonorWhatsAppMessage
{
    public static function normalizeIndonesianPhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0062')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        // Indonesian mobile numbers use 62 followed by 9–13 digits, beginning with 8.
        return preg_match('/^628[0-9]{7,11}$/', $digits) ? $digits : null;
    }

    public function forDonation(Donasi $donasi): ?string
    {
        $status = $donasi->pembayaran?->transaction_status;
        if (!in_array($status, ['pending', 'settlement'], true) || !$donasi->normalized_whatsapp_number) {
            return null;
        }

        $name = trim((string) ($donasi->nama_donatur ?: 'Donatur'));
        $campaign = trim((string) ($donasi->campaign?->judul ?: 'program donasi OrangBaik'));
        $amount = 'Rp ' . number_format((int) $donasi->nominal, 0, ',', '.');
        $greeting = $donasi->sapaan . ' ' . $name;

        if ($status === 'pending') {
            return "Halo {$greeting}, donasi Anda sebesar {$amount} untuk campaign \"{$campaign}\" masih menunggu pembayaran. Silakan selesaikan pembayaran melalui instruksi yang telah diberikan. Jika ada kendala, kami siap membantu. Terima kasih.";
        }

        $receiptUrl = URL::temporarySignedRoute(
            'kwitansi.signed',
            now()->addDays(30),
            ['donasi' => $donasi->id]
        );
        return "Halo {$greeting}, terima kasih atas donasi sebesar {$amount} untuk campaign \"{$campaign}\". Semoga kebaikan Anda membawa keberkahan dan manfaat bagi banyak orang. Aamiin. E-kwitansi resmi: {$receiptUrl}";
    }
}
