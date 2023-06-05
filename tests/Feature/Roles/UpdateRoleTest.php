<?php

namespace Tests\Feature\Roles;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_role_detail(): void
    {
        $user = $this->createUser(['is_admin' => true]);
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
        $user = $this->createUser(['is_admin' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.roles.show', ['role' => 999999]))
            ->assertNotFound();
    }

    public function test_can_update_role(): void
    {
        $user = $this->createUser(['is_admin' => true]);
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

    public function test_can_not_update_role_without_name(): void
    {
        $user = $this->createUser(['is_admin' => true]);
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

    public function test_can_not_update_role_without_display_name(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.roles.update', $role), [
                'name' => 'Test update name role',
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'display_name' => __('validation.required', ['attribute' => 'display name']),
            ]);
    }

    public function test_can_not_update_role_without_permission(): void
    {
        $user = $this->createUser();
        $role = Role::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.roles.update', $role), [
                'name' => 'Test update name role',
                'display_name' => 'Test update display_name role',
            ])
            ->assertForbidden();
    }

    public function test_can_assign_permissions_to_role(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.roles.update', $role), [
                'name' => 'Test update name role',
                'display_name' => 'Test update display_name role',
                'permissions' => [$permission->id]
            ])
            ->assertOk()
            ->assertJson([
                'role' => true,
            ]);
        $this->assertGreaterThan(0, $role->refresh()->permissions->count());
    }

    public function test_can_assign_users_to_role(): void
    {
        $auth = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($auth, 'sanctum')
            ->putJson(route('api.roles.update', $role), [
                'name' => 'Test update name role',
                'display_name' => 'Test update display_name role',
                'users' => [$user->id]
            ])
            ->assertOk()
            ->assertJson([
                'role' => true,
            ]);
        $this->assertGreaterThan(0, $role->refresh()->users->count());
    }
}
