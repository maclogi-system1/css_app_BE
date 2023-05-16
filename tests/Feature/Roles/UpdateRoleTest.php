<?php

namespace Tests\Feature\Roles;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_role_detail(): void
    {
        $user = User::factory()->isSupperAdmin()->create();
        $role = Role::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.roles.show', $role))
            ->assertOk()
            ->assertJson([
                'role' => true,
            ]);
    }

    public function test_can_not_found_role(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.roles.show', ['role' => 999999]))
            ->assertNotFound();
    }

    public function test_can_update_role()
    {
        $user = User::factory()->isSupperAdmin()->create();
        $role = Role::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.roles.update', $role), [
                'name' => 'Test update name role',
                'display_name' => 'Test update display_name role',
            ])
            ->assertOk()
            ->assertJson([
                'role' => true,
            ]);
        $this->assertEquals($role->refresh()->name, 'Test update name role');
        $this->assertEquals($role->refresh()->display_name, 'Test update display_name role');
    }

    public function test_can_not_update_role_without_name()
    {
        $user = User::factory()->isSupperAdmin()->create();
        $role = Role::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.roles.update', $role), [
                'display_name' => 'Test update display_name role',
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
    }
}
