<?php

namespace Tests\Feature;

use App\Models\Penggalang_Dana;
use App\Models\Penggalang_Dana_Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FundraiserDocumentPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_individual_fundraiser_submission_rejects_external_document_links(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('penggalang_dana.individu.store'), [
            'nama_penggalang' => 'Penggalang Uji',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Pengajuan penggalang dana.',
            'email' => 'penggalang@example.test',
            'no_telepon' => '081234567890',
            'nama_dokumen' => ['KTP'],
            'file_dokumen' => ['https://example.test/ktp.pdf'],
        ]);

        $response->assertSessionHasErrors('dokumen_files.0');
        $this->assertDatabaseMissing('penggalang_dana', ['user_id' => $user->id]);
        $this->assertDatabaseCount('penggalang_dana_dokumen', 0);
    }

    public function test_fundraiser_owner_profile_does_not_render_verification_document_metadata(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();

        $fundraiser = Penggalang_Dana::create([
            'user_id' => $user->id,
            'jenis_penggalang' => Penggalang_Dana::CATEGORY_INDIVIDU,
            'foto_profil' => 'profile.jpg',
            'nama_penggalang' => 'Profil Privat Uji',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Profil untuk pengujian privasi.',
            'email' => 'profil-privat-' . $user->id . '@example.test',
            'no_telepon' => '08123456789',
            'status' => 'approved',
        ]);

        $documentPath = 'dokumen_penggalang/' . $fundraiser->id . '/ktp-rahasia.pdf';
        Storage::disk('private')->put($documentPath, 'private test content');

        Penggalang_Dana_Dokumen::create([
            'penggalang_dana_id' => $fundraiser->id,
            'nama_dokumen' => 'KTP RAHASIA JANGAN TAMPIL',
            'file_dokumen' => $documentPath,
            'is_public' => false,
            'storage_disk' => 'private',
        ]);

        $response = $this->actingAs($user)->get(route('profil.penggalang', $fundraiser->slug));

        $response->assertOk();
        $response->assertDontSee('KTP RAHASIA JANGAN TAMPIL');
        $response->assertDontSee(route('dokumen.show', 1));
    }

    public function test_individual_verification_document_is_stored_privately(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('penggalang_dana.individu.store'), [
            'nama_penggalang' => 'Penggalang Uji',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Pengajuan penggalang dana.',
            'email' => 'penggalang@example.test',
            'no_telepon' => '081234567890',
            'nama_dokumen' => ['KTP'],
            'dokumen_files' => [
                UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('penggalang_dana', [
            'user_id' => $user->id,
            'jenis_penggalang' => Penggalang_Dana::CATEGORY_INDIVIDU,
            'status' => 'pending',
        ]);

        $document = Penggalang_Dana_Dokumen::query()->firstOrFail();
        $this->assertFalse($document->is_public);
        $this->assertSame('private', $document->storage_disk);
        Storage::disk('private')->assertExists($document->file_dokumen);
    }
    public function test_legacy_public_flag_never_grants_document_access_to_guest_owner_or_other_user(): void
    {
        Storage::fake('public');
        Storage::fake('private');

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $fundraiser = Penggalang_Dana::create([
            'user_id' => $owner->id,
            'jenis_penggalang' => Penggalang_Dana::CATEGORY_INDIVIDU,
            'foto_profil' => 'profile.jpg',
            'nama_penggalang' => 'Legacy Public Flag Test',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Legacy document authorization test.',
            'email' => 'legacy-public-' . $owner->id . '@example.test',
            'no_telepon' => '08123456789',
            'status' => 'approved',
        ]);

        $legacyPath = 'dokumen_penggalang/' . $fundraiser->id . '/legacy-ktp.pdf';
        Storage::disk('public')->put($legacyPath, 'legacy private document fixture');

        $document = Penggalang_Dana_Dokumen::create([
            'penggalang_dana_id' => $fundraiser->id,
            'nama_dokumen' => 'Legacy KTP',
            'file_dokumen' => $legacyPath,
            'is_public' => true,
            'storage_disk' => 'public',
        ]);

        $this->get(route('dokumen.show', $document->id))->assertForbidden();
        $this->actingAs($owner)->get(route('dokumen.show', $document->id))->assertForbidden();
        $this->actingAs($otherUser)->get(route('dokumen.show', $document->id))->assertForbidden();

        $adminResponse = $this->actingAs($admin)->get(route('dokumen.show', $document->id));
        $adminResponse->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $cacheControl = strtolower((string) $adminResponse->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
    }

}
