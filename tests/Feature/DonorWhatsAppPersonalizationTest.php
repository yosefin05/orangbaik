<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Services\DonorWhatsAppMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DonorWhatsAppPersonalizationTest extends TestCase
{
    public function test_indonesian_phone_formats_are_normalized_and_invalid_numbers_rejected(): void
    {
        foreach (['081234567890', '6281234567890', '+6281234567890'] as $phone) {
            $this->assertSame('6281234567890', DonorWhatsAppMessage::normalizeIndonesianPhone($phone));
        }

        $this->assertNull(DonorWhatsAppMessage::normalizeIndonesianPhone(null));
        $this->assertNull(DonorWhatsAppMessage::normalizeIndonesianPhone('not-a-phone'));
        $this->assertNull(DonorWhatsAppMessage::normalizeIndonesianPhone('021123456'));
    }

    public function test_whatsapp_message_is_status_specific_and_contains_secure_receipt_route(): void
    {
        $campaign = new Campaign(['judul' => 'Bantu Sekolah']);
        $donasi = new Donasi([
            'sapaan' => 'Bu', 'nama_donatur' => 'Siti', 'no_hp' => '081234567890', 'nominal' => 25000,
        ]);
        $donasi->id = 123;
        $donasi->setRelation('campaign', $campaign);

        $payment = new Pembayaran(['transaction_status' => 'pending']);
        $donasi->setRelation('pembayaran', $payment);
        $pendingUrl = $donasi->whatsapp_url;
        $this->assertStringContainsString('wa.me/6281234567890', $pendingUrl);
        $pendingMessage = rawurldecode(parse_url($pendingUrl, PHP_URL_QUERY));
        $this->assertStringContainsString('Bu Siti', $pendingMessage);
        $this->assertStringContainsString('Bantu Sekolah', $pendingMessage);
        $this->assertStringContainsString('Rp 25.000', $pendingMessage);
        $this->assertStringContainsString('selesaikan pembayaran', $pendingMessage);
        $this->assertStringNotContainsString('kwitansi', $pendingMessage);

        $payment->transaction_status = 'settlement';
        $settledUrl = $donasi->whatsapp_url;
        $settledMessage = rawurldecode(parse_url($settledUrl, PHP_URL_QUERY));
        $this->assertStringContainsString('terima kasih', strtolower($settledMessage));
        preg_match('/https?:\/\/[^\s]+/', $settledMessage, $receiptMatch);
        $this->assertNotEmpty($receiptMatch);
        $receiptUrl = $receiptMatch[0];
        $this->assertStringContainsString(route('kwitansi.signed', ['donasi' => $donasi->id], true), $receiptUrl);
        $this->assertTrue(URL::hasValidSignature(Request::create($receiptUrl)));

        foreach (['failed', 'expired', 'expire'] as $status) {
            $payment->transaction_status = $status;
            $this->assertNull($donasi->whatsapp_url);
        }
    }

    public function test_legacy_greeting_falls_back_to_kak_and_admin_pages_are_protected(): void
    {
        $donasi = new Donasi();
        $donasi->setRawAttributes(['sapaan' => null]);
        $this->assertSame('Kak', $donasi->sapaan);
        $this->get(route('admin.donasi.index'))->assertRedirect();
    }
}
