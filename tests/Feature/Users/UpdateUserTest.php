<?php

namespace Tests\Feature\Users;

use App\Models\Company;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_user_detail(): void
    {
        $user = $this->createUser(['is_admin' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.show', $user))
            ->assertOk()
            ->assertJson([
                'user' => true,
            ]);
    }

    public function test_can_not_found_user(): void
    {
        $user = $this->createUser(['is_admin' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users.show', ['user' => 999999]))
            ->assertNotFound();
    }

    public function test_can_update_user(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->post(route('api.users.update', $user), [
                'email' => $user->email,
                'name' => 'User Updated',
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ], [
                'Content-Type' => 'multipart/form-data',
            ])
            ->assertOk()
            ->assertJson([
                'user' => true,
            ]);
        $this->assertEquals($user->refresh()->name, 'User Updated');
    }

    public function test_can_not_update_user_with_email_already_exists(): void
    {
        $auth = $this->createUser(['is_admin' => true]);
        $userUpdated = $this->createUser();
        $userDuplicated = $this->createUser();
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($auth, 'sanctum')
            ->postJson(route('api.users.update', $userUpdated), [
                'name' => 'Create New User',
                'email' => $userDuplicated->email,
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => __('validation.unique', ['attribute' => 'email']),
            ]);
    }

    public function test_can_not_update_user_without_email(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->post(route('api.users.update', $user), [
                'name' => 'User Updated',
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ], [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => __('validation.required', ['attribute' => 'email']),
            ]);
    }

    public function test_can_not_update_user_without_name(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->post(route('api.users.update', $user), [
                'email' => $user->email,
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ], [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
    }

    public function test_can_not_update_user_without_company(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->post(route('api.users.update', $user), [
                'email' => $user->email,
                'name' => 'User Updated',
                'roles' => [$role->id],
                'teams' => [$team->id],
            ], [
                'Content-Type' => 'multipart/form-data',
                'Accept' => 'application/json',
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'company_id' => __('validation.required', ['attribute' => 'company id']),
            ]);
    }
}
