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

class AdminDonasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\KategoriSeeder::class);
        $this->seed(\Database\Seeders\PaymentGatewaySeeder::class);
        $this->seed(\Database\Seeders\PaymentChannelSeeder::class);
    }

    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    protected function createCampaignWithDonations(): Campaign
    {
        $user = User::factory()->create();
        $rand = uniqid();

        $penggalang = Penggalang_Dana::create([
            'user_id'          => $user->id,
            'verified_by'      => $user->id,
            'jenis_penggalang' => 'organisasi',
            'foto_profil'      => 'profile.jpg',
            'nama_penggalang'  => 'Yayasan ' . $rand,
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
            'judul'              => 'Campaign Donasi ' . $rand,
            'slug'               => 'campaign-donasi-' . $rand,
            'deskripsi'          => 'Deskripsi campaign',
            'tanggal_mulai'      => now(),
            'tanggal_berakhir'   => now()->addDays(30),
            'target_donasi'      => 10000000,
            'status'             => 'approved',
            'is_active'          => true,
            'minimal_donasi'     => 5000,
        ]);

        $channel = PaymentChannel::first();

        // 1. Settlement donation
        $d1 = Donasi::create([
            'campaign_id'  => $campaign->id,
            'user_id'      => $user->id,
            'nama_donatur' => 'Ahmad Donatur',
            'email'        => 'ahmad@example.com',
            'no_hp'        => '0811111111',
            'nominal'      => 50000,
            'pesan_doa'    => 'Semoga berkah selalu',
            'is_anonim'    => false,
        ]);
        Pembayaran::create([
            'donasi_id'          => $d1->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-TEST-1',
            'transaction_status' => 'settlement',
            'paid_at'            => now(),
        ]);

        // 2. Pending donation
        $d2 = Donasi::create([
            'campaign_id'  => $campaign->id,
            'user_id'      => $user->id,
            'nama_donatur' => 'Budi Santoso',
            'email'        => 'budi@example.com',
            'no_hp'        => '0822222222',
            'nominal'      => 100000,
            'pesan_doa'    => 'Semoga lekas sembuh',
            'is_anonim'    => false,
        ]);
        Pembayaran::create([
            'donasi_id'          => $d2->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-TEST-2',
            'transaction_status' => 'pending',
        ]);

        return $campaign;
    }

    public function test_admin_can_view_donasi_index_with_pagination(): void
    {
        $admin = $this->createAdminUser();
        $this->createCampaignWithDonations();

        $response = $this->actingAs($admin)->get(route('admin.donasi.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.donasi.index');
        $response->assertViewHas('donasi');
        $response->assertViewHas('totalDonasiCount', 2);
        $response->assertViewHas('totalTerkumpul', 50000);
        $response->assertViewHas('settlementCount', 1);
        $response->assertViewHas('pendingCount', 1);
        $response->assertSee('Ahmad Donatur');
        $response->assertSee('Budi Santoso');
    }

    public function test_admin_can_export_donasi_to_csv(): void
    {
        $admin = $this->createAdminUser();
        $this->createCampaignWithDonations();

        $response = $this->actingAs($admin)->get(route('admin.donasi.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="donasi_orangbaik_', $response->headers->get('Content-Disposition'));

        // Capture stream output
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('ID Donasi', $content);
        $this->assertStringContainsString('Order ID', $content);
        $this->assertStringContainsString('Ahmad Donatur', $content);
        $this->assertStringContainsString('Budi Santoso', $content);
        $this->assertStringContainsString('Settlement', $content);
        $this->assertStringContainsString('Pending', $content);
    }

    public function test_admin_can_export_donasi_with_filter(): void
    {
        $admin = $this->createAdminUser();
        $this->createCampaignWithDonations();

        $response = $this->actingAs($admin)->get(route('admin.donasi.export', ['status' => 'settlement']));

        $response->assertStatus(200);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Ahmad Donatur', $content);
        $this->assertStringNotContainsString('Budi Santoso', $content);
    }
}
