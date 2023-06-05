<?php

namespace Tests\Feature\Roles;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_speccify_role(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $roleDelete = Role::factory([
            'name' => 'Role delete'
        ])->create();

        $this->actingAs($user)
            ->deleteJson(route('api.roles.destroy', $roleDelete))
            ->assertOk();
        $this->assertNull(Role::where('name', 'Role delete')->first());
    }

    public function test_can_not_delete_role_without_permission(): void
    {
        $user = $this->createUser();
        $roleDelete = Role::factory([
            'name' => 'Role delete'
        ])->create();

        $this->actingAs($user)
            ->deleteJson(route('api.roles.destroy', $roleDelete))
            ->assertForbidden();
        $this->assertNotNull(Role::where('name', 'Role delete')->first());
    }

    public function test_can_delete_multiple_roles(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $roleDelete = Role::factory(2)->create();

        $this->actingAs($user)
            ->deleteJson(route('api.roles.delete-multiple', $roleDelete->mapWithKeys(function ($item, $key) {
                return ["role_ids[{$key}]" => $item->id];
            })->toArray()))
            ->assertOk();
    }

    public function test_can_not_delete_multiple_roles_without_permission(): void
    {
        $user = $this->createUser();
        $roleDelete = Role::factory(2)->create();

        $this->actingAs($user)
            ->deleteJson(route('api.roles.delete-multiple', $roleDelete->mapWithKeys(function ($item, $key) {
                return ["role_ids[{$key}]" => $item->id];
            })->toArray()))
            ->assertForbidden();
    }
}
