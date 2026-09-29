<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Campaign_Update;
use App\Models\Kategori;
use App\Models\Filter;
use App\Models\Penggalang_Dana;
use App\Models\Berita;
use App\Models\Testimoni;
use App\Models\Faq;
use App\Models\SyaratKetentuan;
use App\Models\PaymentGateway;
use App\Models\PaymentChannel;
use App\Models\Donasi;
use App\Models\Pembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class FullFormAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Kategori $kategori;
    protected Filter $filter;
    protected PaymentGateway $gateway;
    protected PaymentChannel $channel;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->user = User::factory()->create([
            'role' => 'user',
            'email' => 'donor@orangbaik.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@orangbaik.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->kategori = Kategori::create([
            'nama_kategori' => 'Kemanusiaan',
            'slug'          => 'kemanusiaan',
        ]);

        $this->filter = Filter::create([
            'nama_filter' => 'Tanggap Bencana',
            'slug'        => 'tanggap-bencana',
        ]);

        $this->gateway = PaymentGateway::create([
            'name' => 'Manual Transfer',
            'code' => 'manual',
            'driver' => 'manual',
            'is_active' => true,
        ]);

        $this->channel = PaymentChannel::create([
            'payment_gateway_id' => $this->gateway->id,
            'name' => 'BCA Manual',
            'channel_code' => 'bca_manual',
            'payment_type' => 'transfer',
            'account_number' => '1234567890',
            'account_name' => 'Yayasan OrangBaik',
            'is_active' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. AUTH FORMS AUDIT
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function register_form_creates_new_user_and_redirects()
    {
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@orangbaik.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@orangbaik.id',
            'name' => 'New User',
        ]);
    }

    /** @test */
    public function register_form_validates_duplicate_email_and_password_mismatch()
    {
        $response = $this->post(route('register'), [
            'name' => 'Duplicate User',
            'email' => 'donor@orangbaik.id',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /** @test */
    public function forgot_password_form_sends_reset_link()
    {
        $response = $this->post(route('password.email'), [
            'email' => 'donor@orangbaik.id',
        ]);

        $response->assertSessionHas('status');
    }

    /** @test */
    public function reset_password_form_updates_user_password()
    {
        $token = Password::createToken($this->user);

        $response = $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'donor@orangbaik.id',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->user->password));
    }

    /*
    |--------------------------------------------------------------------------
    | 2. PENGGALANG DANA FORMS AUDIT & UPLOAD
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function organisasi_form_submits_successfully_with_files_and_documents()
    {
        $thumbnail = UploadedFile::fake()->image('banner_org.jpg', 800, 400);
        $fotoProfil = UploadedFile::fake()->image('logo_org.png', 300, 300);

        $response = $this->actingAs($this->user)->post(route('penggalang_dana.organisasi.store'), [
            'thumbnail' => $thumbnail,
            'foto_profil' => $fotoProfil,
            'jenis_penggalang' => 'organisasi',
            'nama_penggalang' => 'Yayasan Peduli Sesama',
            'tahun_berdiri' => '2018',
            'alamat' => 'Jl. Kebaikan No. 100, Sidoarjo',
            'deskripsi' => 'Organisasi sosial kemanusiaan.',
            'visi' => 'Menjadi pelopor kebaikan.',
            'misi' => 'Membantu kaum dhuafa.',
            'email' => 'yayasan@orangbaik.id',
            'no_telepon' => '081234567890',
            'nama_dokumen' => ['Akta Notaris Kemenkumham', 'SK Domisili'],
            'file_dokumen' => [
                'https://drive.google.com/file/d/12345/view',
                'https://drive.google.com/file/d/67890/view',
            ],
        ]);

        $response->assertRedirect(route('profile.user'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('penggalang_dana', [
            'user_id' => $this->user->id,
            'nama_penggalang' => 'Yayasan Peduli Sesama',
            'jenis_penggalang' => 'organisasi',
            'tahun_berdiri' => '2018',
            'status' => 'pending',
        ]);

        $penggalang = Penggalang_Dana::where('user_id', $this->user->id)->first();
        $this->assertNotNull($penggalang);
        Storage::disk('public')->assertExists($penggalang->thumbnail);
        Storage::disk('public')->assertExists($penggalang->foto_profil);

        $this->assertDatabaseHas('penggalang_dana_dokumen', [
            'penggalang_dana_id' => $penggalang->id,
            'nama_dokumen' => 'Akta Notaris Kemenkumham',
        ]);
    }

    /** @test */
    public function penggalang_dana_update_form_saves_changes()
    {
        $penggalang = Penggalang_Dana::create([
            'user_id' => $this->user->id,
            'nama_penggalang' => 'Komunitas Awal',
            'jenis_penggalang' => 'individu',
            'foto_profil' => 'dummy.jpg',
            'alamat' => 'Alamat lama',
            'deskripsi' => 'Deskripsi lama',
            'visi' => 'Visi lama',
            'misi' => 'Misi lama',
            'email' => 'lama@orangbaik.id',
            'no_telepon' => '08111111111',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user)->patch(route('penggalang_dana.update', $penggalang->id), [
            'nama_penggalang' => 'Komunitas Diperbarui',
            'email' => 'baru@orangbaik.id',
            'no_telepon' => '08222222222',
            'alamat' => 'Alamat Baru Sidoarjo',
            'deskripsi' => 'Deskripsi Baru',
            'visi' => 'Visi Baru',
            'misi' => 'Misi Baru',
        ]);

        $response->assertRedirect(route('profil.penggalang', $penggalang->id));
        $this->assertDatabaseHas('penggalang_dana', [
            'id' => $penggalang->id,
            'nama_penggalang' => 'Komunitas Diperbarui',
            'email' => 'baru@orangbaik.id',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. CAMPAIGN FORMS AUDIT
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function campaign_create_and_update_forms_work_properly()
    {
        $penggalang = Penggalang_Dana::create([
            'user_id' => $this->user->id,
            'nama_penggalang' => 'Penggalang Sah',
            'jenis_penggalang' => 'individu',
            'foto_profil' => 'profil.jpg',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Deskripsi',
            'visi' => 'Visi',
            'misi' => 'Misi',
            'email' => 'sah@orangbaik.id',
            'no_telepon' => '08123456789',
            'status' => 'approved',
        ]);

        $thumbnail = UploadedFile::fake()->image('campaign.jpg', 734, 394);

        $response = $this->actingAs($this->user)->post(route('campaign.store'), [
            'thumbnail' => $thumbnail,
            'judul_campaign' => 'Bantu Pembangunan Masjid',
            'custom_slug' => 'bantu-masjid-desa',
            'deskripsi_campaign' => '<p>Deskripsi lengkap pembangunan masjid.</p>',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_akhir' => now()->addDays(30)->format('Y-m-d'),
            'target_donasi' => '50.000.000',
            'minimal_donasi' => '10.000',
            'kategori_id' => $this->kategori->id,
            'campaign_type' => 'regular',
            'filter' => [$this->filter->id],
            'packages' => [
                [
                    'title' => 'Paket Semen',
                    'description' => '1 Sak Semen',
                    'nominal' => '75.000',
                ],
            ],
        ]);

        $response->assertRedirect(route('campaign.show', 'bantu-masjid-desa'));
        $this->assertDatabaseHas('campaign', [
            'judul' => 'Bantu Pembangunan Masjid',
            'custom_slug' => 'bantu-masjid-desa',
            'target_donasi' => 50000000,
        ]);

        $campaign = Campaign::where('custom_slug', 'bantu-masjid-desa')->first();
        $this->assertNotNull($campaign);

        // Edit campaign form — pass custom_slug so it is preserved and redirect is predictable
        $responseEdit = $this->actingAs($this->user)->put(route('campaign.update', $campaign->id), [
            'judul' => 'Bantu Pembangunan Masjid Megah',
            'deskripsi' => '<p>Deskripsi update</p>',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_berakhir' => now()->addDays(60)->format('Y-m-d'),
            'target_donasi' => '75.000.000',
            'minimal_donasi' => '10.000',
            'kategori_id' => $this->kategori->id,
            'campaign_type' => 'regular',
            'filter' => [$this->filter->id],
            'custom_slug' => 'bantu-masjid-desa', // preserve slug so redirect is deterministic
        ]);

        $responseEdit->assertRedirect(route('campaign.show', ['slug' => 'bantu-masjid-desa']));
        $this->assertDatabaseHas('campaign', [
            'id' => $campaign->id,
            'judul' => 'Bantu Pembangunan Masjid Megah',
            'target_donasi' => 75000000,
        ]);
    }

    /** @test */
    public function campaign_update_kabar_terbaru_can_be_created_and_edited()
    {
        $penggalang = Penggalang_Dana::create([
            'user_id' => $this->user->id,
            'nama_penggalang' => 'Penggalang Sah',
            'jenis_penggalang' => 'individu',
            'foto_profil' => 'profil.jpg',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Deskripsi',
            'visi' => 'Visi',
            'misi' => 'Misi',
            'email' => 'sah@orangbaik.id',
            'no_telepon' => '08123456789',
            'status' => 'approved',
        ]);

        $campaign = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id' => $this->kategori->id,
            'thumbnail' => 'campaign.jpg',
            'judul' => 'Campaign Bersama',
            'slug' => 'campaign-bersama',
            'deskripsi' => 'Deskripsi',
            'tanggal_mulai' => now()->subDay(),
            'tanggal_berakhir' => now()->addDays(10),
            'target_donasi' => 10000000,
            'minimal_donasi' => 10000,
            'campaign_type' => 'regular',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        // Create update
        $response = $this->actingAs($this->user)->post(route('campaign.update.store', $campaign->slug), [
            'judul_update' => 'Penyaluran Tahap 1',
            'isi_update' => '<p>Dana telah disalurkan dengan lancar.</p>',
        ]);

        $response->assertRedirect(route('campaign.show', $campaign->slug));
        $this->assertDatabaseHas('campaign_update', [
            'campaign_id' => $campaign->id,
            'judul_update' => 'Penyaluran Tahap 1',
        ]);

        $update = Campaign_Update::where('campaign_id', $campaign->id)->first();

        // Edit update
        $responseEdit = $this->actingAs($this->user)->put(route('campaign.update.update', [
            'slug' => $campaign->slug,
            'update' => $update->id,
        ]), [
            'judul_update' => 'Penyaluran Tahap 1 Selesai',
            'isi_update' => '<p>Dana tahap 1 tuntas 100%.</p>',
        ]);

        $responseEdit->assertRedirect(route('campaign.show', $campaign->slug));
        $this->assertDatabaseHas('campaign_update', [
            'id' => $update->id,
            'judul_update' => 'Penyaluran Tahap 1 Selesai',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. DONASI & PAYMENT FORMS AUDIT
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function donation_form_creates_pending_donation_and_payment_records()
    {
        $penggalang = Penggalang_Dana::create([
            'user_id' => $this->user->id,
            'nama_penggalang' => 'Penggalang Donasi',
            'jenis_penggalang' => 'individu',
            'foto_profil' => 'profil.jpg',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Deskripsi',
            'visi' => 'Visi',
            'misi' => 'Misi',
            'email' => 'penggalang@orangbaik.id',
            'no_telepon' => '08123456789',
            'status' => 'approved',
        ]);

        $campaign = Campaign::create([
            'penggalang_dana_id' => $penggalang->id,
            'kategori_id' => $this->kategori->id,
            'thumbnail' => 'campaign.jpg',
            'judul' => 'Campaign Donasi',
            'slug' => 'campaign-donasi',
            'deskripsi' => 'Deskripsi',
            'tanggal_mulai' => now()->subDay(),
            'tanggal_berakhir' => now()->addDays(10),
            'target_donasi' => 10000000,
            'minimal_donasi' => 10000,
            'campaign_type' => 'regular',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $response = $this->post(route('donasi.store', $campaign->slug), [
            'nominal' => 50000,
            'payment_channel_id' => $this->channel->id,
            'nama_donatur' => 'Hamba Allah',
            'no_hp' => '081299998888',
            'doa' => 'Semoga berkah',
            'anonim' => '0',
        ]);

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertTrue($json['success']);
        $this->assertNotNull($json['payment_token']);

        $this->assertDatabaseHas('donasi', [
            'campaign_id' => $campaign->id,
            'nominal' => 50000,
            'nama_donatur' => 'Hamba Allah',
        ]);
    }

    /** @test */
    public function zakat_calculator_form_calculates_estimasi()
    {
        $response = $this->post(route('kalkulator.hitung'), [
            'jenis' => 'penghasilan',
            'gaji' => '10.000.000',
            'bonus' => '2.000.000',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('hasil');
    }

    /** @test */
    public function news_comment_form_creates_comment()
    {
        $berita = Berita::create([
            'user_id' => $this->admin->id,
            'judul' => 'Berita Penyaluran',
            'slug' => 'berita-penyaluran',
            'thumbnail' => 'berita.jpg',
            'isi' => '<p>Konten berita.</p>',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->user)->post(route('berita.komentar.store', $berita->id), [
            'komentar' => 'Alhamdulillah sangat bermanfaat!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('komentar', [
            'berita_id' => $berita->id,
            'user_id' => $this->user->id,
            'komentar' => 'Alhamdulillah sangat bermanfaat!',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. ADMIN CRUD FORMS AUDIT
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function admin_can_crud_berita()
    {
        $thumbnail = UploadedFile::fake()->image('berita.jpg', 600, 400);

        // CREATE
        $response = $this->actingAs($this->admin)->post(route('admin.berita.store'), [
            'judul' => 'Berita Admin Baru',
            'custom_slug' => 'berita-admin-baru',
            'thumbnail' => $thumbnail,
            'isi' => '<p>Isi berita lengkap</p>',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.berita.index'));
        $this->assertDatabaseHas('berita', [
            'judul' => 'Berita Admin Baru',
            'slug' => 'berita-admin-baru',
        ]);

        $berita = Berita::where('slug', 'berita-admin-baru')->first();

        // UPDATE
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.berita.update', $berita->id), [
            'judul' => 'Berita Admin Diperbarui',
            'isi' => '<p>Isi berita update</p>',
            'status' => 'published',
        ]);

        $responseUpdate->assertRedirect(route('admin.berita.show', $berita->id));
        $this->assertDatabaseHas('berita', [
            'id' => $berita->id,
            'judul' => 'Berita Admin Diperbarui',
        ]);

        // DELETE
        $responseDelete = $this->actingAs($this->admin)->delete(route('admin.berita.destroy', $berita->id));
        $responseDelete->assertRedirect(route('admin.berita.index'));
        $this->assertDatabaseMissing('berita', ['id' => $berita->id]);
    }

    /** @test */
    public function admin_can_crud_testimoni()
    {
        $foto = UploadedFile::fake()->image('tokoh.jpg', 200, 200);

        // CREATE
        $response = $this->actingAs($this->admin)->post(route('admin.testimoni.store'), [
            'nama' => 'Ustadz Abdullah',
            'jabatan' => 'Tokoh Masyarakat',
            'isi_testimoni' => 'Platform yang sangat terpercaya dan amanah.',
            'foto_profil' => $foto,
        ]);

        $response->assertRedirect(route('admin.testimoni.index'));
        $this->assertDatabaseHas('testimoni', [
            'nama' => 'Ustadz Abdullah',
        ]);

        $testimoni = Testimoni::where('nama', 'Ustadz Abdullah')->first();

        // UPDATE
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.testimoni.update', $testimoni->id), [
            'nama' => 'Ustadz Abdullah Lc.',
            'jabatan' => 'Tokoh Masyarakat Sidoarjo',
            'isi_testimoni' => 'Platform terbaik untuk sedekah.',
        ]);

        $responseUpdate->assertRedirect(route('admin.testimoni.index'));
        $this->assertDatabaseHas('testimoni', [
            'id' => $testimoni->id,
            'nama' => 'Ustadz Abdullah Lc.',
        ]);

        // DELETE
        $responseDelete = $this->actingAs($this->admin)->delete(route('admin.testimoni.destroy', $testimoni->id));
        $responseDelete->assertRedirect(route('admin.testimoni.index'));
        $this->assertDatabaseMissing('testimoni', ['id' => $testimoni->id]);
    }

    /** @test */
    public function admin_can_crud_faq()
    {
        // CREATE
        $response = $this->actingAs($this->admin)->post(route('admin.faq.store'), [
            'pertanyaan' => 'Bagaimana cara berdonasi?',
            'jawaban' => 'Pilih campaign lalu klik tombol Donasi Sekarang.',
            'urutan' => 1,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.faq.index'));
        $this->assertDatabaseHas('faq', [
            'pertanyaan' => 'Bagaimana cara berdonasi?',
        ]);

        $faq = Faq::where('pertanyaan', 'Bagaimana cara berdonasi?')->first();

        // UPDATE
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.faq.update', $faq->id), [
            'pertanyaan' => 'Bagaimana cara berdonasi di OrangBaik?',
            'jawaban' => 'Pilih campaign, tentukan nominal, dan pilih metode pembayaran.',
            'urutan' => 1,
            'is_active' => '1',
        ]);

        $responseUpdate->assertRedirect(route('admin.faq.index'));
        $this->assertDatabaseHas('faq', [
            'id' => $faq->id,
            'pertanyaan' => 'Bagaimana cara berdonasi di OrangBaik?',
        ]);

        // DELETE
        $responseDelete = $this->actingAs($this->admin)->delete(route('admin.faq.destroy', $faq->id));
        $responseDelete->assertRedirect(route('admin.faq.index'));
        $this->assertDatabaseMissing('faq', ['id' => $faq->id]);
    }

    /** @test */
    public function admin_can_crud_syarat_ketentuan()
    {
        // CREATE
        $response = $this->actingAs($this->admin)->post(route('admin.syarat-ketentuan.store'), [
            'judul' => 'Ketentuan Donatur',
            'isi' => 'Donatur wajib menggunakan dana yang sah dan halal.',
            'urutan' => 1,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.syarat-ketentuan.index'));
        $this->assertDatabaseHas('syarat_ketentuan', [
            'judul' => 'Ketentuan Donatur',
        ]);

        $sk = SyaratKetentuan::where('judul', 'Ketentuan Donatur')->first();

        // UPDATE
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.syarat-ketentuan.update', $sk->id), [
            'judul' => 'Ketentuan Donatur & Penggalang',
            'isi' => 'Semua pihak wajib mematuhi etika bersedekah.',
            'urutan' => 1,
            'is_active' => '1',
        ]);

        $responseUpdate->assertRedirect(route('admin.syarat-ketentuan.index'));
        $this->assertDatabaseHas('syarat_ketentuan', [
            'id' => $sk->id,
            'judul' => 'Ketentuan Donatur & Penggalang',
        ]);

        // DELETE
        $responseDelete = $this->actingAs($this->admin)->delete(route('admin.syarat-ketentuan.destroy', $sk->id));
        $responseDelete->assertRedirect(route('admin.syarat-ketentuan.index'));
        $this->assertDatabaseMissing('syarat_ketentuan', ['id' => $sk->id]);
    }

    /** @test */
    public function admin_can_approve_and_reject_penggalang_dana()
    {
        $penggalang = Penggalang_Dana::create([
            'user_id' => $this->user->id,
            'nama_penggalang' => 'Calon Penggalang',
            'jenis_penggalang' => 'individu',
            'foto_profil' => 'profil.jpg',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Deskripsi',
            'visi' => 'Visi',
            'misi' => 'Misi',
            'email' => 'calon@orangbaik.id',
            'no_telepon' => '08123456789',
            'status' => 'pending',
        ]);

        // APPROVE
        $responseApprove = $this->actingAs($this->admin)->patch(route('admin.penggalang_dana.approve', $penggalang->id));
        $responseApprove->assertRedirect();
        $this->assertDatabaseHas('penggalang_dana', [
            'id' => $penggalang->id,
            'status' => 'approved',
        ]);

        // REJECT
        $responseReject = $this->actingAs($this->admin)->patch(route('admin.penggalang_dana.reject', $penggalang->id), [
            'catatan_verifikasi' => 'Dokumen belum lengkap.',
        ]);
        $responseReject->assertRedirect();
        $this->assertDatabaseHas('penggalang_dana', [
            'id' => $penggalang->id,
            'status' => 'rejected',
        ]);
    }
}
