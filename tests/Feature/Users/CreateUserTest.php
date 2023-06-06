<?php

namespace Tests\Feature\Users;

use App\Models\Company;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_new_user(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.1@example.com',
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ])
            ->assertCreated()
            ->assertJson([
                'user' => true,
            ]);
    }

    public function test_can_not_create_a_new_user_without_name(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'email' => 'create_new_user.1@example.com',
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
    }

    public function test_can_not_create_a_new_user_without_email(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => __('validation.required', ['attribute' => 'email']),
            ]);
    }

    public function test_can_not_create_a_new_user_with_invalid_email(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.1',
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => __('validation.email', ['attribute' => 'email']),
            ]);
    }

    public function test_can_not_create_a_new_user_with_email_already_exists(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => $user->email,
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => __('validation.unique', ['attribute' => 'email']),
            ]);
    }

    public function test_can_not_create_a_new_user_without_permission(): void
    {
        $user = $this->createUser();
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.2@example.com',
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ])
            ->assertForbidden();
    }

    public function test_can_assign_role_to_user(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $role = Role::factory()->create([
            'name' => 'Role testing',
        ]);
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.3@example.com',
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ])
            ->assertCreated();
        $this->assertTrue(User::where('email', 'create_new_user.3@example.com')->first()->hasRole('Role testing'));
    }
}
