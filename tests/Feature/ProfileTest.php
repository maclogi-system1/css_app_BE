<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_user_profile(): void
    {
        $user = User::factory()->make();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.user.info'))
            ->assertOk()
            ->assertJson($user->toArray());
    }

    public function test_can_update_profile_information(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.user.update-user-profile-info'), [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertOk();

        $user->refresh();

        $this->assertSame('Test', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame('test@example.com', $user->email);
    }

    public function test_can_update_profile_photo(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($user, 'sanctum')
            ->post(route('api.user.update-profile-photo'), [
                'photo' => $file,
            ], [
                'Content-Type' => 'multipart/form-data'
            ])
            ->assertSessionHasNoErrors()
            ->assertOk();

        $this->assertNotEmpty($user->refresh()->profile_photo_path);
    }
}
