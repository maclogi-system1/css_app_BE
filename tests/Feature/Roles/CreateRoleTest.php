<?php

namespace Tests\Feature\Roles;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_new_role(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.roles.store'), [
                'name' => 'Create new role',
                'display_name' => 'Display name',
            ])
            ->assertCreated()
            ->assertJson([
                'role' => true,
            ]);
    }

    public function test_can_not_create_a_new_role_without_name(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.roles.store'), [
                'display_name' => 'Test',
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
    }

    public function test_can_not_create_a_new_user_without_display_name(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.roles.store'), [
                'name' => 'Test',
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'display_name' => __('validation.required', ['attribute' => 'display name']),
            ]);
    }

    public function test_can_not_create_a_new_user_with_name_already_exists(): void
    {
        $user = User::factory()->isSupperAdmin()->create();
        $role = Role::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.roles.store'), [
                'name' => $role->name,
                'display_name' => 'Test',
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'name' => __('validation.unique', ['attribute' => 'name']),
            ]);
    }

    public function test_can_not_create_a_new_user_with_display_name_already_exists(): void
    {
        $user = User::factory()->isSupperAdmin()->create();
        $role = Role::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.roles.store'), [
                'name' => 'Test name',
                'display_name' => $role->display_name,
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'display_name' => __('validation.unique', ['attribute' => 'display name']),
            ]);
    }

    public function test_can_not_create_a_new_user_without_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.roles.store'), [
                'name' => 'Test name 1',
                'display_name' => 'Test display name 1',
            ])
            ->assertForbidden();
    }

    public function test_can_assign_user_to_role(): void
    {
        $user = User::factory()->isSupperAdmin()->create();
        $anotherUser = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.roles.store'), [
                'name' => 'Test with users',
                'display_name' => 'Test with users',
                'users' => [$anotherUser->id],
            ])
            ->assertCreated();
        $this->assertTrue(Role::where('name', 'Test with users')->first()->hasUser($anotherUser->id));
    }
}
