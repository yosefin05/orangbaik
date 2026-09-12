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
use App\Models\Legalitas;
use App\Models\LaporanKeuangan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PreLaunchHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Penggalang_Dana $penggalang;
    protected Campaign $campaign;
    protected PaymentChannel $channel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->penggalang = Penggalang_Dana::create([
            'user_id'          => $this->user->id,
            'verified_by'      => $this->admin->id,
            'jenis_penggalang' => 'organisasi',
            'foto_profil'      => 'profil.jpg',
            'nama_penggalang'  => 'Yayasan Kita',
            'email'            => 'yayasan@example.com',
            'no_telepon'       => '08123456789',
            'alamat'           => 'Jakarta',
            'status'           => 'approved',
        ]);


        $kategori = Kategori::create([
            'nama_kategori' => 'Kemanusiaan',
            'slug'          => 'kemanusiaan',
        ]);

        $this->campaign = Campaign::create([
            'penggalang_dana_id' => $this->penggalang->id,
            'kategori_id'        => $kategori->id,
            'thumbnail'          => 'thumb.jpg',
            'judul'              => 'Bantu Korban Bencana',
            'slug'               => 'bantu-korban-bencana',
            'deskripsi'          => 'Deskripsi campaign bantuan',
            'tanggal_mulai'      => now(),
            'tanggal_berakhir'   => now()->addDays(30),
            'target_donasi'      => 10000000,
            'minimal_donasi'     => 10000,
            'is_active'          => true,
            'campaign_type'      => 'regular',
            'approval_status'    => 'approved',
        ]);

        $gateway = PaymentGateway::create([
            'name'      => 'Manual Transfer',
            'code'      => 'manual',
            'driver'    => 'manual',
            'is_active' => true,
        ]);

        $this->channel = PaymentChannel::create([
            'payment_gateway_id' => $gateway->id,
            'name'               => 'Transfer Bank BCA',
            'channel_code'       => 'bca',
            'payment_type'       => 'transfer',
            'sort_order'         => 1,
            'is_active'          => true,
        ]);
    }

    /** @test */
    public function custom_nominal_and_anonymous_donation_work_correctly()
    {
        $response = $this->postJson(route('donasi.store', $this->campaign->slug), [
            'payment_channel_id' => $this->channel->id,
            'nominal_lainnya'    => 150000,
            'nama_donatur'       => 'Donatur Baik',
            'no_hp'              => '081299998888',
            'anonymous_donor'    => 'on',
            'pesan'              => 'Semoga bermanfaat',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('donasi', [
            'campaign_id'  => $this->campaign->id,
            'nominal'      => 150000,
            'nama_donatur' => 'Hamba Allah',
            'is_anonim'    => true,
            'no_hp'        => '081299998888',
        ]);
    }

    /** @test */
    public function financial_statistics_only_count_settlement_status()
    {
        // Donasi 1: Settlement (50.000)
        $donasi1 = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'nama_donatur' => 'Donatur 1',
            'no_hp'        => '08111111111',
            'nominal'      => 50000,
            'is_anonim'    => false,
        ]);
        Pembayaran::create([
            'donasi_id'          => $donasi1->id,
            'payment_channel_id' => $this->channel->id,
            'order_id'           => 'OB-TEST-1',
            'transaction_status' => 'settlement',
        ]);

        // Donasi 2: Pending (100.000) - Harusnya TIDAK dihitung
        $donasi2 = Donasi::create([
            'campaign_id'  => $this->campaign->id,
            'nama_donatur' => 'Donatur 2',
            'no_hp'        => '08222222222',
            'nominal'      => 100000,
            'is_anonim'    => false,
        ]);
        Pembayaran::create([
            'donasi_id'          => $donasi2->id,
            'payment_channel_id' => $this->channel->id,
            'order_id'           => 'OB-TEST-2',
            'transaction_status' => 'pending',
        ]);

        $this->assertEquals(50000, $this->campaign->getTotalDonasiSuccess());
        $this->assertEquals(50000, $this->campaign->getTotalDonasi());
        $this->assertEquals(1, $this->campaign->getDonaturCount());
    }

    /** @test */
    public function user_cannot_edit_other_user_penggalang_dana()
    {
        $otherUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($otherUser)
            ->get(route('penggalang_dana.edit', $this->penggalang->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function public_profile_penggalang_loads_by_id_param()
    {
        $response = $this->get(route('profil.penggalang', $this->penggalang->id));

        $response->assertStatus(200);
        $response->assertSee('Yayasan Kita');
    }

    /** @test */
    public function admin_can_crud_legalitas()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.legalitas.store'), [
                'judul'           => 'SK Kemenkumham',
                'nomor_legalitas' => 'AHU-0012345.AH.01.04',
                'file'            => \Illuminate\Http\UploadedFile::fake()->create('sk.pdf', 100, 'application/pdf'),
                'urutan'          => 1,
                'is_active'       => 1,
            ]);

        $response->assertRedirect(route('admin.legalitas.index'));
        $this->assertDatabaseHas('legalitas', [
            'judul'           => 'SK Kemenkumham',
            'nomor_legalitas' => 'AHU-0012345.AH.01.04',
        ]);
    }

    /** @test */
    public function admin_can_crud_laporan_keuangan()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.laporan-keuangan.store'), [
                'tahun'     => 2025,
                'judul'     => 'Laporan Keuangan Audit 2025',
                'deskripsi' => 'Predikat WTP',
                'file'      => \Illuminate\Http\UploadedFile::fake()->create('laporan2025.pdf', 100, 'application/pdf'),
                'urutan'    => 1,
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('admin.laporan-keuangan.index'));
        $this->assertDatabaseHas('laporan_keuangan', [
            'tahun' => 2025,
            'judul' => 'Laporan Keuangan Audit 2025',
        ]);
    }

    /** @test */
    public function approved_penggalang_can_access_penggalang_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('penggalang.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Yayasan Kita');
    }
}
