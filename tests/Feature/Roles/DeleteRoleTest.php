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
        $user = User::factory()->isSupperAdmin()->create();
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
        $user = User::factory()->create();
        $roleDelete = Role::factory([
            'name' => 'Role delete'
        ])->create();

        $this->actingAs($user)
            ->deleteJson(route('api.roles.destroy', $roleDelete))
            ->assertForbidden();
        $this->assertNotNull(Role::where('name', 'Role delete')->first());
    }
}
