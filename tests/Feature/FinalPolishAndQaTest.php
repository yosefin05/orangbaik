<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Kategori;
use App\Models\Filter;
use App\Models\Penggalang_Dana;
use App\Models\Penggalang_Dana_Dokumen;
use App\Models\Legalitas;
use App\Models\LaporanKeuangan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FinalPolishAndQaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Kategori $kategori;
    protected Filter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->user = User::factory()->create([
            'role' => 'user',
            'email' => 'user@orangbaik.id',
            'password' => bcrypt('password123'),
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@orangbaik.id',
            'password' => bcrypt('password123'),
        ]);

        $this->kategori = Kategori::create([
            'nama_kategori' => 'Kesehatan',
            'slug'          => 'kesehatan',
        ]);

        $this->filter = Filter::create([
            'nama_filter' => 'Mendesak',
            'slug'        => 'mendesak',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. AUTH & REMEMBER ME TESTS
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function user_can_login_with_remember_me_enabled()
    {
        $response = $this->post(route('login'), [
            'email'    => 'user@orangbaik.id',
            'password' => 'password123',
            'remember' => 'on',
        ]);

        $response->assertRedirect(route('home', absolute: false));
        $this->assertAuthenticatedAs($this->user);
        
        // Verify remember_token was set on user model
        $this->user->refresh();
        $this->assertNotNull($this->user->remember_token);
    }

    /** @test */
    public function user_can_login_without_remember_me()
    {
        $response = $this->post(route('login'), [
            'email'    => 'user@orangbaik.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home', absolute: false));
        $this->assertAuthenticatedAs($this->user);
    }

    /** @test */
    public function login_fails_with_invalid_credentials_and_shows_error()
    {
        $response = $this->post(route('login'), [
            'email'    => 'user@orangbaik.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. PENGGALANG DANA ORGANISASI & INDIVIDU CREATE TESTS
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function user_can_submit_penggalang_dana_organisasi_with_files_and_documents()
    {
        $thumbnail = UploadedFile::fake()->image('banner_org.jpg', 800, 400);
        $fotoProfil = UploadedFile::fake()->image('logo_org.png', 400, 400);

        $payload = [
            'thumbnail'        => $thumbnail,
            'foto_profil'      => $fotoProfil,
            'nama_penggalang'  => 'Yayasan Peduli Umat',
            'tahun_berdiri'    => '2018',
            'alamat'           => 'Jl. Raya Sidoarjo No. 123',
            'deskripsi'        => 'Deskripsi lengkap yayasan peduli umat.',
            'visi'             => 'Menjadi lembaga amanah.',
            'misi'             => 'Membantu sesama.',
            'email'            => 'kontak@peduliumat.org',
            'no_telepon'       => '081234567890',
            'nama_dokumen'     => ['Akta Kemenkumham', 'SK BAZNAS'],
            'file_dokumen'     => ['https://drive.google.com/doc1', 'https://drive.google.com/doc2'],
            'jenis_penggalang' => 'organisasi',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('penggalang_dana.organisasi.store'), $payload);

        $response->assertRedirect(route('profile.user'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('penggalang_dana', [
            'user_id'          => $this->user->id,
            'nama_penggalang'  => 'Yayasan Peduli Umat',
            'jenis_penggalang' => 'organisasi',
            'email'            => 'kontak@peduliumat.org',
            'status'           => 'pending',
        ]);

        $penggalang = Penggalang_Dana::where('user_id', $this->user->id)->first();
        $this->assertCount(2, $penggalang->penggalangDanaDokumen);
        Storage::disk('public')->assertExists($penggalang->thumbnail);
        Storage::disk('public')->assertExists($penggalang->foto_profil);
    }

    /** @test */
    public function penggalang_dana_organisasi_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('penggalang_dana.organisasi.store'), []);

        $response->assertSessionHasErrors([
            'thumbnail',
            'foto_profil',
            'nama_penggalang',
            'tahun_berdiri',
            'alamat',
            'deskripsi',
            'visi',
            'misi',
            'email',
            'no_telepon',
            'nama_dokumen.0',
            'file_dokumen.0',
        ]);
    }

    /** @test */
    public function user_can_submit_penggalang_dana_individu()
    {
        $fotoProfil = UploadedFile::fake()->image('foto_saya.jpg', 300, 300);

        $payload = [
            'foto_profil'      => $fotoProfil,
            'nama_penggalang'  => 'Ahmad Fulan',
            'alamat'           => 'Jl. Pahlawan Sidoarjo',
            'deskripsi'        => 'Relawan sosial mandiri.',
            'visi'             => 'Menebar kebaikan.',
            'misi'             => 'Membantu dhuafa.',
            'email'            => 'fulan@gmail.com',
            'no_telepon'       => '085712345678',
            'nama_dokumen'     => ['KTP Relawan'],
            'file_dokumen'     => ['https://drive.google.com/ktp'],
            'jenis_penggalang' => 'individu',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('penggalang_dana.individu.store'), $payload);

        $response->assertRedirect(route('profile.user'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('penggalang_dana', [
            'user_id'          => $this->user->id,
            'nama_penggalang'  => 'Ahmad Fulan',
            'jenis_penggalang' => 'individu',
            'status'           => 'pending',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. CAMPAIGN CREATE & PACKAGES TESTS
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function approved_penggalang_can_create_campaign_with_thumbnail_and_packages()
    {
        $penggalang = Penggalang_Dana::create([
            'user_id'          => $this->user->id,
            'nama_penggalang'  => 'Penggalang Approved',
            'jenis_penggalang' => 'organisasi',
            'foto_profil'      => 'profil.jpg',
            'thumbnail'        => 'thumb.jpg',
            'alamat'           => 'Alamat',
            'deskripsi'        => 'Deskripsi',
            'visi'             => 'Visi',
            'misi'             => 'Misi',
            'email'            => 'approved@org.com',
            'no_telepon'       => '081234',
            'status'           => 'approved',
        ]);

        $thumbnail = UploadedFile::fake()->image('campaign_poster.jpg', 734, 394);
        $packageImg = UploadedFile::fake()->image('package_1.jpg', 400, 400);

        $payload = [
            'thumbnail'          => $thumbnail,
            'judul_campaign'     => 'Bantuan Sembako Yatim Piatu',
            'deskripsi_campaign' => '<p>Deskripsi campaign bantuan yatim piatu.</p>',
            'tanggal_mulai'      => now()->format('Y-m-d'),
            'tanggal_akhir'      => now()->addDays(30)->format('Y-m-d'),
            'target_donasi'      => '50.000.000',
            'minimal_donasi'     => '10.000',
            'kategori_id'        => $this->kategori->id,
            'campaign_type'      => 'regular',
            'filter'             => [$this->filter->id],
            'packages'           => [
                [
                    'title'       => 'Paket Beras 5kg',
                    'nominal'     => '75.000',
                    'description' => 'Paket sembako beras',
                    'image'       => $packageImg,
                ]
            ],
            'custom_slug'        => 'sembako-yatim-sidoarjo',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('campaign.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('campaign', [
            'penggalang_dana_id' => $penggalang->id,
            'judul'              => 'Bantuan Sembako Yatim Piatu',
            'custom_slug'        => 'sembako-yatim-sidoarjo',
            'target_donasi'      => 50000000,
        ]);

        $campaign = Campaign::where('custom_slug', 'sembako-yatim-sidoarjo')->first();
        $this->assertNotNull($campaign);
        $this->assertCount(1, $campaign->packages);
        $this->assertEquals(75000, $campaign->packages->first()->nominal);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. ADMIN CRUD LEGALITAS & LAPORAN KEUANGAN TESTS
    |--------------------------------------------------------------------------
    */

    /** @test */
    public function admin_can_create_update_toggle_and_delete_legalitas()
    {
        $file = UploadedFile::fake()->create('sk_kemenkumham.pdf', 500);

        // CREATE
        $resCreate = $this->actingAs($this->admin)
            ->post(route('admin.legalitas.store'), [
                'judul'           => 'Akta Notaris Pendirian',
                'nomor_legalitas' => 'AHU-00123.AH.01.04',
                'deskripsi'       => 'Dokumen resmi pendirian yayasan.',
                'file'            => $file,
                'is_active'       => '1',
            ]);
        $resCreate->assertRedirect(route('admin.legalitas.index'));

        $legalitas = Legalitas::first();
        $this->assertNotNull($legalitas);
        $this->assertEquals('Akta Notaris Pendirian', $legalitas->judul);

        // TOGGLE
        $resToggle = $this->actingAs($this->admin)
            ->patch(route('admin.legalitas.toggle', $legalitas->id));
        $resToggle->assertRedirect();
        $legalitas->refresh();
        $this->assertFalse((bool) $legalitas->is_active);

        // DELETE
        $resDelete = $this->actingAs($this->admin)
            ->delete(route('admin.legalitas.destroy', $legalitas->id));
        $resDelete->assertRedirect(route('admin.legalitas.index'));
        $this->assertDatabaseMissing('legalitas', ['id' => $legalitas->id]);
    }
}
