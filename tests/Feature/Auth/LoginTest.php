<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_login(): void
    {
        $user = $this->createUser();

        $this->postJson(route('api.login'), [
                'email' => $user->email,
                'password' => '123456',
                'company_id' => $user->company->company_id,
            ])
            ->assertOk()
            ->assertJson([
                'access_token' => true,
                'user' => true,
            ]);
    }

    public function test_can_not_login_with_invalid_email()
    {
        $this->postJson(route('api.login'), [
                'email' => 'email_invalid',
                'password' => '123456',
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertInvalid([
                'email' => 'The email field must be a valid email address.',
            ]);
    }

    public function test_can_not_login_with_wrong_password()
    {
        $user = $this->createUser();

        $this->postJson(route('api.login'), [
                'email' => $user->email,
                'password' => 'wrongpassword',
                'company_id' => $user->company_id,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertInvalid([
                'email' => 'The provided credentials are incorrect.',
            ]);
    }

    public function test_can_not_login_with_wrong_company()
    {
        $user = User::factory()->create();

        $this->postJson(route('api.login'), [
                'email' => $user->email,
                'password' => '123456',
                'company_id' => 1234,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertInvalid([
                'email' => 'The provided credentials are incorrect.',
            ]);
    }

    public function test_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.logout'))
            ->assertOk()
            ->assertJson([
                'message' => 'You are logged out.',
            ]);
    }
}
