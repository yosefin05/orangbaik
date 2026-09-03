<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\SyaratKetentuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPageContentTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_guest_sees_terms_and_faq_from_database(): void
    {
        SyaratKetentuan::create([
            'judul' => 'Ketentuan Donasi',
            'isi' => "Paragraf pertama.\nParagraf kedua.",
            'urutan' => 1,
            'is_active' => true,
        ]);

        Faq::create([
            'pertanyaan' => 'Apakah donasi saya aman?',
            'jawaban' => 'Donasi diproses secara aman.',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $this->get(route('syarat.ketentuan'))
            ->assertOk()
            ->assertSee('Ketentuan Donasi')
            ->assertSee('Paragraf pertama.')
            ->assertSee('Apakah donasi saya aman?');

        $this->get(route('pusat.bantuan'))
            ->assertOk()
            ->assertSee('Apakah donasi saya aman?')
            ->assertSee('Donasi diproses secara aman.');

        $this->get(route('tentang'))
            ->assertOk()
            ->assertSee('Apakah donasi saya aman?');
    }

    public function test_inactive_content_is_hidden_from_public_pages(): void
    {
        SyaratKetentuan::create([
            'judul' => 'Bagian Nonaktif',
            'isi' => 'Isi tersembunyi.',
            'urutan' => 1,
            'is_active' => false,
        ]);

        Faq::create([
            'pertanyaan' => 'Pertanyaan tersembunyi',
            'jawaban' => 'Jawaban tersembunyi.',
            'urutan' => 1,
            'is_active' => false,
        ]);

        $this->get(route('syarat.ketentuan'))
            ->assertOk()
            ->assertDontSee('Bagian Nonaktif')
            ->assertDontSee('Pertanyaan tersembunyi');
    }

    public function test_admin_can_crud_syarat_ketentuan(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->post(route('admin.syarat-ketentuan.store'), [
                'judul' => 'Kewajiban Pengguna',
                'isi' => 'Pengguna wajib menjaga akun.',
                'urutan' => 1,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.syarat-ketentuan.index'));

        $this->assertDatabaseHas('syarat_ketentuan', [
            'judul' => 'Kewajiban Pengguna',
            'isi' => 'Pengguna wajib menjaga akun.',
            'is_active' => 1,
        ]);

        $item = SyaratKetentuan::first();

        $this->actingAs($admin)
            ->put(route('admin.syarat-ketentuan.update', $item), [
                'judul' => 'Kewajiban Pengguna Diperbarui',
                'isi' => 'Isi baru.',
                'urutan' => 2,
            ])
            ->assertRedirect(route('admin.syarat-ketentuan.index'));

        $this->assertDatabaseHas('syarat_ketentuan', [
            'id' => $item->id,
            'judul' => 'Kewajiban Pengguna Diperbarui',
            'is_active' => 0,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.syarat-ketentuan.destroy', $item))
            ->assertRedirect(route('admin.syarat-ketentuan.index'));

        $this->assertDatabaseMissing('syarat_ketentuan', [
            'id' => $item->id,
        ]);
    }

    public function test_admin_can_crud_faq(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->post(route('admin.faq.store'), [
                'pertanyaan' => 'Bagaimana cara donasi?',
                'jawaban' => 'Pilih campaign lalu bayar.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.faq.index'));

        $this->assertDatabaseHas('faq', [
            'pertanyaan' => 'Bagaimana cara donasi?',
            'jawaban' => 'Pilih campaign lalu bayar.',
        ]);

        $faq = Faq::first();

        $this->actingAs($admin)
            ->put(route('admin.faq.update', $faq), [
                'pertanyaan' => 'Bagaimana cara donasi online?',
                'jawaban' => 'Jawaban diperbarui.',
                'urutan' => 3,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.faq.index'));

        $this->assertDatabaseHas('faq', [
            'id' => $faq->id,
            'pertanyaan' => 'Bagaimana cara donasi online?',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.faq.destroy', $faq))
            ->assertRedirect(route('admin.faq.index'));

        $this->assertDatabaseMissing('faq', [
            'id' => $faq->id,
        ]);
    }

    public function test_non_admin_cannot_manage_page_content(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.faq.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.syarat-ketentuan.index'))
            ->assertForbidden();
    }
}
