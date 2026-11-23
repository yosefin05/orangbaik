<?php

namespace App\Services;

use App\Models\Donasi;
use Illuminate\Support\Facades\URL;

class DonorWhatsAppMessage
{
    /**
     * Normalisasi nomor HP Indonesia ke format internasional.
     */
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

        // Nomor seluler Indonesia: 628 + 7 sampai 11 digit.
        return preg_match('/^628[0-9]{7,11}$/', $digits)
            ? $digits
            : null;
    }

    /**
     * Membuat pesan WhatsApp berdasarkan status pembayaran.
     *
     * Pending:
     * Mengingatkan donatur untuk melanjutkan pembayaran.
     *
     * Settlement:
     * Mengucapkan terima kasih dan memberikan link e-kwitansi.
     */
    public function forDonation(Donasi $donasi): ?string
    {
        $pembayaran = $donasi->pembayaran;
        $status = $pembayaran?->transaction_status;

        // Hanya pending yang masih berlaku dan settlement yang boleh dihubungi.
        // Tanggal berakhir campaign tidak membatalkan masa berlaku transaksi gateway.
        if (
            !in_array($status, ['pending', 'settlement'], true) ||
            !$donasi->normalized_whatsapp_number
        ) {
            return null;
        }

        if ($status === 'pending' && !$pembayaran?->isFollowUpAvailable()) {
            return null;
        }

        $nama = trim(
            (string) ($donasi->nama_donatur ?: 'Donatur')
        );

        $sapaan = trim(
            (string) ($donasi->sapaan ?: 'Kak')
        );

        $campaign = trim(
            (string) (
                $donasi->campaign?->judul
                ?: 'program donasi OrangBaik'
            )
        );

        $nominal = 'Rp ' . number_format(
            (int) $donasi->nominal,
            0,
            ',',
            '.'
        );

        $greeting = trim($sapaan . ' ' . $nama);

        /*
        |--------------------------------------------------------------------------
        | STATUS PENDING
        |--------------------------------------------------------------------------
        | Tujuan:
        | Mengingatkan donatur agar menyelesaikan pembayaran.
        */

        if ($status === 'pending') {
            $linkPembayaran = null;

            if ($pembayaran?->getKey()) {
                $routeParameters = ['pembayaran' => $pembayaran->id];

                // Donasi guest wajib membawa token meskipun link dibuka setelah
                // berpindah perangkat atau tidak memiliki sesi login.
                if ($donasi->user_id === null) {
                    $routeParameters['token'] = $pembayaran->payment_token;
                }

                $linkPembayaran = route('donasi.bayar.instruksi', $routeParameters);
            }

            $message = "Assalamu'alaikum, {$greeting} 😊\n\n";

            $message .= "Terima kasih telah memilih untuk berdonasi melalui OrangBaik.\n\n";

            $message .= "Kami ingin mengingatkan bahwa pembayaran donasi Kakak untuk program *{$campaign}* sebesar *{$nominal}* masih menunggu penyelesaian.\n\n";

            $message .= "Yuk, selesaikan pembayaran agar donasi Kakak dapat segera diproses. 💙\n\n";

            if ($linkPembayaran) {
                $message .= "🔗 *Lanjutkan Pembayaran:*\n";
                $message .= $linkPembayaran . "\n\n";
            } else {
                $message .= "Silakan gunakan instruksi pembayaran yang telah diberikan sebelumnya.\n\n";
            }

            $message .= "Jika Kakak sudah melakukan pembayaran, silakan abaikan pesan ini. Apabila mengalami kendala, tim OrangBaik siap membantu.\n\n";

            $message .= "Terima kasih telah menjadi bagian dari kebaikan!\n\n";

            $message .= "*OrangBaik — Bersama Menebar Kebaikan.*";

            return $message;
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS SETTLEMENT
        |--------------------------------------------------------------------------
        | Tujuan:
        | Mengucapkan terima kasih dan memberikan e-kwitansi resmi.
        */

        $receiptUrl = URL::temporarySignedRoute(
            'kwitansi.signed',
            now()->addDays(30),
            ['donasi' => $donasi->id]
        );

        $message = "Assalamu'alaikum, {$greeting} 💙\n\n";

        $message .= "Alhamdulillah, donasi Kakak untuk program *{$campaign}* sebesar *{$nominal}* telah berhasil kami terima.\n\n";

        $message .= "Terima kasih telah mempercayakan kebaikan Kakak melalui OrangBaik. Semoga donasi ini menjadi amal jariyah dan membawa manfaat bagi banyak orang. Aamiin. 🤲\n\n";

        $message .= "Sebagai bukti transaksi, berikut e-kwitansi resmi donasi Kakak:\n\n";

        $message .= "🧾 *Lihat E-Kwitansi:*\n";
        $message .= $receiptUrl . "\n\n";

        $message .= "Terima kasih telah ikut menghadirkan kebaikan bersama OrangBaik!\n\n";

        $message .= "*OrangBaik — Bersama Menebar Kebaikan.*";

        return $message;
    }
}
