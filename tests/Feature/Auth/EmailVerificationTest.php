<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('home', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
    public function test_registration_sends_verification_and_redirects_to_verification_screen(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Penggalang Baru',
            'email' => 'penggalang@example.test',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);

        $user = User::where('email', 'penggalang@example.test')->firstOrFail();

        $this->assertFalse($user->hasVerifiedEmail());
        $response->assertRedirect(route('verification.notice'));
        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    public function test_unverified_user_can_request_a_new_verification_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_cannot_submit_fundraiser_profile(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('penggalang_dana.organisasi.create'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('verifikasi.penggalang'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->post(route('penggalang_dana.organisasi.store'), [])
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('penggalang.dashboard'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->post(route('fundraiser.store', ['slug' => 'campaign-uji']), [])
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_fundraiser_application_screen(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('penggalang_dana.organisasi.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('verifikasi.penggalang'))
            ->assertOk();
    }


    public function test_unverified_user_cannot_manage_fundraiser_profile(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/penggalang/123/edit')
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->patch('/penggalang/123', [])
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->delete('/penggalang/123/dokumen', [])
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('penggalang_dana.rejected'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->patch('/penggalang-dana/123/resubmit', [])
            ->assertRedirect(route('verification.notice'));
    }

}
