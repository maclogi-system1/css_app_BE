<?php

namespace Tests\Feature\Roles;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_list_of_role(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        Role::factory(20)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.roles.index'))
            ->assertOk()
            ->assertJson([
                'roles' => true,
                'links' => true,
                'meta' => true,
            ]);
    }

    public function test_can_search_role_by_name(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $roles = Role::factory(20)->create();
        $search = $roles->first();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.roles.index', ['search' => ['name' => $search->name]]))
            ->assertOk()
            ->assertJson([
                'roles' => true,
                'links' => true,
                'meta' => true,
            ]);

        $result = $response->collect('roles');
        $this->assertTrue($result->isNotEmpty());
        $this->assertTrue($result->where('name', $search->name)->isNotEmpty());
    }

    public function test_can_filter_role_by_name(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $roles = Role::factory(20)->create();
        $search = $roles->first();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.roles.index', ['filter' => ['name' => $search->name]]))
            ->assertOk()
            ->assertJson([
                'roles' => true,
                'links' => true,
                'meta' => true,
            ]);

        $result = $response->collect('roles');
        $this->assertTrue($result->isNotEmpty());
        $this->assertTrue($result->where('name', $search->name)->isNotEmpty());
    }
}
