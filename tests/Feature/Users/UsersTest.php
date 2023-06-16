<?php

namespace Tests\Feature\Users;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_list_of_user(): void
    {
        $user = $this->createUser(['is_admin' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.index'))
            ->assertOk()
            ->assertJson([
                'users' => true,
                'links' => true,
                'meta' => true,
            ]);
    }

    public function test_can_search_user_by_name(): void
    {
        $user = $this->createUser(['is_admin' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.index', ['search' => ['name' => $user->name]]))
            ->assertOk()
            ->assertJson([
                'users' => true,
                'links' => true,
                'meta' => true,
            ]);
        $result = $response->collect('users');
        $this->assertTrue($result->isNotEmpty());
    }

    public function test_can_search_user_by_email(): void
    {
        $user = $this->createUser(['is_admin' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.index', ['search' => ['email' => $user->email]]))
            ->assertOk()
            ->assertJson([
                'users' => true,
                'links' => true,
                'meta' => true,
            ]);
        $result = $response->collect('users');
        $this->assertTrue($result->isNotEmpty());
        $this->assertTrue($result->where('email', $user->email)->isNotEmpty());
    }

    public function test_can_search_user_by_company(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $company = Company::factory()->has(User::factory()->count(2))->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.index', ['search' => ['company' => $company->name]]))
            ->assertOk()
            ->assertJson([
                'users' => true,
                'links' => true,
                'meta' => true,
            ]);
        $result = $response->collect('users');
        $this->assertTrue($result->isNotEmpty());
    }

    public function test_can_search_user_by_role(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->has(User::factory()->count(2))->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.index', ['search' => ['role' => $role->name]]))
            ->assertOk()
            ->assertJson([
                'users' => true,
                'links' => true,
                'meta' => true,
            ]);
        $result = $response->collect('users');
        $this->assertTrue($result->isNotEmpty());
    }

    public function test_can_search_multiple_user_name(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $userNames = $this->createUser(count: 2)->pluck('name')->join(',');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.index', ['searches' => ['name' => $userNames]]))
            ->assertOk()
            ->assertJson([
                'users' => true,
                'links' => true,
                'meta' => true,
            ]);
        $result = $response->collect('users');
        $this->assertEquals(2, $result->count());
    }

    public function test_can_search_multiple_user_email(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $userNames = $this->createUser(count: 2)->pluck('email')->join(',');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.index', ['searches' => ['email' => $userNames]]))
            ->assertOk()
            ->assertJson([
                'users' => true,
                'links' => true,
                'meta' => true,
            ]);
        $result = $response->collect('users');
        $this->assertEquals(2, $result->count());
    }

    public function test_can_filter_multiple_user_name(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $userNames = $this->createUser(count: 2)->pluck('name')->join(',');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.index', ['filters' => ['name' => $userNames]]))
            ->assertOk()
            ->assertJson([
                'users' => true,
                'links' => true,
                'meta' => true,
            ]);
        $result = $response->collect('users');
        $this->assertEquals(2, $result->count());
    }

    public function test_can_filter_multiple_user_email(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $userNames = $this->createUser(count: 2)->pluck('email')->join(',');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.index', ['filters' => ['email' => $userNames]]))
            ->assertOk()
            ->assertJson([
                'users' => true,
                'links' => true,
                'meta' => true,
            ]);
        $result = $response->collect('users');
        $this->assertEquals(2, $result->count());
    }
}
