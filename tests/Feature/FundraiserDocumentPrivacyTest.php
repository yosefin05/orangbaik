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
