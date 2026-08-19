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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
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

    /**
     * Test PaymentGateway and PaymentChannel relationships
     */
    public function test_payment_gateway_has_channels(): void
    {
        $gateway = PaymentGateway::where('code', 'midtrans')->first();
        $this->assertNotNull($gateway);
        $this->assertTrue($gateway->channels()->count() > 0);
    }

    /**
     * Test active scope on PaymentChannel
     */
    public function test_payment_channel_active_scope(): void
    {
        $activeCount = PaymentChannel::active()->count();
        $this->assertGreaterThan(0, $activeCount);

        // Deactivate one channel
        $channel = PaymentChannel::first();
        $channel->update(['is_active' => false]);

        $newActiveCount = PaymentChannel::active()->count();
        $this->assertEquals($activeCount - 1, $newActiveCount);
    }

    /**
     * Test soft-disable when deleting a channel that has transactions
     */
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

    /**
     * Test ManualTransferService transaction creation, proof save, approve and reject
     */
    public function test_manual_transfer_service_flow(): void
    {
        Storage::fake('public');

        $manualChannel = PaymentChannel::where('payment_type', 'transfer')->first();
        $this->assertNotNull($manualChannel);

        $donasi = $this->createDonasi();
        $service = new ManualTransferService();

        $pembayaran = $service->createTransaction($donasi, $manualChannel);

        $this->assertEquals('pending', $pembayaran->transaction_status);
        $this->assertEquals('transfer', $pembayaran->payment_type);
        $this->assertStringStartsWith('OB-', $pembayaran->order_id);

        // Test upload proof
        $file = UploadedFile::fake()->image('bukti.jpg');
        $path = $service->saveBuktiTransfer($pembayaran, $file);

        $this->assertNotNull($pembayaran->fresh()->bukti_transfer);
        Storage::disk('public')->assertExists($path);

        // Test approve
        $service->approve($pembayaran);
        $this->assertEquals('settlement', $pembayaran->fresh()->transaction_status);
        $this->assertNotNull($pembayaran->fresh()->paid_at);

        // Test reject on another payment
        $donasi2 = $this->createDonasi();
        $pembayaran2 = $service->createTransaction($donasi2, $manualChannel);
        $service->reject($pembayaran2, 'Nominal tidak cocok');
        $this->assertEquals('failed', $pembayaran2->fresh()->transaction_status);
        $this->assertEquals('Nominal tidak cocok', $pembayaran2->fresh()->rejection_reason);
    }

    /**
     * Test validation: minimum donation of Rp5.000
     */
    public function test_donation_minimum_amount_validation(): void
    {
        $donasi = $this->createDonasi();
        $campaign = $donasi->campaign;
        $channel = PaymentChannel::first();

        $response = $this->postJson(route('donasi.store', $campaign->slug), [
            'payment_channel_id' => $channel->id,
            'nominal'            => 3000, // under Rp 5000
            'nama_donatur'       => 'Test User',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test donor flow: manual transfer creates pending payment and returns redirect_url
     */
    public function test_donation_manual_transfer_creates_pending_and_redirects(): void
    {
        $donasi = $this->createDonasi();
        $campaign = $donasi->campaign;
        $manualChannel = PaymentChannel::where('payment_type', 'transfer')->first();

        $response = $this->postJson(route('donasi.store', $campaign->slug), [
            'payment_channel_id' => $manualChannel->id,
            'nominal'            => 50000,
            'nama_donatur'       => 'Donatur Baik',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'type'    => 'manual_transfer',
        ]);
        $this->assertNotNull($response->json('redirect_url'));
    }

    /**
     * Test Midtrans webhook: valid signature updates status to settlement and is idempotent
     */
    public function test_midtrans_webhook_signature_and_idempotency(): void
    {
        Config::set('midtrans.serverKey', 'test-server-key');

        $donasi = $this->createDonasi();
        $channel = PaymentChannel::where('channel_code', 'qris')->first();

        $orderId = 'TEST-MID-' . time();
        $pembayaran = Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => $orderId,
            'transaction_status' => 'pending',
        ]);

        $grossAmount = '50000.00';
        $statusCode = '200';
        $serverKey = 'test-server-key';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'transaction_status' => 'settlement',
            'payment_type'       => 'qris',
            'transaction_id'     => 'TRX-123456',
        ];

        $service = new MidtransService();
        $result = $service->handleWebhook($payload);

        $this->assertTrue($result);
        $this->assertEquals('settlement', $pembayaran->fresh()->transaction_status);
        $this->assertEquals('qris', $pembayaran->fresh()->payment_type);
        $this->assertNotNull($pembayaran->fresh()->paid_at);

        // Idempotency: second webhook with status pending should not revert settlement
        $payload['transaction_status'] = 'pending';
        $service->handleWebhook($payload);
        $this->assertEquals('settlement', $pembayaran->fresh()->transaction_status);
    }
}
