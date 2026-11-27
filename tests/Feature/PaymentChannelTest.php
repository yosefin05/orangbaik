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

        $flipId = '2502091430251230005';
        $paymentId = 'PGPWF10117309708599318549';
        $orderId = 'OB-FLIP-' . time();
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => $orderId,
            'transaction_id'     => $flipId,
            'payment_type'       => 'va',
            'transaction_status' => 'pending',
            'gateway_response'   => [
                'credential_mode' => 'sandbox',
                'flip_api_version' => 'v3',
                'link_id' => $flipId,
                'flip_payment_id' => $paymentId,
                'unique_code' => 0,
            ],
        ]);

        $payload = [
            'token' => 'test-flip-token',
            'data' => json_encode([
                'id' => $paymentId,
                'bill_link_id' => $flipId,
                'reference_id' => $orderId,
                'amount' => 50000,
                'status' => 'SUCCESSFUL',
            ]),
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
            'sapaan'             => 'Kak',
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
    public function test_admin_manual_account_page_only_lists_manual_transfer_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.payment.channel.index'));

        $response->assertOk();
        $response->assertSee('Rekening tujuan donasi');
        $response->assertSee('Nama pemilik rekening');
        $response->assertDontSee('GoPay');
        $response->assertDontSee('Virtual Account');
    }

    public function test_manual_account_batch_endpoint_cannot_edit_provider_channels(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $providerChannel = PaymentChannel::where('channel_code', 'qris')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.payment.channel.batch'), [
            'channels' => [
                [
                    'id' => $providerChannel->id,
                    'name' => 'QRIS',
                    'channel_code' => 'qris',
                    'account_name' => 'Not a bank account',
                    'account_number' => '000000',
                    'payment_type' => 'transfer',
                    'is_active' => '1',
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertSame('QRIS', $providerChannel->fresh()->name);
        $this->assertNull($providerChannel->fresh()->account_number);
    }

    public function test_provider_detail_manages_methods_without_manual_bank_account_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $midtrans = PaymentGateway::where('code', 'midtrans')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.payment.gateway.show', $midtrans));

        $response->assertOk();
        $response->assertSee('Metode yang ditampilkan di OrangBaik');
        $response->assertSee('dashboard merchant Midtrans');
        $response->assertDontSee('Nama pemilik rekening');
        $response->assertDontSee('Nomor rekening');
    }

    public function test_payment_gateway_snapshot_preserves_original_provider_after_channel_routing_changes(): void
    {
        $donasi = $this->createDonasi();
        $midtrans = PaymentGateway::where('code', 'midtrans')->firstOrFail();
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();
        $channel = PaymentChannel::where('channel_code', 'qris')->firstOrFail();

        $pembayaran = Pembayaran::create([
            'donasi_id' => $donasi->id,
            'payment_channel_id' => $channel->id,
            'payment_gateway_id' => $midtrans->id,
            'order_id' => 'OB-ROUTING-SNAPSHOT-' . uniqid(),
            'transaction_status' => 'pending',
        ]);

        $channel->update(['payment_gateway_id' => $flip->id]);

        $this->assertSame('midtrans', $pembayaran->fresh()->resolvedGateway()->code);
        $this->assertSame('flip', $channel->fresh()->gateway->code);
    }

    public function test_gateway_payment_type_capabilities_match_current_adapters(): void
    {
        $midtrans = PaymentGateway::where('code', 'midtrans')->firstOrFail();
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();
        $manual = PaymentGateway::where('code', 'manual')->firstOrFail();

        $this->assertTrue($midtrans->supportsPaymentType('instant'));
        $this->assertFalse($midtrans->supportsPaymentType('va'));
        $this->assertTrue($flip->supportsPaymentType('va'));
        $this->assertTrue($flip->supportsPaymentType('instant'));
        $this->assertTrue($manual->supportsPaymentType('transfer'));
    }


    public function test_admin_can_register_midtrans_ewallet_methods_from_provider_catalog(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $midtrans = PaymentGateway::where('code', 'midtrans')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.payment.gateway.show', $midtrans))
            ->assertOk()
            ->assertSee('OVO')
            ->assertSee('DANA')
            ->assertSee('ShopeePay')
            ->assertSee('Daftar metode yang bisa ditambahkan');

        $this->post(route('admin.payment.gateway.methods.store', $midtrans), [
            'channel_code' => 'ovo',
        ])->assertRedirect(route('admin.payment.gateway.show', $midtrans))
            ->assertSessionHas('success');

        $channel = PaymentChannel::where('payment_gateway_id', $midtrans->id)
            ->where('channel_code', 'ovo')
            ->firstOrFail();

        $this->assertSame('instant', $channel->payment_type);
        $this->assertFalse($channel->is_active);
    }

    public function test_admin_can_register_flip_virtual_account_methods_from_provider_catalog(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.payment.gateway.show', $flip))
            ->assertOk()
            ->assertSee('Bank BCA')
            ->assertSee('Virtual Account, OVO, DANA, dan QRIS melalui Direct API v3')
            ->assertSee('SeaBank Virtual Account')
            ->assertSee('OVO')
            ->assertSee('DANA')
            ->assertSee('QRIS');

        $this->post(route('admin.payment.gateway.methods.store', $flip), [
            'channel_code' => 'seabank',
        ])->assertRedirect(route('admin.payment.gateway.show', $flip))
            ->assertSessionHas('success');

        $channel = PaymentChannel::where('payment_gateway_id', $flip->id)
            ->where('channel_code', 'seabank')
            ->firstOrFail();

        $this->assertSame('va', $channel->payment_type);
        $this->assertFalse($channel->is_active);
    }


    public function test_flip_ovo_dana_and_qris_are_registered_as_inactive_direct_api_v3_methods(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();

        foreach (['ovo', 'dana', 'qris'] as $code) {
            $this->actingAs($admin)
                ->post(route('admin.payment.gateway.methods.store', $flip), [
                    'channel_code' => $code,
                ])
                ->assertRedirect(route('admin.payment.gateway.show', $flip))
                ->assertSessionHas('success');

            $channel = PaymentChannel::where('payment_gateway_id', $flip->id)
                ->where('channel_code', $code)
                ->firstOrFail();

            $this->assertSame('instant', $channel->payment_type);
            $this->assertFalse($channel->is_active);
        }
    }

    public function test_flip_direct_api_v3_creates_ovo_payment_with_staging_credentials(): void
    {
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();
        $flip->update([
            'config' => PaymentGateway::secureConfigForStorage([
                'credential_mode' => 'staging',
                'staging_api_key' => 'test-staging-secret',
                'staging_webhook_token' => 'test-staging-webhook',
                'staging_base_url' => 'https://staging.flip.id',
            ]),
        ]);

        $channel = PaymentChannel::create([
            'payment_gateway_id' => $flip->id,
            'name' => 'OVO',
            'channel_code' => 'ovo',
            'payment_type' => 'instant',
            'sort_order' => 99,
            'is_active' => false,
        ]);
        $channel->load('gateway');

        $donasi = $this->createDonasi();
        $pembayaran = Pembayaran::create([
            'donasi_id' => $donasi->id,
            'payment_channel_id' => $channel->id,
            'payment_gateway_id' => $flip->id,
            'payment_token' => (string) \Illuminate\Support\Str::uuid(),
            'order_id' => 'OB-FLIP-V3-' . uniqid(),
            'payment_type' => 'instant',
            'transaction_status' => 'pending',
        ]);

        Http::fake([
            'https://staging.flip.id/big_api/v3/pwf/bill' => Http::response([
                'link_id' => '2502091430251230005',
                'payment_url' => 'https://checkout.flip.example.test/pay/123',
                'bill_payment' => [
                    'id' => 'PGPWF10117309708599318549',
                    'amount' => 50000,
                    'unique_code' => 0,
                    'receiver_bank_account' => [
                        'account_number' => null,
                        'account_holder' => 'Budi Santoso',
                    ],
                ],
            ], 200),
        ]);

        $result = app(FlipService::class)->createEwalletDirectApiV3($donasi, $pembayaran, $channel);

        $this->assertSame('redirect', $result['type']);
        $this->assertSame('https://checkout.flip.example.test/pay/123', $result['redirect_url']);
        $this->assertSame('2502091430251230005', $pembayaran->fresh()->transaction_id);
        $this->assertSame('PGPWF10117309708599318549', data_get($pembayaran->fresh()->gateway_response, 'flip_payment_id'));
        $this->assertSame('staging', data_get($pembayaran->fresh()->gateway_response, 'credential_mode'));

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://staging.flip.id/big_api/v3/pwf/bill'
            && $request['sender_bank'] === 'ovo'
            && $request['sender_bank_type'] === 'wallet_account'
            && $request['step'] === 'direct_api'
        );
    }

    public function test_flip_direct_api_v3_creates_qris_payment_with_staging_credentials(): void
    {
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();
        $flip->update([
            'config' => PaymentGateway::secureConfigForStorage([
                'credential_mode' => 'staging',
                'staging_api_key' => 'test-staging-secret',
                'staging_webhook_token' => 'test-staging-webhook',
                'staging_base_url' => 'https://staging.flip.id',
            ]),
        ]);

        $channel = PaymentChannel::create([
            'payment_gateway_id' => $flip->id,
            'name' => 'QRIS',
            'channel_code' => 'qris',
            'payment_type' => 'instant',
            'sort_order' => 99,
            'is_active' => false,
        ]);
        $channel->load('gateway');

        $donasi = $this->createDonasi();
        $pembayaran = Pembayaran::create([
            'donasi_id' => $donasi->id,
            'payment_channel_id' => $channel->id,
            'payment_gateway_id' => $flip->id,
            'payment_token' => (string) \Illuminate\Support\Str::uuid(),
            'order_id' => 'OB-FLIP-QRIS-' . uniqid(),
            'payment_type' => 'instant',
            'transaction_status' => 'pending',
        ]);

        Http::fake([
            'https://staging.flip.id/big_api/v3/pwf/bill' => Http::response([
                'link_id' => '2502091430251230006',
                'payment_url' => 'https://checkout.flip.example.test/qris/123',
                'bill_payment' => [
                    'id' => 'PGPWF10117309708599318550',
                    'amount' => 50000,
                    'unique_code' => 0,
                    'receiver_bank_account' => [
                        'account_number' => null,
                        'account_holder' => 'Budi Santoso',
                        'qr_code_data' => '000201010212...',
                    ],
                ],
            ], 200),
        ]);

        $result = app(FlipService::class)->createEwalletDirectApiV3($donasi, $pembayaran, $channel);

        $this->assertSame('redirect', $result['type']);
        $this->assertSame('flip_qris', $result['provider_type']);
        $this->assertSame('000201010212...', $result['qr_code_data']);
        $this->assertSame('2502091430251230006', $pembayaran->fresh()->transaction_id);

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://staging.flip.id/big_api/v3/pwf/bill'
            && $request['sender_bank'] === 'qris'
            && $request['sender_bank_type'] === 'wallet_account'
            && $request['step'] === 'direct_api'
        );
    }

    public function test_flip_direct_api_v3_creates_virtual_account_using_staging_credentials(): void
    {
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();
        $flip->update([
            'config' => PaymentGateway::secureConfigForStorage([
                'credential_mode' => 'staging',
                'staging_api_key' => 'test-staging-secret',
                'staging_webhook_token' => 'test-staging-webhook',
                'staging_base_url' => 'https://staging.flip.id',
            ]),
        ]);

        $channel = PaymentChannel::create([
            'payment_gateway_id' => $flip->id,
            'name' => 'BCA Virtual Account',
            'channel_code' => 'bca',
            'payment_type' => 'va',
            'sort_order' => 99,
            'is_active' => false,
        ]);
        $channel->load('gateway');

        $donasi = $this->createDonasi();
        $pembayaran = Pembayaran::create([
            'donasi_id' => $donasi->id,
            'payment_channel_id' => $channel->id,
            'payment_gateway_id' => $flip->id,
            'payment_token' => (string) \Illuminate\Support\Str::uuid(),
            'order_id' => 'OB-FLIP-VA-' . uniqid(),
            'payment_type' => 'va',
            'transaction_status' => 'pending',
        ]);

        Http::fake([
            'https://staging.flip.id/big_api/v3/pwf/bill' => Http::response([
                'link_id' => '2502091430251230007',
                'payment_url' => 'https://checkout.flip.example.test/va/123',
                'expired_date' => '2026-11-26 20:00',
                'bill_payment' => [
                    'id' => 'PGPWF10117309708599318551',
                    'amount' => 50000,
                    'unique_code' => 0,
                    'receiver_bank_account' => [
                        'account_number' => '1900800000106689',
                        'account_holder' => 'Budi Santoso',
                    ],
                ],
            ], 200),
        ]);

        $result = app(FlipService::class)->createVirtualAccount($donasi, $pembayaran, $channel);

        $this->assertSame('1900800000106689', $result['account_number']);
        $this->assertSame('Budi Santoso', $result['account_name']);
        $this->assertSame('2502091430251230007', $pembayaran->fresh()->transaction_id);

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://staging.flip.id/big_api/v3/pwf/bill'
            && $request['sender_bank'] === 'bca'
            && $request['sender_bank_type'] === 'virtual_account'
            && $request['step'] === 'direct_api'
        );
    }

    public function test_flip_staging_configuration_does_not_require_legacy_sandbox_credentials(): void
    {
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();
        $flip->update([
            'config' => PaymentGateway::secureConfigForStorage([
                'credential_mode' => 'staging',
                'staging_api_key' => 'test-staging-secret',
                'staging_webhook_token' => 'test-staging-webhook',
                'staging_base_url' => 'https://staging.flip.id',
            ]),
        ]);

        $this->assertTrue($flip->fresh()->isConfigured());
    }

    public function test_provider_catalog_rejects_methods_not_supported_by_that_provider(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.payment.gateway.show', $flip))
            ->post(route('admin.payment.gateway.methods.store', $flip), [
                'channel_code' => 'gopay',
            ])
            ->assertRedirect(route('admin.payment.gateway.show', $flip))
            ->assertSessionHas('error');

        $this->assertFalse(
            PaymentChannel::where('payment_gateway_id', $flip->id)
                ->where('channel_code', 'gopay')
                ->exists()
        );
    }



    public function test_flip_422_error_list_is_logged_as_sanitized_structured_diagnostics(): void
    {
        $flip = PaymentGateway::where('code', 'flip')->firstOrFail();
        $flip->update([
            'config' => PaymentGateway::secureConfigForStorage([
                'credential_mode' => 'staging',
                'staging_api_key' => 'test-staging-secret',
                'staging_webhook_token' => 'test-staging-webhook',
                'staging_base_url' => 'https://staging.flip.id',
            ]),
        ]);

        $channel = PaymentChannel::create([
            'payment_gateway_id' => $flip->id,
            'name' => 'BCA Virtual Account',
            'channel_code' => 'bca',
            'payment_type' => 'va',
            'sort_order' => 99,
            'is_active' => false,
        ]);
        $channel->load('gateway');

        $donasi = $this->createDonasi();
        $pembayaran = Pembayaran::create([
            'donasi_id' => $donasi->id,
            'payment_channel_id' => $channel->id,
            'payment_gateway_id' => $flip->id,
            'payment_token' => (string) \Illuminate\Support\Str::uuid(),
            'order_id' => 'OB-FLIP-422-' . uniqid(),
            'payment_type' => 'va',
            'transaction_status' => 'pending',
        ]);

        \Illuminate\Support\Facades\Log::spy();

        Http::fake([
            'https://staging.flip.id/big_api/v3/pwf/bill' => Http::response([
                'code' => 'VALIDATION_ERROR',
                'errors' => [
                    [
                        'attribute' => 'sender_bank',
                        'code' => 'METHOD_NOT_ENABLED',
                        'message' => 'Bank method unavailable for test@example.com, contact 08123456789.',
                    ],
                ],
            ], 422),
        ]);

        try {
            app(FlipService::class)->createVirtualAccount($donasi, $pembayaran, $channel);
            $this->fail('Expected Flip HTTP 422 to throw a RuntimeException.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('HTTP 422', $exception->getMessage());
        }

        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')
            ->withArgs(function ($message, $context) {
                $providerError = $context['provider_errors'][0] ?? [];
                $providerMessage = $providerError['message'] ?? '';

                return $message === 'Flip Direct API v3 rejected bill creation'
                    && ($context['provider_error_code'] ?? null) === 'VALIDATION_ERROR'
                    && ($context['provider_error_fields'] ?? []) === ['sender_bank']
                    && ($providerError['attribute'] ?? null) === 'sender_bank'
                    && ($providerError['code'] ?? null) === 'METHOD_NOT_ENABLED'
                    && str_contains($providerMessage, '[redacted-email]')
                    && str_contains($providerMessage, '[redacted-phone]')
                    && !str_contains($providerMessage, 'test@example.com')
                    && !str_contains($providerMessage, '08123456789');
            });
    }

    public function test_replacing_a_legacy_public_transfer_proof_does_not_delete_historical_file(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $manualChannel = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->where('channel_code', 'BSI - NM')
            ->firstOrFail();

        $legacyPath = 'bukti-transfer/legacy-proof.jpg';
        Storage::disk('public')->put($legacyPath, 'historical-proof-content');

        $payment = (new ManualTransferService())->createTransaction($this->createDonasi(), $manualChannel);
        $payment->update(['bukti_transfer' => $legacyPath]);

        $newPath = (new ManualTransferService())->saveBuktiTransfer(
            $payment,
            UploadedFile::fake()->image('replacement-proof.jpg')
        );

        Storage::disk('public')->assertExists($legacyPath);
        Storage::disk('private')->assertExists($newPath);
        $this->assertSame($newPath, $payment->fresh()->bukti_transfer);
    }



    public function test_replacing_a_private_transfer_proof_deletes_old_file_only_after_successful_replacement(): void
    {
        Storage::fake('private');

        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $manualChannel = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->where('channel_code', 'BSI - NM')
            ->firstOrFail();

        $oldPath = 'bukti-transfer/previous-private-proof.jpg';
        Storage::disk('private')->put($oldPath, 'previous-proof-content');

        $payment = (new ManualTransferService())->createTransaction($this->createDonasi(), $manualChannel);
        $payment->update(['bukti_transfer' => $oldPath]);

        $newPath = (new ManualTransferService())->saveBuktiTransfer(
            $payment,
            UploadedFile::fake()->image('replacement-private-proof.jpg')
        );

        Storage::disk('private')->assertMissing($oldPath);
        Storage::disk('private')->assertExists($newPath);
        $this->assertSame($newPath, $payment->fresh()->bukti_transfer);
    }

    public function test_private_transfer_proof_response_is_not_cacheable_and_has_security_headers(): void
    {
        Storage::fake('private');

        $donasi = $this->createDonasi();
        $manualGateway = PaymentGateway::where('code', 'manual')->firstOrFail();
        $manualChannel = PaymentChannel::where('payment_gateway_id', $manualGateway->id)
            ->where('channel_code', 'BSI - NM')
            ->firstOrFail();

        $service = new ManualTransferService();
        $payment = $service->createTransaction($donasi, $manualChannel);
        $service->saveBuktiTransfer($payment, UploadedFile::fake()->image('private-proof.jpg'));

        $response = $this->actingAs($donasi->user)
            ->get(route('donasi.bayar.bukti_transfer', $payment->id));

        $response->assertOk()
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'no-referrer');

        $cacheControl = strtolower((string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringNotContainsString('public', $cacheControl);
    }


}
