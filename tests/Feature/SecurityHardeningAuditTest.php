<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Models\PaymentChannel;
use App\Models\PaymentGateway;
use App\Models\Penggalang_Dana;
use App\Models\Penggalang_Dana_Dokumen;
use App\Models\User;
use App\Services\FlipService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SecurityHardeningAuditTest extends TestCase
{
    /**
     * Skenario 1: Dokumen individu tidak boleh berstatus publik untuk pengunjung (isPublicForVisitor)
     */
    public function test_dokumen_individu_dilarang_publik(): void
    {
        $penggalangIndividu = new Penggalang_Dana([
            'id' => 10,
            'user_id' => 100,
            'jenis_penggalang' => 'individu',
            'nama_penggalang' => 'Individu Test',
        ]);

        $dokumenKtp = new Penggalang_Dana_Dokumen([
            'nama_dokumen' => 'KTP Ahmad',
            'file_dokumen' => 'https://drive.google.com/ktp-ahm',
            'is_public' => false,
        ]);
        $dokumenKtp->setRelation('penggalangDana', $penggalangIndividu);

        $this->assertFalse($dokumenKtp->isPublicForVisitor(), 'KTP Individu dilarang berstatus publik untuk pengunjung');
    }

    /**
     * Skenario 2: User lain tidak dapat mengakses dokumen privat
     */
    public function test_user_lain_tidak_dapat_mengakses_dokumen_privat(): void
    {
        $owner = new User(['role' => 'user']);
        $owner->id = 100;

        $otherUser = new User(['role' => 'user']);
        $otherUser->id = 999;

        $penggalang = new Penggalang_Dana([
            'id' => 10,
            'user_id' => 100,
            'jenis_penggalang' => 'individu',
        ]);

        $dokumen = new Penggalang_Dana_Dokumen([
            'nama_dokumen' => 'KTP Owner',
            'file_dokumen' => 'ktp-owner.pdf',
            'is_public' => false,
        ]);
        $dokumen->setRelation('penggalangDana', $penggalang);

        $this->assertFalse($dokumen->canBeAccessedBy($otherUser), 'Pengguna lain dilarang mengakses dokumen privat');
        $this->assertTrue($dokumen->canBeAccessedBy($owner), 'Pemilik sah berhak mengakses dokumen miliknya');
    }

    /**
     * Skenario 3: Admin dapat mengakses dokumen untuk verifikasi
     */
    public function test_admin_dapat_mengakses_semua_dokumen_verifikasi(): void
    {
        $admin = new User(['role' => 'admin']);
        $admin->id = 1;

        $penggalang = new Penggalang_Dana([
            'id' => 20,
            'user_id' => 200,
            'jenis_penggalang' => 'organisasi',
        ]);

        $dokumen = new Penggalang_Dana_Dokumen([
            'nama_dokumen' => 'Akta Yayasan',
            'file_dokumen' => 'akta.pdf',
            'is_public' => false,
        ]);
        $dokumen->setRelation('penggalangDana', $penggalang);

        $this->assertTrue($dokumen->canBeAccessedBy($admin), 'Admin harus memiliki akses untuk verifikasi dokumen');
    }

    /**
     * Skenario 4: Token guest anti-IDOR pada pembayaran
     */
    public function test_validasi_token_guest_anti_idor(): void
    {
        $pembayaran = new Pembayaran([
            'order_id' => 'OB-TEST-12345',
            'payment_token' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $this->assertTrue($pembayaran->isValidGuestToken('550e8400-e29b-41d4-a716-446655440000'));
        $this->assertFalse($pembayaran->isValidGuestToken('invalid-token'));
        $this->assertFalse($pembayaran->isValidGuestToken(''));
        $this->assertFalse($pembayaran->isValidGuestToken(null));
    }

    /**
     * Skenario 5: Flip Webhook menolak callback saat expected secret token kosong (Fail-Closed)
     */
    public function test_flip_webhook_fail_closed_saat_secret_kosong(): void
    {
        Config::set('payment.flip.webhook_token', '');

        $flipService = new FlipService();
        $result = $flipService->handleWebhook('any-token', [
            'id' => 'flip-test-123',
            'status' => 'SUCCESSFUL',
        ]);

        $this->assertFalse($result, 'Flip webhook harus ditolak jika secret token di .env belum dikonfigurasi (fail-closed)');
    }

    /**
     * Skenario 6: Flip Webhook menolak token yang tidak cocok
     */
    public function test_flip_webhook_menolak_token_tidak_cocok(): void
    {
        Config::set('payment.flip.webhook_token', 'valid-secret-token-12345');

        $flipService = new FlipService();
        $result = $flipService->handleWebhook('wrong-token', [
            'id' => 'flip-test-123',
            'status' => 'SUCCESSFUL',
        ]);

        $this->assertFalse($result);
    }

    /**
     * Skenario 7: Midtrans Webhook menolak callback saat Server Key kosong (Fail-Closed)
     */
    public function test_midtrans_webhook_fail_closed_saat_server_key_kosong(): void
    {
        Config::set('midtrans.serverKey', '');
        Config::set('payment.midtrans.server_key', '');

        $midtransService = new MidtransService();
        $result = $midtransService->handleWebhook([
            'order_id' => 'OB-12345',
            'status_code' => '200',
            'gross_amount' => '50000',
            'signature_key' => 'any-sig',
        ]);

        $this->assertFalse($result, 'Midtrans webhook harus menolak jika server key tidak diset (fail-closed)');
    }

    /**
     * Skenario 8: Midtrans Webhook menolak signature tidak valid
     */
    public function test_midtrans_webhook_menolak_signature_tidak_valid(): void
    {
        Config::set('midtrans.serverKey', 'secret-key-midtrans');

        $midtransService = new MidtransService();
        $result = $midtransService->handleWebhook([
            'order_id' => 'OB-12345',
            'status_code' => '200',
            'gross_amount' => '50000',
            'signature_key' => 'invalid-hash-string',
        ]);

        $this->assertFalse($result, 'Midtrans webhook harus menolak signature yang tidak valid');
    }

    /**
     * Skenario 9: Status settlement adalah terminal dan tidak dapat rollback
     */
    public function test_status_settlement_tidak_dapat_rollback(): void
    {
        $pembayaranSettled = new Pembayaran([
            'order_id' => 'OB-SETTLED-1',
            'transaction_status' => 'settlement',
        ]);

        $this->assertTrue($pembayaranSettled->isSettled());
        $this->assertFalse($pembayaranSettled->isResumable(), 'Pembayaran settlement tidak boleh resumable');
    }
}
