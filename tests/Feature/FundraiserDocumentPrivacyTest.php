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
}
