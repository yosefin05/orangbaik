<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Kategori;
use App\Models\Penggalang_Dana;
use App\Models\Penggalang_Dana_Dokumen;
use App\Models\PaymentGateway;
use App\Models\PaymentChannel;
use App\Models\Donasi;
use App\Models\Pembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Batch1And2EvaluationHardeningTest extends TestCase
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

    protected function createRegularUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'user',
        ], $attributes));
    }

    protected function createPenggalangDana(User $user, array $attributes = []): Penggalang_Dana
    {
        return Penggalang_Dana::create(array_merge([
            'user_id'          => $user->id,
            'jenis_penggalang' => 'organisasi',
            'foto_profil'      => 'profile.jpg',
            'nama_penggalang'  => 'Yayasan ' . uniqid(),
            'email'            => 'yayasan_' . uniqid() . '@example.com',
            'no_telepon'       => '081234567890',
            'alamat'           => 'Sidoarjo',
            'status'           => 'approved',
        ], $attributes));
    }

    /**
     * 1. Test Registrasi Auto-Login dan Redirect Verifikasi Email
     */
    public function test_user_registration_auto_logins_and_redirects_to_home(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Donatur Teladan',
            'email'                 => 'teladan@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'email' => 'teladan@example.com',
            'name'  => 'Donatur Teladan',
        ]);
    }

    /**
     * 2. Test Registrasi Email Unik
     */
    public function test_registration_validation_handles_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->post('/register', [
            'name'                  => 'Donatur Duplikat',
            'email'                 => 'duplicate@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * 3. Test WhatsApp Button pada Admin Kelola Donasi (Normalisasi & Link WA)
     */
    public function test_admin_donasi_has_working_whatsapp_button_with_normalized_indonesian_phone(): void
    {
        $admin = $this->createAdminUser();
        $kategori = Kategori::first();
        $channel = PaymentChannel::first();

        $penggalang = $this->createPenggalangDana($admin, [
            'nama_penggalang'  => 'Yayasan Sidoarjo',
            'email'            => 'sidoarjo@example.com',
            'no_telepon'       => '081234567890',
        ]);

        $campaign = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id'        => $kategori->id,
            'thumbnail'          => 'campaign.jpg',
            'judul'              => 'Sedekah Al-Quran Sidoarjo',
            'slug'               => 'sedekah-al-quran-sidoarjo',
            'deskripsi'          => 'Program bantuan Al-Quran',
            'tanggal_mulai'      => now()->subDays(5),
            'tanggal_berakhir'   => now()->addDays(20),
            'target_donasi'      => 50000000,
            'is_active'          => true,
            'status'             => 'approved',
            'minimal_donasi'     => 10000,
        ]);

        // Donasi dengan nomor lokal "081234567890"
        $donasi = Donasi::create([
            'campaign_id'  => $campaign->id,
            'nama_donatur' => 'Pak Haji Syukur',
            'email'        => 'syukur@example.com',
            'no_hp'        => '081234567890',
            'nominal'      => 100000,
            'pesan_doa'    => 'Semoga berkah',
            'is_anonim'    => false,
        ]);

        Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-WA-001',
            'transaction_status' => 'settlement',
            'paid_at'            => now(),
        ]);

        // Test Model attributes
        $this->assertEquals('6281234567890', $donasi->normalized_whatsapp_number);
        $this->assertNotNull($donasi->whatsapp_url);
        $this->assertStringContainsString('https://wa.me/6281234567890', $donasi->whatsapp_url);
        $this->assertStringContainsString('Pak%20Haji%20Syukur', $donasi->whatsapp_url);

        // Test Index View
        $responseIndex = $this->actingAs($admin)->get(route('admin.donasi.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('https://wa.me/6281234567890', false);

        // Test Show View
        $responseShow = $this->actingAs($admin)->get(route('admin.donasi.show', $donasi->id));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('https://wa.me/6281234567890', false);
        $responseShow->assertSee('Hubungi via WhatsApp');
    }

    /**
     * 4. Test WhatsApp Button saat nomor telepon tidak tersedia
     */
    public function test_admin_donasi_handles_missing_phone_gracefully(): void
    {
        $admin = $this->createAdminUser();
        $kategori = Kategori::first();

        $penggalang = $this->createPenggalangDana($admin, [
            'nama_penggalang'  => 'Yayasan Sidoarjo 2',
            'email'            => 'sidoarjo2@example.com',
            'no_telepon'       => '081234567891',
        ]);

        $campaign = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id'        => $kategori->id,
            'thumbnail'          => 'campaign.jpg',
            'judul'              => 'Sedekah Santri',
            'slug'               => 'sedekah-santri',
            'deskripsi'          => 'Program santri',
            'tanggal_mulai'      => now(),
            'tanggal_berakhir'   => now()->addDays(10),
            'target_donasi'      => 10000000,
            'is_active'          => true,
            'status'             => 'approved',
            'minimal_donasi'     => 10000,
        ]);

        $donasiTanpaHp = Donasi::create([
            'campaign_id'  => $campaign->id,
            'nama_donatur' => 'Donatur Misterius',
            'email'        => 'misterius@example.com',
            'no_hp'        => null,
            'nominal'      => 50000,
            'is_anonim'    => true,
        ]);

        $this->assertNull($donasiTanpaHp->normalized_whatsapp_number);
        $this->assertNull($donasiTanpaHp->whatsapp_url);

        $responseShow = $this->actingAs($admin)->get(route('admin.donasi.show', $donasiTanpaHp->id));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Tidak ada nomor HP terdaftar');
    }

    public function test_search_hides_expired_campaigns_and_keeps_active_campaigns(): void
    {
        $owner = $this->createRegularUser();
        $penggalang = $this->createPenggalangDana($owner);
        $kategori = Kategori::firstOrFail();

        foreach ([
            ['QA Campaign Search Marker Active', now()->addDays(5)],
            ['QA Campaign Search Marker Expired', now()->subDay()],
        ] as [$title, $endDate]) {
            Campaign::create([
                'penggalang_dana_id' => $penggalang->id,
                'kategori_id' => $kategori->id,
                'thumbnail' => 'campaign.jpg',
                'judul' => $title,
                'slug' => \Illuminate\Support\Str::slug($title),
                'deskripsi' => 'Search QA fixture',
                'tanggal_mulai' => now()->subDays(2),
                'tanggal_berakhir' => $endDate,
                'target_donasi' => 1000000,
                'is_active' => true,
                'status' => 'approved',
                'approval_status' => 'approved',
                'campaign_type' => 'regular',
                'minimal_donasi' => 10000,
            ]);
        }

        $this->get(route('search', ['q' => 'QA Campaign Search Marker', 'type' => 'campaign']))
            ->assertOk()
            ->assertSee('QA Campaign Search Marker Active')
            ->assertDontSee('QA Campaign Search Marker Expired');
    }

    /**
     * 5. Test Kwitansi Settlement-Only (Pending/Failed Ditolak 404)
     */
    public function test_kwitansi_cannot_be_accessed_for_non_settlement_donations(): void
    {
        $user = $this->createRegularUser();
        $kategori = Kategori::first();
        $channel = PaymentChannel::first();

        $penggalang = $this->createPenggalangDana($user, [
            'jenis_penggalang' => 'individu',
            'nama_penggalang'  => 'Ustadz Fulan',
            'email'            => 'fulan@example.com',
            'no_telepon'       => '081234567899',
        ]);

        $campaign = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id'        => $kategori->id,
            'thumbnail'          => 'c.jpg',
            'judul'              => 'Santunan Yatim',
            'slug'               => 'santunan-yatim',
            'deskripsi'          => 'Santunan anak yatim',
            'tanggal_mulai'      => now(),
            'tanggal_berakhir'   => now()->addDays(15),
            'target_donasi'      => 5000000,
            'is_active'          => true,
            'status'             => 'approved',
            'minimal_donasi'     => 10000,
        ]);

        $donasiPending = Donasi::create([
            'campaign_id'  => $campaign->id,
            'user_id'      => $user->id,
            'nama_donatur' => $user->name,
            'email'        => $user->email,
            'nominal'      => 50000,
            'is_anonim'    => false,
        ]);

        Pembayaran::create([
            'donasi_id'          => $donasiPending->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-PENDING-1',
            'transaction_status' => 'pending', // NOT settlement
        ]);

        $response = $this->actingAs($user)->get(route('riwayat-donasi.kwitansi', $donasiPending->id));
        $response->assertStatus(404);
    }

    /**
     * 6. Test Otorisasi Kwitansi (User lain dilarang 403)
     */
    public function test_kwitansi_cannot_be_accessed_by_unauthorized_users(): void
    {
        $owner = $this->createRegularUser(['email' => 'owner@example.com']);
        $stranger = $this->createRegularUser(['email' => 'stranger@example.com']);
        $kategori = Kategori::first();
        $channel = PaymentChannel::first();

        $penggalang = $this->createPenggalangDana($owner, [
            'jenis_penggalang' => 'individu',
            'nama_penggalang'  => 'Relawan Baik',
            'email'            => 'relawan@example.com',
            'no_telepon'       => '081234567888',
        ]);

        $campaign = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id'        => $kategori->id,
            'thumbnail'          => 'c.jpg',
            'judul'              => 'Bantu Masjid',
            'slug'               => 'bantu-masjid',
            'deskripsi'          => 'Renovasi masjid',
            'tanggal_mulai'      => now(),
            'tanggal_berakhir'   => now()->addDays(15),
            'target_donasi'      => 5000000,
            'is_active'          => true,
            'status'             => 'approved',
            'minimal_donasi'     => 10000,
        ]);

        $donasi = Donasi::create([
            'campaign_id'  => $campaign->id,
            'user_id'      => $owner->id,
            'nama_donatur' => $owner->name,
            'email'        => $owner->email,
            'nominal'      => 100000,
            'is_anonim'    => false,
        ]);

        Pembayaran::create([
            'donasi_id'          => $donasi->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-SETTLE-1',
            'transaction_status' => 'settlement',
            'paid_at'            => now(),
        ]);

        // Stranger mencoba akses -> 403
        $responseStranger = $this->actingAs($stranger)->get(route('riwayat-donasi.kwitansi', $donasi->id));
        $responseStranger->assertStatus(403);

        // Owner akses -> 200
        $responseOwner = $this->actingAs($owner)->get(route('riwayat-donasi.kwitansi', $donasi->id));
        $responseOwner->assertStatus(200);
        $responseOwner->assertSee('OB-SETTLE-1');

        // Admin akses -> 200
        $admin = $this->createAdminUser();
        $responseAdmin = $this->actingAs($admin)->get(route('riwayat-donasi.kwitansi', $donasi->id));
        $responseAdmin->assertStatus(200);
    }

    /**
     * 7. Test Perlindungan Privasi Donatur Anonim
     */
    public function test_anonymous_donor_privacy_is_strictly_protected(): void
    {
        $kategori = Kategori::first();
        $channel = PaymentChannel::first();
        $user = $this->createRegularUser(['name' => 'Nama Rahasia Donatur', 'email' => 'rahasia@example.com']);

        $penggalang = $this->createPenggalangDana($user, [
            'nama_penggalang'  => 'Yayasan Amanah',
            'email'            => 'amanah@example.com',
            'no_telepon'       => '081234567877',
        ]);

        $campaign = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id'        => $kategori->id,
            'thumbnail'          => 'c.jpg',
            'judul'              => 'Program Pangan Yatim',
            'slug'               => 'program-pangan-yatim',
            'deskripsi'          => 'Bantuan pangan',
            'tanggal_mulai'      => now()->subDays(2),
            'tanggal_berakhir'   => now()->addDays(20),
            'target_donasi'      => 10000000,
            'is_active'          => true,
            'status'             => 'approved',
            'minimal_donasi'     => 10000,
        ]);

        $donasiAnonim = Donasi::create([
            'campaign_id'  => $campaign->id,
            'user_id'      => $user->id,
            'nama_donatur' => 'Nama Rahasia Donatur',
            'email'        => 'rahasia@example.com',
            'nominal'      => 75000,
            'pesan_doa'    => 'Doa tulus dari hamba Allah',
            'is_anonim'    => true,
        ]);

        Pembayaran::create([
            'donasi_id'          => $donasiAnonim->id,
            'payment_channel_id' => $channel->id,
            'order_id'           => 'OB-ANON-1',
            'transaction_status' => 'settlement',
            'paid_at'            => now(),
        ]);

        // Cek halaman publik campaign
        $responsePublic = $this->get(route('campaign.show', $campaign->slug));
        $responsePublic->assertStatus(200);
        $responsePublic->assertSee('Hamba Allah');
        $responsePublic->assertDontSee('Nama Rahasia Donatur');
        $responsePublic->assertDontSee('rahasia@example.com');

        // Cek halaman kwitansi milik owner
        $responseKwitansi = $this->actingAs($user)->get(route('riwayat-donasi.kwitansi', $donasiAnonim->id));
        $responseKwitansi->assertStatus(200);
        $responseKwitansi->assertSee('Hamba Allah (Anonim)');
        $responseKwitansi->assertDontSee('Nama Rahasia Donatur');
    }

    /**
     * 8. Test Campaign Expired / Belum Approved Tidak Bisa Didonasi
     */
    public function test_campaign_unapproved_or_ended_cannot_receive_donations(): void
    {
        $admin = $this->createAdminUser();
        $kategori = Kategori::first();

        $penggalang = $this->createPenggalangDana($admin, [
            'nama_penggalang'  => 'Yayasan Tangguh',
            'email'            => 'tangguh@example.com',
            'no_telepon'       => '081234567866',
        ]);

        // Campaign sudah berakhir 5 hari lalu
        $campaignExpired = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id'        => $kategori->id,
            'thumbnail'          => 'c.jpg',
            'judul'              => 'Bantuan Banjir Masa Lalu',
            'slug'               => 'bantuan-banjir-masa-lalu',
            'deskripsi'          => 'Program darurat lampau',
            'tanggal_mulai'      => now()->subDays(20),
            'tanggal_berakhir'   => now()->subDays(5),
            'target_donasi'      => 10000000,
            'is_active'          => true,
            'status'             => 'approved',
            'minimal_donasi'     => 10000,
        ]);

        $this->assertFalse($campaignExpired->isDonatable());

        // GET halaman donasi harus 404
        $responseGet = $this->get(route('donasi.create', $campaignExpired->slug));
        $responseGet->assertStatus(404);

        // POST donasi harus ditolak 422
        $responsePost = $this->postJson(route('donasi.store', $campaignExpired->slug), [
            'nominal'      => 50000,
            'sapaan'       => 'Kak',
            'nama_donatur' => 'Budi Baik',
            'email'        => 'budi@example.com',
            'no_hp'        => '081234567890',
            'payment_channel_id' => 1,
        ]);

        $responsePost->assertStatus(422);
        $responsePost->assertJsonFragment(['success' => false]);
    }

    /**
     * 9. Test Empat Kategori Penggalang Dana Konsisten
     */
    public function test_four_fundraiser_categories_consistency(): void
    {
        $categories = Penggalang_Dana::getCategoryLabels();
        $this->assertCount(4, $categories);
        $this->assertArrayHasKey('organisasi', $categories);
        $this->assertArrayHasKey('yayasan_lembaga', $categories);
        $this->assertArrayHasKey('komunitas', $categories);
        $this->assertArrayHasKey('individu', $categories);

        $this->assertEquals('Yayasan/Lembaga', $categories['yayasan_lembaga']);
        $this->assertEquals('Komunitas', $categories['komunitas']);
        $this->assertEquals('Organisasi', $categories['organisasi']);
        $this->assertEquals('Individu', $categories['individu']);

        $user = $this->createRegularUser();
        $penggalang = $this->createPenggalangDana($user, [
            'jenis_penggalang' => Penggalang_Dana::CATEGORY_YAYASAN,
            'nama_penggalang'  => 'Yayasan Bina Quran',
            'email'            => 'binaquran@example.com',
            'no_telepon'       => '081234567855',
        ]);

        $this->assertEquals('Yayasan/Lembaga', $penggalang->jenis_penggalang_label);
        $this->assertTrue($penggalang->isYayasan());
        $this->assertTrue($penggalang->isEntity());
        $this->assertFalse($penggalang->isIndividu());
    }

    /**
     * 10. Test Profil Penggalang Belum Approved Terisolasi dari Publik
     */
    public function test_unapproved_fundraiser_profile_is_inaccessible_to_public(): void
    {
        $user = $this->createRegularUser();
        $stranger = $this->createRegularUser(['email' => 'stranger2@example.com']);

        $penggalangPending = $this->createPenggalangDana($user, [
            'jenis_penggalang' => 'individu',
            'nama_penggalang'  => 'Calon Penggalang',
            'email'            => 'calon@example.com',
            'no_telepon'       => '081999999999',
            'alamat'           => 'Alamat Rahasia 123',
            'status'           => 'pending', // NOT approved
        ]);

        // Public visitor -> 404
        $responseGuest = $this->get(route('profil.penggalang', $penggalangPending->id));
        $responseGuest->assertStatus(404);

        // Stranger user -> 404
        $responseStranger = $this->actingAs($stranger)->get(route('profil.penggalang', $penggalangPending->id));
        $responseStranger->assertStatus(404);

        // Owner -> 200
        $responseOwner = $this->actingAs($user)->get(route('profil.penggalang', $penggalangPending->id));
        $responseOwner->assertStatus(200);

        // Admin -> 200
        $admin = $this->createAdminUser();
        $responseAdmin = $this->actingAs($admin)->get(route('profil.penggalang', $penggalangPending->id));
        $responseAdmin->assertStatus(200);
    }
}
