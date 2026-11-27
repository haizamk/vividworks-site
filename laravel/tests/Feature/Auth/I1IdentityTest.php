<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class I1IdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_unverified_user_and_sends_notification(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_login_returns_token_and_verification_flag(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('email_verified', false)
            ->assertJsonStructure(['token']);
    }

    public function test_signed_verification_marks_user_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $response = $this->getJson($url);
        $response->assertOk();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_f1_design_generation_route_still_unauthenticated(): void
    {
        $this->postJson('/api/ai/design-generations', [])
            ->assertStatus(422);
    }
}