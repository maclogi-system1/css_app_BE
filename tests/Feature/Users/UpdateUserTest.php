<?php

namespace Tests\Feature\Users;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_user_detail(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.show', $user))
            ->assertOk()
            ->assertJson([
                'user' => true,
            ]);
    }

    public function test_can_not_found_user(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.show', ['user' => 999999]))
            ->assertNotFound();
    }

    public function test_can_update_user()
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.update', $user), [
                'email' => $user->email,
                'first_name' => 'User',
                'last_name' => 'Updated',
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertOk()
            ->assertJson([
                'user' => true,
            ]);
        $this->assertEquals($user->refresh()->first_name, 'User');
        $this->assertEquals($user->refresh()->last_name, 'Updated');
    }
}
