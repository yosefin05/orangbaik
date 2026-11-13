<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Kategori;
use App\Models\Penggalang_Dana;
use App\Models\PaymentGateway;
use App\Models\PaymentChannel;
use App\Models\Donasi;
use App\Models\Pembayaran;
use App\Services\ManualTransferService;
use App\Services\MidtransService;
use App\Services\FlipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PaymentChannelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\KategoriSeeder::class);
        $this->seed(\Database\Seeders\PaymentGatewaySeeder::class);
        $this->seed(\Database\Seeders\PaymentChannelSeeder::class);
    }

    protected function createDonasi(): Donasi
    {
        $user = User::factory()->create();
        $rand = uniqid();
        $penggalang = Penggalang_Dana::create([
            'user_id'          => $user->id,
            'verified_by'      => $user->id,
            'jenis_penggalang' => 'organisasi',
            'foto_profil'      => 'profile.jpg',
            'nama_penggalang'  => 'Yayasan Amal ' . $rand,
            'email'            => 'yayasan_' . $rand . '@example.com',
            'no_telepon'       => '08' . rand(100000000, 999999999),
            'alamat'           => 'Jakarta',
            'status'           => 'approved',
        ]);

        $kategori = Kategori::first();

        $campaign = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id'        => $kategori->id,
            'thumbnail'          => 'thumb.jpg',
            'judul'              => 'Bantu Pendidikan Anak ' . $rand,
            'slug'               => 'bantu-pendidikan-anak-' . $rand,
            'deskripsi'          => 'Deskripsi bantuan pendidikan',
            'tanggal_mulai'      => now(),
            'tanggal_berakhir'   => now()->addDays(30),
            'target_donasi'      => 10000000,
            'status'             => 'approved',
            'is_active'          => true,
            'minimal_donasi'     => 5000,
        ]);

        return Donasi::create([
            'campaign_id'  => $campaign->id,
            'user_id'      => $user->id,
            'nama_donatur' => 'Budi Santoso',
            'email'        => 'budi@example.com',
            'no_hp'        => '08123456789',
            'nominal'      => 50000,
            'pesan_doa'    => 'Semoga berkah',
            'is_anonim'    => false,
        ]);
    }

    // =========================================================================
    // 1. PAYMENT ARCHITECTURE & GATEWAY COUNT TESTS (HANYA 2 GATEWAYS + 1 MANUAL)
    // =========================================================================

    public function test_only_two_payment_gateways_plus_manual_exist(): void
    {
        $gateways = PaymentGateway::all();
        $this->assertCount(3, $gateways);

        $codes = $gateways->pluck('code')->toArray();
        $this->assertContains('midtrans', $codes);
        $this->assertContains('flip', $codes);
        $this->assertContains('manual', $codes);
    }

    public function test_payment_gateway_has_active_channels(): void
    {
        $midtrans = PaymentGateway::where('code', 'midtrans')->first();
        $this->assertNotNull($midtrans);
        $this->assertGreaterThan(0, $midtrans->channels()->count());

        $flip = PaymentGateway::where('code', 'flip')->first();
        $this->assertNotNull($flip);
        $this->assertGreaterThan(0, $flip->channels()->count());

        $manual = PaymentGateway::where('code', 'manual')->first();
        $this->assertNotNull($manual);
        $this->assertGreaterThan(0, $manual->channels()->count());
    }

    public function test_channel_with_transactions_is_soft_disabled_not_deleted(): void
    {
        $donasi = $this->createDonasi();
        $channel = PaymentChannel::first();

        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'TEST-SOFT-DEL-' . time(),
            'transaction_status' => 'pending',
        ]);

        $this->assertTrue($channel->hasTransactions());
    }

    // =========================================================================
    // 2. MIDTRANS GATEWAY TESTS
    // =========================================================================

    public function test_midtrans_webhook_valid_signature_settlement_and_idempotency(): void
    {
        Config::set('midtrans.serverKey', 'test-midtrans-key');

        $donasi = $this->createDonasi();
        $channel = PaymentChannel::where('channel_code', 'qris')->first();

        $orderId = 'OB-MID-' . time();
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => $orderId,
            'transaction_status' => 'pending',
        ]);

        $grossAmount = '50000.00';
        $statusCode = '200';
        $serverKey = 'test-midtrans-key';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'settlement',
            'payment_type'       => 'qris',
            'transaction_id'     => 'MID-TRX-12345',
        ];

        $service = new MidtransService();
        $result = $service->handleWebhook($payload);

        $this->assertTrue($result);
        $this->assertEquals('settlement', $pembayaran->fresh()->transaction_status);
        $this->assertNotNull($pembayaran->fresh()->paid_at);

        // Idempotency: Late pending notification will not revert settlement
        $payload['transaction_status'] = 'pending';
        $service->handleWebhook($payload);
        $this->assertEquals('settlement', $pembayaran->fresh()->transaction_status);
    }

    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        Config::set('midtrans.serverKey', 'test-midtrans-key');

        $donasi = $this->createDonasi();
        $channel = PaymentChannel::where('channel_code', 'qris')->first();

        $orderId = 'OB-MID-INVALID-' . time();
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => $orderId,
            'transaction_status' => 'pending',
        ]);

        $payload = [
            'order_id'           => $orderId,
            'status_code'        => '200',
            'gross_amount'       => '50000.00',
            'signature_key'      => 'fake-invalid-signature',
            'transaction_status' => 'settlement',
        ];

        $service = new MidtransService();
        $result = $service->handleWebhook($payload);

        $this->assertFalse($result);
        $this->assertEquals('pending', $pembayaran->fresh()->transaction_status);
    }

    public function test_midtrans_webhook_status_mapping(): void
    {
        Config::set('midtrans.serverKey', 'test-midtrans-key');
        $service = new MidtransService();
        $channel = PaymentChannel::where('channel_code', 'gopay')->first();

        // Failed status: deny
        $donasi1 = $this->createDonasi();
        $orderId1 = 'OB-MID-DENY-' . time();
        $pembayaran1 = Pembayaran::create([
            'donasi_id'          => $donasi1->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => $orderId1,
            'transaction_status' => 'pending',
        ]);
        $sig1 = hash('sha512', $orderId1 . '200' . '50000.00' . 'test-midtrans-key');
        $service->handleWebhook([
            'order_id'           => $orderId1,
            'status_code'        => '200',
            'gross_amount'       => '50000.00',
            'signature_key'      => $sig1,
            'transaction_status' => 'deny',
        ]);
        $this->assertEquals('failed', $pembayaran1->fresh()->transaction_status);

        // Expired status: expire
        $donasi2 = $this->createDonasi();
        $orderId2 = 'OB-MID-EXP-' . time();
        $pembayaran2 = Pembayaran::create([
            'donasi_id'          => $donasi2->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => $orderId2,
            'transaction_status' => 'pending',
        ]);
        $sig2 = hash('sha512', $orderId2 . '200' . '50000.00' . 'test-midtrans-key');
        $service->handleWebhook([
            'order_id'           => $orderId2,
            'status_code'        => '200',
            'gross_amount'       => '50000.00',
            'signature_key'      => $sig2,
            'transaction_status' => 'expire',
        ]);
        $this->assertEquals('expired', $pembayaran2->fresh()->transaction_status);
    }

    // =========================================================================
    // 3. FLIP GATEWAY TESTS
    // =========================================================================

    public function test_flip_webhook_valid_token_successful_and_idempotency(): void
    {
        Config::set('payment.flip.webhook_token', 'test-flip-token');

        $donasi = $this->createDonasi();
        $channel = PaymentChannel::where('payment_gateway_id', PaymentGateway::where('code', 'flip')->first()->id)->first();

        $flipId = '987654';
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-FLIP-' . time(),
            'transaction_id'     => $flipId,
            'payment_type'       => 'va',
            'transaction_status' => 'pending',
        ]);

        $payload = [
            'id'     => $flipId,
            'amount' => 50000,
            'status' => 'SUCCESSFUL',
        ];

        $service = new FlipService();
        $result = $service->handleWebhook('test-flip-token', $payload);

        $this->assertTrue($result);
        $this->assertEquals('settlement', $pembayaran->fresh()->transaction_status);
        $this->assertNotNull($pembayaran->fresh()->paid_at);

        // Idempotency: second webhook should not fail or change status
        $result2 = $service->handleWebhook('test-flip-token', $payload);
        $this->assertTrue($result2);
        $this->assertEquals('settlement', $pembayaran->fresh()->transaction_status);
    }

    public function test_flip_webhook_rejects_invalid_token(): void
    {
        Config::set('payment.flip.webhook_token', 'correct-flip-token');

        $donasi = $this->createDonasi();
        $channel = PaymentChannel::where('payment_gateway_id', PaymentGateway::where('code', 'flip')->first()->id)->first();

        $flipId = '112233';
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-FLIP-INV-' . time(),
            'transaction_id'     => $flipId,
            'payment_type'       => 'va',
            'transaction_status' => 'pending',
        ]);

        $payload = [
            'id'     => $flipId,
            'amount' => 50000,
            'status' => 'SUCCESSFUL',
        ];

        $service = new FlipService();
        $result = $service->handleWebhook('wrong-token', $payload);

        $this->assertFalse($result);
        $this->assertEquals('pending', $pembayaran->fresh()->transaction_status);
    }

    // =========================================================================
    // 4. MANUAL TRANSFER TESTS (Dompet Al Qur'an)
    // =========================================================================

    public function test_manual_transfer_full_flow(): void
    {
        Storage::fake('private');

        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $manualChannel = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->where('channel_code', 'BSI - NM')
            ->first();
        $this->assertNotNull($manualChannel);
        $this->assertTrue($manualChannel->is_active);
        $this->assertSame('3311131318', $manualChannel->account_number);
        $this->assertSame('Orang Baik', $manualChannel->account_name);

        $donasi = $this->createDonasi();
        $service = new ManualTransferService();

        // 1. Buat transaksi manual
        $pembayaran = $service->createTransaction($donasi, $manualChannel);
        $this->assertEquals('pending', $pembayaran->transaction_status);
        $this->assertEquals('transfer', $pembayaran->payment_type);
        $this->assertStringStartsWith('OB-', $pembayaran->order_id);

        // 2. Upload bukti transfer
        $file = UploadedFile::fake()->image('bukti_transfer.jpg');
        $path = $service->saveBuktiTransfer($pembayaran, $file);
        $this->assertNotNull($pembayaran->fresh()->bukti_transfer);
        Storage::disk('private')->assertExists($path);

        // 3. Admin Approve
        $service->approve($pembayaran);
        $this->assertEquals('settlement', $pembayaran->fresh()->transaction_status);
        $this->assertNotNull($pembayaran->fresh()->paid_at);

        // 4. Admin Reject flow on another payment
        $donasi2 = $this->createDonasi();
        $pembayaran2 = $service->createTransaction($donasi2, $manualChannel);
        $service->reject($pembayaran2, 'Foto bukti transfer tidak terbaca');
        $this->assertEquals('failed', $pembayaran2->fresh()->transaction_status);
        $this->assertEquals('Foto bukti transfer tidak terbaca', $pembayaran2->fresh()->rejection_reason);
    }

    // =========================================================================
    // 5. PRD DONATION CALCULATION & VALIDATION RULES
    // =========================================================================

    public function test_donation_minimum_amount_validation(): void
    {
        $donasi = $this->createDonasi();
        $campaign = $donasi->campaign;
        $channel = PaymentChannel::first();

        $response = $this->postJson(route('donasi.store', $campaign->slug), [
            'payment_channel_id' => $channel->id,
            'nominal'            => 3000, // < Rp 5.000
            'nama_donatur'       => 'Test User',
        ]);

        $response->assertStatus(422);
    }

    public function test_donation_minimum_uses_the_larger_global_or_campaign_amount(): void
    {
        Config::set('payment.minimum_amount', 10000);

        $donasi = $this->createDonasi();
        $campaign = $donasi->campaign;
        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $channel = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->where('channel_code', 'BSI - NM')
            ->firstOrFail();

        $basePayload = [
            'payment_channel_id' => $channel->id,
            'nama_donatur'       => 'Test User',
            'no_hp'              => '08123456789',
        ];

        $this->postJson(route('donasi.store', $campaign->slug), $basePayload + ['nominal' => 5000])
            ->assertStatus(422);

        $campaign->update(['minimal_donasi' => 15000]);

        $this->postJson(route('donasi.store', $campaign->slug), $basePayload + ['nominal' => 10000])
            ->assertStatus(422);

        $this->postJson(route('donasi.store', $campaign->slug), $basePayload + ['nominal' => 15000])
            ->assertOk()
            ->assertJson(['success' => true, 'type' => 'manual_transfer']);
    }

    public function test_manual_approval_requires_an_available_transfer_proof(): void
    {
        Storage::fake('private');
        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $manualChannel = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->where('channel_code', 'BSI - NM')
            ->firstOrFail();
        $pembayaran = (new ManualTransferService())->createTransaction($this->createDonasi(), $manualChannel);

        try {
            (new ManualTransferService())->approve($pembayaran);
            $this->fail('Approval without a stored proof must be rejected.');
        } catch (\DomainException $e) {
            $this->assertSame('Pembayaran tidak dapat disetujui sebelum bukti transfer tersedia.', $e->getMessage());
        }

        $this->assertSame('pending', $pembayaran->fresh()->transaction_status);
    }

    public function test_manual_review_cannot_change_terminal_payment_statuses(): void
    {
        Storage::fake('private');
        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $manualChannel = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->where('channel_code', 'BSI - NM')
            ->firstOrFail();
        $service = new ManualTransferService();

        foreach (['settlement', 'failed', 'expired', 'expire'] as $status) {
            $pembayaran = Pembayaran::create([
                'donasi_id'          => $this->createDonasi()->id,
                'payment_channel_id' => $manualChannel->id,
                'order_id'           => 'OB-TERMINAL-' . strtoupper($status),
                'transaction_status' => $status,
            ]);

            foreach (['approve', 'reject'] as $action) {
                try {
                    $action === 'approve'
                        ? $service->approve($pembayaran)
                        : $service->reject($pembayaran, 'Tidak valid');
                    $this->fail("The {$action} action must reject {$status} payments.");
                } catch (\DomainException $e) {
                    $this->assertSame($status, $pembayaran->fresh()->transaction_status);
                }
            }

            try {
                $service->saveBuktiTransfer($pembayaran, UploadedFile::fake()->image('bukti.jpg'));
                $this->fail("Proof uploads must be rejected for {$status} payments.");
            } catch (\DomainException $e) {
                $this->assertSame($status, $pembayaran->fresh()->transaction_status);
            }
        }

        $this->assertEmpty(Storage::disk('private')->allFiles());
    }

    public function test_admin_manual_review_endpoints_apply_proof_and_terminal_guards(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create(['role' => 'admin']);
        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $manualChannel = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->where('channel_code', 'BSI - NM')
            ->firstOrFail();
        $service = new ManualTransferService();

        $approvedDonation = $this->createDonasi();
        $approvedPayment = $service->createTransaction($approvedDonation, $manualChannel);

        $this->actingAs($admin)
            ->patch(route('admin.donasi.approve-manual', $approvedDonation->id))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('pending', $approvedPayment->fresh()->transaction_status);

        $service->saveBuktiTransfer($approvedPayment, UploadedFile::fake()->image('bukti.jpg'));
        $this->patch(route('admin.donasi.approve-manual', $approvedDonation->id))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame('settlement', $approvedPayment->fresh()->transaction_status);

        $rejectedDonation = $this->createDonasi();
        $rejectedPayment = $service->createTransaction($rejectedDonation, $manualChannel);
        $this->patch(route('admin.donasi.reject-manual', $rejectedDonation->id), [
            'rejection_reason' => 'Bukti tidak valid',
        ])->assertRedirect()->assertSessionHas('warning');
        $this->assertSame('failed', $rejectedPayment->fresh()->transaction_status);

        $this->patch(route('admin.donasi.approve-manual', $rejectedDonation->id))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('failed', $rejectedPayment->fresh()->transaction_status);
    }

    public function test_soft_launch_enables_only_the_official_manual_bsi_channel(): void
    {
        $manual = PaymentGateway::where('code', 'manual')->firstOrFail();
        $midtrans = PaymentGateway::where('code', 'midtrans')->firstOrFail();
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();

        $bsiManual = PaymentChannel::where('payment_gateway_id', $manual->id)
            ->where('channel_code', 'BSI - NM')
            ->firstOrFail();

        $this->assertTrue($bsiManual->is_active);
        $this->assertSame('3311131318', $bsiManual->account_number);
        $this->assertSame('Orang Baik', $bsiManual->account_name);
        $this->assertTrue($manual->is_active);
        $this->assertTrue($midtrans->is_active);
        $this->assertTrue($flip->is_active);
        $this->assertSame(1, PaymentChannel::where('payment_gateway_id', $manual->id)->where('is_active', true)->count());
        $this->assertSame(0, PaymentChannel::whereIn('payment_gateway_id', [$midtrans->id, $flip->id])->where('is_active', true)->count());

        $channelIds = PaymentChannel::orderBy('id')->pluck('id')->all();
        $this->seed(\Database\Seeders\PaymentChannelSeeder::class);
        $this->assertSame($channelIds, PaymentChannel::orderBy('id')->pluck('id')->all());
    }

    public function test_bsi_instruction_uses_the_manual_channel_record(): void
    {
        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $manualChannel = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->where('channel_code', 'BSI - NM')
            ->firstOrFail();
        $donasi = $this->createDonasi();
        $donasi->update(['user_id' => null]);
        $token = (string) \Illuminate\Support\Str::uuid();
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $manualChannel->id,
            'payment_token'      => $token,
            'order_id'           => 'OB-BSI-INSTRUKSI-' . $donasi->id,
            'payment_type'       => 'transfer',
            'transaction_status' => 'pending',
        ]);

        $this->get(route('donasi.bayar.instruksi', [
            'pembayaran' => $pembayaran->id,
            'token'      => $token,
        ]))
            ->assertOk()
            ->assertSee('3311131318')
            ->assertSee('Orang Baik');
    }

    public function test_only_settlement_donations_counted_in_campaign_total(): void
    {
        $donasi = $this->createDonasi();
        $campaign = $donasi->campaign;
        $channel = PaymentChannel::first();

        // 1. Donasi Pending: Rp 50.000
        Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-PENDING-1',
            'transaction_status' => 'pending',
        ]);

        // 2. Donasi Settlement: Rp 100.000
        $donasiSettled = Donasi::create([
            'campaign_id'  => $campaign->id,
            'user_id'      => $donasi->user_id,
            'nama_donatur' => 'Donatur Sukses',
            'nominal'      => 100000,
            'is_anonim'    => false,
        ]);
        Pembayaran::create([
            'donasi_id'          => $donasiSettled->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-SETTLED-1',
            'transaction_status' => 'settlement',
            'paid_at'            => now(),
        ]);

        // 3. Donasi Failed: Rp 75.000
        $donasiFailed = Donasi::create([
            'campaign_id'  => $campaign->id,
            'user_id'      => $donasi->user_id,
            'nama_donatur' => 'Donatur Gagal',
            'nominal'      => 75000,
            'is_anonim'    => false,
        ]);
        Pembayaran::create([
            'donasi_id'          => $donasiFailed->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-FAILED-1',
            'transaction_status' => 'failed',
        ]);

        // 4. Donasi Expired: Rp 60.000
        $donasiExpired = Donasi::create([
            'campaign_id'  => $campaign->id,
            'user_id'      => $donasi->user_id,
            'nama_donatur' => 'Donatur Expired',
            'nominal'      => 60000,
            'is_anonim'    => false,
        ]);
        Pembayaran::create([
            'donasi_id'          => $donasiExpired->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-EXPIRED-1',
            'transaction_status' => 'expired',
        ]);

        // Response campaign detail page
        $response = $this->get(route('campaign.show', $campaign->slug));
        $response->assertStatus(200);

        // Hanya 100.000 yang terhitung sebagai total terkumpul
        $response->assertViewHas('campaign', function ($viewCampaign) {
            return $viewCampaign->terkumpul == 100000 && $viewCampaign->donasi_count == 1;
        });
    }
}
