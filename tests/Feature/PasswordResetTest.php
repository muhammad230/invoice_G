<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertStatus(200)
            ->assertSee('Reset your password');
    }

    public function test_reset_link_is_emailed_to_a_known_user(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'known@example.com']);

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertRedirect();
        $response->assertSessionHas('status', __('passwords.sent'));

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_response_is_identical_for_an_unknown_email(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), ['email' => 'nobody@example.com']);

        // Same redirect + flash message as the known-email case (no user
        // enumeration), and of course no notification is sent.
        $response->assertRedirect();
        $response->assertSessionHas('status', __('passwords.sent'));

        Notification::assertNothingSent();
    }

    public function test_reset_requests_are_throttled(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'throttle@example.com']);

        // The first three requests go through.
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('password.email'), ['email' => $user->email])
                ->assertSessionHasNoErrors();
        }

        // The fourth within the same minute is blocked.
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasErrors('email');
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user  = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'a-fresh-password',
            'password_confirmation' => 'a-fresh-password',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertTrue(Hash::check('a-fresh-password', $user->fresh()->password));
    }

    public function test_password_reset_rejects_an_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->post(route('password.update'), [
            'token'                 => 'obviously-wrong',
            'email'                 => $user->email,
            'password'              => 'a-fresh-password',
            'password_confirmation' => 'a-fresh-password',
        ])->assertSessionHasErrors('email');

        // The old password is still in place.
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
