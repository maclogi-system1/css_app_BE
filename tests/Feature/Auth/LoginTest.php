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

    public function test_can_login_company(): void
    {
        $company = Company::factory()->create();

        $this->postJson(route('api.login-company'), [
                'company_id' => $company->company_id,
                'password' => '123456',
            ])
            ->assertOk()
            ->assertJson([
                'access_token' => true,
                'company' => $company->toArray(),
            ]);
    }

    public function test_can_login(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $response = $this->postJson(route('api.login-company'), [
            'company_id' => $company->company_id,
            'password' => '123456',
        ]);

        $companyAccessToken = $response->json('access_token');

        $this->postJson(route('api.login'), [
                'email' => $user->email,
                'password' => '123456',
                'company_access_token' => $companyAccessToken,
            ])
            ->assertOk()
            ->assertJson([
                'access_token' => true,
                'user' => $user->toArray(),
            ]);
    }

    public function test_can_not_login_with_invalid_email()
    {
        $this->postJson(route('api.login'), [
                'email' => 'email_invalid',
                'password' => '123456',
                'company_access_token' => '1|thisIsForTesting',
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertInvalid([
                'email' => 'The email field must be a valid email address.',
            ]);
    }

    public function test_can_not_login_with_wrong_password()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $response = $this->postJson(route('api.login-company'), [
            'company_id' => $company->company_id,
            'password' => '123456',
        ]);

        $companyAccessToken = $response->json('access_token');

        $this->postJson(route('api.login'), [
                'email' => $user->email,
                'password' => 'wrong_password',
                'company_access_token' => $companyAccessToken,
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertInvalid([
                'email' => 'The provided credentials are incorrect.',
            ]);
    }

    public function test_can_not_login_with_wrong_company_access_token()
    {
        $user = User::factory()->create();

        $this->postJson(route('api.login'), [
                'email' => $user->email,
                'password' => '123456',
                'company_access_token' => '1|thisIsForTesting',
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertInvalid([
                'company_access_token' => 'The provided credentials are incorrect.',
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
