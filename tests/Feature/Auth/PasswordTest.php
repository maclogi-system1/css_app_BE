<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_can_get_password_reset_token(): void
    {
        $user = User::factory()->create();
        $this->postJson(route('api.password-reset-token'), [
                'email' => $user->email,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'token' => true,
            ]);
    }

    public function test_can_reset_password()
    {
        $user = User::factory()->create();
        $response = $this->postJson(route('api.password-reset-token'), [
            'email' => $user->email,
        ]);

        $token = $response->json('token');

        $this->postJson(route('api.reset-password'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertStatus(Response::HTTP_ACCEPTED)
            ->assertJson([
                'message' => __('passwords.reset'),
            ]);
        $this->assertTrue(Hash::check('newpassword123', $user->refresh()->password));
    }

    public function test_can_update_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.user.update-password'), [
                'current_password' => '123456',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Your password has been change.',
            ]);
        $this->assertTrue(Hash::check('newpassword123', $user->refresh()->password));
    }

    public function test_can_not_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.user.update-password'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertInvalid([
                'current_password' => __('validation.current_password'),
            ]);
        $this->assertFalse(Hash::check('newpassword123', $user->refresh()->password));
    }

    public function test_can_not_update_password_with_confirmation_does_not_match(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.user.update-password'), [
                'current_password' => '123456',
                'password' => 'newpassword123',
                'password_confirmation' => 'wrongpassword',
            ]);

        $this->assertFalse(Hash::check('newpassword123', $user->refresh()->password));
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertInvalid([
            'password' => __('validation.confirmed', ['attribute' => 'password']),
        ]);
    }
}
