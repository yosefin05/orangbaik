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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Batch2PendingPaymentRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;
    protected User $admin;
    protected Campaign $campaign;
    protected PaymentGateway $gatewayManual;
    protected PaymentGateway $gatewayMidtrans;
    protected PaymentGateway $gatewayFlip;
    protected PaymentChannel $channelManual;
    protected PaymentChannel $channelMidtrans;
    protected PaymentChannel $channelFlip;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Config::set('midtrans.serverKey', 'SB-Mid-server-TESTKEY12345');
        Config::set('midtrans.clientKey', 'SB-Mid-client-TESTKEY12345');
        Config::set('payment.flip.webhook_token', 'FLIP_SECRET_TOKEN_123');

        $this->userA = User::factory()->create(['role' => 'user']);
        $this->userB = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $penggalang = Penggalang_Dana::create([
            'user_id'          => $this->userA->id,
            'verified_by'      => $this->admin->id,
            'jenis_penggalang' => 'organisasi',
            'foto_profil'      => 'profil.jpg',
            'nama_penggalang'  => 'Yayasan OrangBaik',
            'email'            => 'yayasan@orangbaik.id',
            'no_telepon'       => '08123456789',
            'alamat'           => 'Jakarta',
            'status'           => 'approved',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Kemanusiaan',
            'slug'          => 'kemanusiaan',
        ]);

        $this->campaign = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id'        => $kategori->id,
            'thumbnail'          => 'thumb.jpg',
            'judul'              => 'Bantu Pendidikan Yatim',
            'slug'               => 'bantu-pendidikan-yatim',
            'deskripsi'          => 'Deskripsi campaign bantuan',
            'tanggal_mulai'      => now()->subDay(),
            'tanggal_berakhir'   => now()->addDays(30),
            'target_donasi'      => 10000000,
            'minimal_donasi'     => 10000,
            'is_active'          => true,
            'campaign_type'      => 'regular',
            'approval_status'    => 'approved',
        ]);

        $this->gatewayManual = PaymentGateway::create([
            'name'      => 'Manual Transfer',
            'code'      => 'manual',
            'driver'    => 'manual',
            'is_active' => true,
        ]);

        $this->channelManual = PaymentChannel::create([
            'payment_gateway_id' => $this->gatewayManual->id,
            'name'               => 'BCA Manual',
            'channel_code'       => 'bca_manual',
            'account_name'       => 'Yayasan OrangBaik',
            'account_number'     => '1234567890',
            'payment_type'       => 'transfer',
            'sort_order'         => 1,
            'is_active'          => true,
        ]);

        $this->gatewayMidtrans = PaymentGateway::create([
            'name'      => 'Midtrans',
            'code'      => 'midtrans',
            'driver'    => 'midtrans',
            'is_active' => true,
        ]);

        $this->channelMidtrans = PaymentChannel::create([
            'payment_gateway_id' => $this->gatewayMidtrans->id,
            'name'               => 'GoPay / QRIS',
            'channel_code'       => 'gopay',
            'payment_type'       => 'instant',
            'sort_order'         => 2,
            'is_active'          => true,
        ]);

        $this->gatewayFlip = PaymentGateway::create([
            'name'      => 'Flip',
            'code'      => 'flip',
            'driver'    => 'flip',
            'is_active' => true,
        ]);

        $this->channelFlip = PaymentChannel::create([
            'payment_gateway_id' => $this->gatewayFlip->id,
            'name'               => 'BNI Virtual Account (Flip)',
            'channel_code'       => 'bni',
            'payment_type'       => 'va',
            'sort_order'         => 3,
            'is_active'          => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. GUEST PAYMENT IDOR TESTS
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function guest_donation_creation_generates_unique_payment_token()
    {
        $response = $this->postJson(route('donasi.store', $this->campaign->slug), [
            'payment_channel_id' => $this->channelManual->id,
            'nominal'            => 50000,
            'nama_donatur'       => 'Guest Donatur',
            'no_hp'              => '081299990001',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $pembayaran = Pembayaran::latest()->first();
        $this->assertNotNull($pembayaran->payment_token);
        $this->assertNull($pembayaran->donasi->user_id);

        // Redirect URL must contain the secure payment_token
        $this->assertStringContainsString('token=' . $pembayaran->payment_token, $response->json('redirect_url'));
    }

    /** @test */
    public function guest_can_access_own_instruction_page_with_valid_token()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => null, // Guest
            'nama_donatur' => 'Guest Donatur',
            'no_hp'        => '081299990002',
            'nominal'      => 50000,
        ]);

        $token = (string) Str::uuid();
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => $token,
            'order_id'           => 'OB-TEST-GUEST-1',
            'transaction_status' => 'pending',
            'payment_type'       => 'transfer',
        ]);

        $response = $this->get(route('donasi.bayar.instruksi', [
            'pembayaran' => $pembayaran->id,
            'token'      => $token,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Instruksi Pembayaran');
        $response->assertSee('OB-TEST-GUEST-1');
    }

    /** @test */
    public function guest_cannot_access_instruction_page_without_token()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => null,
            'nama_donatur' => 'Guest Donatur',
            'no_hp'        => '081299990003',
            'nominal'      => 50000,
        ]);

        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-TEST-GUEST-2',
            'transaction_status' => 'pending',
            'payment_type'       => 'transfer',
        ]);

        // Access with payment ID only (no token) -> MUST be 403 Forbidden
        $response = $this->get(route('donasi.bayar.instruksi', [
            'pembayaran' => $pembayaran->id,
        ]));

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_instruction_page_with_invalid_or_other_token()
    {
        $donasi1 = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => null,
            'nama_donatur' => 'Guest 1',
            'no_hp'        => '081299990004',
            'nominal'      => 50000,
        ]);

        $pembayaran1 = Pembayaran::create([
            'donasi_id'          => $donasi1->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-TEST-GUEST-3',
            'transaction_status' => 'pending',
            'payment_type'       => 'transfer',
        ]);

        $fakeToken = (string) Str::uuid();

        // With invalid token
        $response = $this->get(route('donasi.bayar.instruksi', [
            'pembayaran' => $pembayaran1->id,
            'token'      => $fakeToken,
        ]));

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_authenticated_user_payment_instruction()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081299990005',
            'nominal'      => 100000,
        ]);

        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-TEST-AUTH-1',
            'transaction_status' => 'pending',
            'payment_type'       => 'transfer',
        ]);

        // Unauthenticated guest attempting to access user A's payment
        $response = $this->get(route('donasi.bayar.instruksi', [
            'pembayaran' => $pembayaran->id,
            'token'      => $pembayaran->payment_token,
        ]));

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_upload_transfer_proof_requires_valid_token()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => null,
            'nama_donatur' => 'Guest Donatur',
            'no_hp'        => '081299990006',
            'nominal'      => 75000,
        ]);

        $token = (string) Str::uuid();
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => $token,
            'order_id'           => 'OB-TEST-UPLOAD-1',
            'transaction_status' => 'pending',
            'payment_type'       => 'transfer',
        ]);

        $file = UploadedFile::fake()->image('bukti.jpg');

        // Without token -> 403
        $responseNoToken = $this->post(route('donasi.bayar.upload_bukti', $pembayaran->id), [
            'bukti_transfer' => $file,
        ]);
        $responseNoToken->assertStatus(403);

        // With valid token -> 302 (success upload)
        $responseWithToken = $this->post(route('donasi.bayar.upload_bukti', [
            'pembayaran' => $pembayaran->id,
            'token'      => $token,
        ]), [
            'bukti_transfer' => $file,
            'token'          => $token,
        ]);

        $responseWithToken->assertStatus(302);
        $responseWithToken->assertSessionHas('success');

        $pembayaran->refresh();
        $this->assertNotNull($pembayaran->bukti_transfer);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. AUTHENTICATED DONOR RECOVERY & IDOR TESTS
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function authenticated_donor_can_access_own_instruction_and_riwayat()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081211112222',
            'nominal'      => 100000,
        ]);

        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-TEST-AUTH-OWN',
            'transaction_status' => 'pending',
            'payment_type'       => 'transfer',
        ]);

        // User A accesses own instruction without needing token query param
        $response = $this->actingAs($this->userA)
            ->get(route('donasi.bayar.instruksi', $pembayaran->id));

        $response->assertStatus(200);
        $response->assertSee('OB-TEST-AUTH-OWN');

        // User A sees pending donation with Lanjutkan Pembayaran button in riwayat
        $riwayatResponse = $this->actingAs($this->userA)
            ->get(route('riwayat.donasi'));

        $riwayatResponse->assertStatus(200);
        $riwayatResponse->assertSee('Lanjutkan Pembayaran');
        $riwayatResponse->assertSee('OB-TEST-AUTH-OWN');
    }

    /** @test */
    public function authenticated_user_cannot_access_or_resume_another_users_payment()
    {
        $donasiA = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081211112222',
            'nominal'      => 100000,
        ]);

        $pembayaranA = Pembayaran::create([
            'donasi_id'          => $donasiA->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-USER-A-ORDER',
            'transaction_status' => 'pending',
            'payment_type'       => 'transfer',
        ]);

        // User B attempts to access User A's instruction
        $response = $this->actingAs($this->userB)
            ->get(route('donasi.bayar.instruksi', $pembayaranA->id));

        $response->assertStatus(403);

        // User B attempts to resume User A's payment
        $resumeResponse = $this->actingAs($this->userB)
            ->get(route('donasi.resume', $pembayaranA->id));

        $resumeResponse->assertStatus(403);

        // User B cannot see User A's donation in riwayat
        $riwayatResponse = $this->actingAs($this->userB)
            ->get(route('riwayat.donasi'));

        $riwayatResponse->assertStatus(200);
        $riwayatResponse->assertDontSee('OB-USER-A-ORDER');
    }

    /** @test */
    public function resume_pending_payment_does_not_create_duplicate_donation_or_payment()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081211113333',
            'nominal'      => 200000,
        ]);

        $snapToken = 'snap-token-uuid-12345';
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelMidtrans->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-MIDTRANS-RESUME-1',
            'snap_token'         => $snapToken,
            'transaction_status' => 'pending',
            'payment_type'       => 'gopay',
        ]);

        $donasiCountBefore = Donasi::count();
        $pembayaranCountBefore = Pembayaran::count();

        // Call resume endpoint via JSON / AJAX
        $response = $this->actingAs($this->userA)
            ->getJson(route('donasi.resume', $pembayaran->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success'    => true,
            'type'       => 'midtrans',
            'snap_token' => $snapToken,
            'order_id'   => 'OB-MIDTRANS-RESUME-1',
            'donasi_id'  => $donasi->id,
        ]);

        // Verify zero new rows were created
        $this->assertEquals($donasiCountBefore, Donasi::count());
        $this->assertEquals($pembayaranCountBefore, Pembayaran::count());
    }

    /** @test */
    public function already_settled_payment_cannot_be_resumed()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081211114444',
            'nominal'      => 100000,
        ]);

        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-SETTLED-RESUME',
            'transaction_status' => 'settlement',
            'paid_at'            => now(),
            'payment_type'       => 'transfer',
        ]);

        $response = $this->actingAs($this->userA)
            ->getJson(route('donasi.resume', $pembayaran->id));

        $response->assertStatus(422);
        $response->assertJson(['success' => false, 'status' => 'settlement']);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. WEBHOOK AMOUNT INTEGRITY & STATUS TESTS
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function midtrans_webhook_with_matching_amount_settles_and_updates_campaign_total()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081211115555',
            'nominal'      => 150000,
        ]);

        $orderId = 'OB-MIDTRANS-MATCH-1';
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelMidtrans->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => $orderId,
            'transaction_status' => 'pending',
            'payment_type'       => 'gopay',
        ]);

        $serverKey = config('midtrans.serverKey');
        $statusCode = '200';
        $grossAmount = '150000.00';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'settlement',
            'payment_type'       => 'gopay',
            'transaction_id'     => 'TRX-MID-001',
        ];

        $response = $this->postJson(route('payment.midtrans.webhook'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->transaction_status);
        $this->assertNotNull($pembayaran->paid_at);

        // Campaign totals must increase by exactly 150.000
        $this->assertEquals(150000, $this->campaign->getTotalDonasiSuccess());
        $this->assertEquals(1, $this->campaign->getDonaturCount());
    }

    /** @test */
    public function midtrans_webhook_with_mismatched_amount_is_rejected_and_does_not_settle()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081211116666',
            'nominal'      => 500000, // Expected 500.000
        ]);

        $orderId = 'OB-MIDTRANS-MISMATCH-1';
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelMidtrans->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => $orderId,
            'transaction_status' => 'pending',
            'payment_type'       => 'gopay',
        ]);

        $serverKey = config('midtrans.serverKey');
        $statusCode = '200';
        $tamperedGrossAmount = '5000.00'; // Tampered amount (5.000 instead of 500.000)
        $signature = hash('sha512', $orderId . $statusCode . $tamperedGrossAmount . $serverKey);

        $payload = [
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $tamperedGrossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'settlement',
            'payment_type'       => 'gopay',
            'transaction_id'     => 'TRX-MID-002',
        ];

        $response = $this->postJson(route('payment.midtrans.webhook'), $payload);

        // Mismatched amount must return 400 Bad Request
        $response->assertStatus(400);

        $pembayaran->refresh();
        $this->assertEquals('pending', $pembayaran->transaction_status);
        $this->assertNull($pembayaran->paid_at);

        // Campaign total must remain 0
        $this->assertEquals(0, $this->campaign->getTotalDonasiSuccess());
    }

    /** @test */
    public function flip_webhook_with_matching_amount_settles_and_updates_campaign_total()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081211117777',
            'nominal'      => 250000,
        ]);

        $flipLinkId = '987654321';
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelFlip->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-FLIP-MATCH-1',
            'transaction_id'     => $flipLinkId,
            'transaction_status' => 'pending',
            'payment_type'       => 'va',
            'gateway_response'   => ['link_id' => $flipLinkId, 'amount' => 250000],
        ]);

        $payload = [
            'token'        => 'FLIP_SECRET_TOKEN_123',
            'bill_link_id' => $flipLinkId,
            'status'       => 'SUCCESSFUL',
            'amount'       => 250000,
        ];

        $response = $this->withHeaders(['X-CALLBACK-TOKEN' => 'FLIP_SECRET_TOKEN_123'])
            ->postJson(route('payment.flip.webhook'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $pembayaran->refresh();
        $this->assertEquals('settlement', $pembayaran->transaction_status);
        $this->assertNotNull($pembayaran->paid_at);

        // Campaign totals must increase by 250.000
        $this->assertEquals(250000, $this->campaign->getTotalDonasiSuccess());
    }

    /** @test */
    public function flip_webhook_with_mismatched_amount_is_rejected_and_does_not_settle()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081211118888',
            'nominal'      => 300000,
        ]);

        $flipLinkId = '987654322';
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelFlip->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-FLIP-MISMATCH-1',
            'transaction_id'     => $flipLinkId,
            'transaction_status' => 'pending',
            'payment_type'       => 'va',
            'gateway_response'   => ['link_id' => $flipLinkId, 'amount' => 300000],
        ]);

        $payload = [
            'token'        => 'FLIP_SECRET_TOKEN_123',
            'bill_link_id' => $flipLinkId,
            'status'       => 'SUCCESSFUL',
            'amount'       => 30000, // Mismatched amount
        ];

        $response = $this->withHeaders(['X-CALLBACK-TOKEN' => 'FLIP_SECRET_TOKEN_123'])
            ->postJson(route('payment.flip.webhook'), $payload);

        $response->assertStatus(400);

        $pembayaran->refresh();
        $this->assertEquals('pending', $pembayaran->transaction_status);
        $this->assertNull($pembayaran->paid_at);
        $this->assertEquals(0, $this->campaign->getTotalDonasiSuccess());
    }

    /** @test */
    public function duplicate_webhook_is_idempotent_and_does_not_duplicate_campaign_totals()
    {
        $donasi = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'user_id'      => $this->userA->id,
            'nama_donatur' => $this->userA->name,
            'no_hp'        => '081211119999',
            'nominal'      => 100000,
        ]);

        $orderId = 'OB-MIDTRANS-IDEMPOTENT-1';
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $this->channelMidtrans->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => $orderId,
            'transaction_status' => 'pending',
            'payment_type'       => 'gopay',
        ]);

        $serverKey = config('midtrans.serverKey');
        $statusCode = '200';
        $grossAmount = '100000.00';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'settlement',
            'payment_type'       => 'gopay',
            'transaction_id'     => 'TRX-MID-003',
        ];

        // First webhook delivery
        $res1 = $this->postJson(route('payment.midtrans.webhook'), $payload);
        $res1->assertStatus(200);

        $this->assertEquals(100000, $this->campaign->getTotalDonasiSuccess());
        $this->assertEquals(1, $this->campaign->getDonaturCount());

        // Second duplicate webhook delivery
        $res2 = $this->postJson(route('payment.midtrans.webhook'), $payload);
        $res2->assertStatus(200);

        // Totals must remain exactly 100.000 and count 1 (no duplicate addition)
        $this->assertEquals(100000, $this->campaign->getTotalDonasiSuccess());
        $this->assertEquals(1, $this->campaign->getDonaturCount());
    }

    /** @test */
    public function pending_and_expired_and_failed_statuses_do_not_contribute_to_campaign_total()
    {
        // 1. Pending donation
        $d1 = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'nama_donatur' => 'Pending Donor',
            'nominal'      => 50000,
        ]);
        Pembayaran::create([
            'donasi_id'          => $d1->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-PENDING-ONLY',
            'transaction_status' => 'pending',
        ]);

        // 2. Expired donation
        $d2 = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'nama_donatur' => 'Expired Donor',
            'nominal'      => 75000,
        ]);
        Pembayaran::create([
            'donasi_id'          => $d2->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-EXPIRED-ONLY',
            'transaction_status' => 'expired',
        ]);

        // 3. Failed donation
        $d3 = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'nama_donatur' => 'Failed Donor',
            'nominal'      => 80000,
        ]);
        Pembayaran::create([
            'donasi_id'          => $d3->id,
            'payment_channel_id' => $this->channelManual->id,
            'payment_token'      => (string) Str::uuid(),
            'order_id'           => 'OB-FAILED-ONLY',
            'transaction_status' => 'failed',
        ]);

        $this->assertEquals(0, $this->campaign->getTotalDonasiSuccess());
        $this->assertEquals(0, $this->campaign->getDonaturCount());
    }
}
