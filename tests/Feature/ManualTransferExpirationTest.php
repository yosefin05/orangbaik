<?php

namespace Tests\\Feature;

use App\\Models\\Campaign;
use App\\Models\\Donasi;
use App\\Models\\Kategori;
use App\\Models\\PaymentChannel;
use App\\Models\\PaymentGateway;
use App\\Models\\Pembayaran;
use App\\Models\\Penggalang_Dana;
use App\\Models\\User;
use App\\Services\\ManualTransferService;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class ManualTransferExpirationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\\Database\\Seeders\\KategoriSeeder::class);
        $this->seed(\\Database\\Seeders\\PaymentGatewaySeeder::class);
        $this->seed(\\Database\\Seeders\\PaymentChannelSeeder::class);
    }

    private function createDonation(): Donasi
    {
        $owner = User::factory()->create();
        $suffix = uniqid();
        $fundraiser = Penggalang_Dana::create([
            'user_id' => $owner->id,
            'verified_by' => $owner->id,
            'jenis_penggalang' => 'organisasi',
            'foto_profil' => 'profile.jpg',
            'nama_penggalang' => 'Yayasan Uji ' . $suffix,
            'email' => 'yayasan-' . $suffix . '@example.com',
            'no_telepon' => '081234567890',
            'alamat' => 'Sidoarjo',
            'status' => 'approved',
        ]);
        $campaign = Campaign::create([
            'penggalang_dana_id' => $fundraiser->id,
            'kategori_id' => Kategori::firstOrFail()->id,
            'thumbnail' => 'thumb.jpg',
            'judul' => 'Campaign Uji ' . $suffix,
            'slug' => 'campaign-uji-' . $suffix,
            'deskripsi' => 'Deskripsi campaign uji',
            'tanggal_mulai' => now(),
            'tanggal_berakhir' => now()->addDays(30),
            'target_donasi' => 1000000,
            'status' => 'approved',
            'is_active' => true,
            'minimal_donasi' => 5000,
        ]);

        return Donasi::create([
            'campaign_id' => $campaign->id,
            'user_id' => $owner->id,
            'nama_donatur' => 'Donatur Uji',
            'email' => 'donatur@example.com',
            'no_hp' => '081234567890',
            'nominal' => 50000,
            'is_anonim' => false,
        ]);
    }

    private function manualChannel(): PaymentChannel
    {
        $gateway = PaymentGateway::where('code', 'manual')->firstOrFail();

        return PaymentChannel::where('payment_gateway_id', $gateway->id)->firstOrFail();
    }

    public function test_admin_can_expire_pending_manual_transfer_and_audit_the_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $donasi = $this->createDonation();
        $payment = app(ManualTransferService::class)->createTransaction($donasi, $this->manualChannel());

        $this->actingAs($admin)
            ->get(route('admin.donasi.show', $donasi->id))
            ->assertOk()
            ->assertSee('Tandai Kedaluwarsa');

        $this->patch(route('admin.donasi.expire-manual', $donasi->id), [
            'expiration_reason' => 'Sudah dihubungi melalui WhatsApp, belum merespons.',
        ])->assertRedirect()
          ->assertSessionHas('success');

        $payment->refresh();
        $this->assertSame('expired', $payment->transaction_status);
        $this->assertNotNull($payment->expired_at);
        $this->assertSame($admin->id, $payment->expired_by);
        $this->assertSame('Sudah dihubungi melalui WhatsApp, belum merespons.', $payment->expiration_reason);

        $this->get(route('admin.donasi.index', ['status' => 'expired']))
            ->assertOk()
            ->assertSee('Kedaluwarsa');

        $this->get(route('donasi.bayar.instruksi', [
            'pembayaran' => $payment->id,
            'token' => $payment->payment_token,
        ]))->assertRedirect();
    }

    public function test_expiration_rejects_non_manual_and_terminal_payments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $donasi = $this->createDonation();
        $midtrans = PaymentGateway::where('code', 'midtrans')->firstOrFail();
        $midtransChannel = PaymentChannel::where('payment_gateway_id', $midtrans->id)->firstOrFail();
        $gatewayPayment = Pembayaran::create([
            'donasi_id' => $donasi->id,
            'payment_channel_id' => $midtransChannel->id,
            'order_id' => 'MIDTRANS-EXPIRY-GUARD-' . $donasi->id,
            'transaction_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.donasi.expire-manual', $donasi->id))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame('pending', $gatewayPayment->fresh()->transaction_status);

        $manualDonation = $this->createDonation();
        $manualPayment = app(ManualTransferService::class)->createTransaction($manualDonation, $this->manualChannel());
        app(ManualTransferService::class)->expire($manualPayment, $admin->id);

        try {
            app(ManualTransferService::class)->approve($manualPayment);
            $this->fail('Expired manual payment must not be approved.');
        } catch (\\DomainException $exception) {
            $this->assertSame('expired', $manualPayment->fresh()->transaction_status);
        }
    }

    public function test_non_admin_cannot_expire_manual_transfer(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $donasi = $this->createDonation();
        app(ManualTransferService::class)->createTransaction($donasi, $this->manualChannel());

        $this->actingAs($user)
            ->patch(route('admin.donasi.expire-manual', $donasi->id))
            ->assertForbidden();
    }
}
