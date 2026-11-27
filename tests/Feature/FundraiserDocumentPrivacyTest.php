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

        $this->get(route('dokumen.show', $document->id))->assertRedirect(route('login'));
        $this->actingAs($owner)->get(route('dokumen.show', $document->id))->assertForbidden();
        $this->actingAs($otherUser)->get(route('dokumen.show', $document->id))->assertForbidden();

        // Legacy public-disk documents remain unavailable even to admins until
        // they have been safely migrated into private storage.
        $this->actingAs($admin)->get(route('dokumen.show', $document->id))->assertNotFound();
    }

    public function test_owner_edit_forms_never_render_verification_document_names_or_paths(): void
    {
        Storage::fake('private');

        foreach (['organisasi', 'individu'] as $category) {
            $owner = User::factory()->create();
            $fundraiser = Penggalang_Dana::create([
                'user_id' => $owner->id,
                'jenis_penggalang' => $category,
                'foto_profil' => 'profile.jpg',
                'nama_penggalang' => 'Profil ' . ucfirst($category),
                'alamat' => 'Sidoarjo',
                'deskripsi' => 'Profil pengujian privasi dokumen.',
                'email' => 'edit-privacy-' . $owner->id . '@example.test',
                'no_telepon' => '0812345' . str_pad((string) $owner->id, 4, '0', STR_PAD_LEFT),
                'status' => 'approved',
            ]);

            $secretName = 'NAMA DOKUMEN RAHASIA ' . strtoupper($category);
            $secretPath = 'dokumen_penggalang/' . $fundraiser->id . '/private-' . $category . '-path.pdf';
            Storage::disk('private')->put($secretPath, 'private document fixture');

            Penggalang_Dana_Dokumen::create([
                'penggalang_dana_id' => $fundraiser->id,
                'nama_dokumen' => $secretName,
                'file_dokumen' => $secretPath,
                'is_public' => false,
                'storage_disk' => 'private',
            ]);

            $response = $this->actingAs($owner)->get(route('penggalang_dana.edit', $fundraiser->id));

            $response->assertOk()
                ->assertDontSee($secretName)
                ->assertDontSee($secretPath)
                ->assertSee($category === 'organisasi' ? 'Dokumen Legalitas 1' : 'Dokumen Identitas');
        }
    }

    public function test_verification_upload_count_is_bounded_for_individual_and_organization_submissions(): void
    {
        Storage::fake('private');

        $individual = User::factory()->create();
        $individualResponse = $this->actingAs($individual)->post(route('penggalang_dana.individu.store'), [
            'nama_penggalang' => 'Penggalang Individu Batas',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Pengujian batas dokumen.',
            'email' => 'individual-limit-' . $individual->id . '@example.test',
            'no_telepon' => '0812345' . str_pad((string) $individual->id, 4, '0', STR_PAD_LEFT),
            'nama_dokumen' => ['Dokumen Identitas', 'Dokumen Tambahan'],
            'dokumen_files' => [
                UploadedFile::fake()->create('identity.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('extra.pdf', 100, 'application/pdf'),
            ],
        ]);

        $individualResponse->assertSessionHasErrors('nama_dokumen');
        $this->assertDatabaseMissing('penggalang_dana', ['user_id' => $individual->id]);

        $organization = User::factory()->create();
        $organizationResponse = $this->actingAs($organization)->post(route('penggalang_dana.organisasi.store'), [
            'jenis_penggalang' => 'organisasi',
            'nama_penggalang' => 'Organisasi Batas Dokumen',
            'tahun_berdiri' => '2020',
            'alamat' => 'Sidoarjo',
            'deskripsi' => 'Pengujian batas dokumen organisasi.',
            'visi' => 'Visi uji.',
            'misi' => 'Misi uji.',
            'email' => 'organization-limit-' . $organization->id . '@example.test',
            'no_telepon' => '0812345' . str_pad((string) $organization->id, 4, '0', STR_PAD_LEFT),
            'nama_dokumen' => ['Dokumen 1', 'Dokumen 2', 'Dokumen 3', 'Dokumen 4'],
            'dokumen_files' => [
                UploadedFile::fake()->create('doc-1.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('doc-2.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('doc-3.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('doc-4.pdf', 100, 'application/pdf'),
            ],
        ]);

        $organizationResponse->assertSessionHasErrors(['nama_dokumen', 'dokumen_files']);
        $this->assertDatabaseMissing('penggalang_dana', ['user_id' => $organization->id]);
        $this->assertDatabaseCount('penggalang_dana_dokumen', 0);
        $this->assertEmpty(Storage::disk('private')->allFiles());
    }

}
