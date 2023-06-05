<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_user_profile(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.user.profile'))
            ->assertOk()
            ->assertJson([
                'user' => true,
            ]);
    }

    public function test_can_update_profile_information(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.user.update-user-profile-info'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'team_id' => 1,
                'chatwork_account_id' => 8177903,
            ])
            ->assertOk();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
    }

    public function test_can_update_profile_photo(): void
    {
        $user = $this->createUser();
        $file = UploadedFile::fake()->image('avatar.png');

        $this->actingAs($user, 'sanctum')
            ->post(route('api.user.update-profile-photo'), [
                'profile_photo_path' => $file,
            ], [
                'Content-Type' => 'multipart/form-data'
            ])
            ->assertSessionHasNoErrors()
            ->assertOk();

        $this->assertNotEmpty($user->refresh()->profile_photo_path);
    }

    public function test_can_get_company_of_user(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.user.company.show'))
            ->assertOk()
            ->assertJson([
                'company' => true,
            ]);
        $company = $response->json('company');

        $this->assertEquals($user->company_id, $company['id']);
    }

    public function test_can_update_company_of_user(): void
    {
        $user = $this->createUser(['is_admin' => true]);

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.user.company.update'), [
                'company_id' => 'company_id',
                'name' => 'Sample Company',
                'team_names' => [],
            ])
            ->assertOk()
            ->assertJson([
                'company' => true,
            ]);
    }

    public function test_can_update_company_of_user_and_create_teams(): void
    {
        $user = $this->createUser(['is_admin' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson(route('api.user.company.update'), [
                'company_id' => 'company_id',
                'name' => 'Sample Company',
                'team_names' => ['TeamA'],
            ])
            ->assertOk()
            ->assertJson([
                'company' => true,
            ]);

        $company = $response->json('company');
    }

    public function test_can_not_update_company_of_user_when_not_admin(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.user.company.update'), [
                'company_id' => 'company_id',
                'name' => 'Sample Company',
                'team_names' => [],
            ])
            ->assertForbidden();
    }
}
