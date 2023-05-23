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
        $user = User::factory()->isSupperAdmin()->create();
        $role = Role::factory()->create();
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.1@example.com',
                'password' => 'pass123456',
                'password_confirmation' => 'pass123456',
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
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'email' => 'create_new_user.1@example.com',
                'password' => 'pass123456',
                'password_confirmation' => 'pass123456',
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
    }

    public function test_can_not_create_a_new_user_without_email(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'password' => 'pass123456',
                'password_confirmation' => 'pass123456',
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => __('validation.required', ['attribute' => 'email']),
            ]);
    }

    public function test_can_not_create_a_new_user_with_invalid_email(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.1',
                'password' => 'pass123456',
                'password_confirmation' => 'pass123456',
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => __('validation.email', ['attribute' => 'email']),
            ]);
    }

    public function test_can_not_create_a_new_user_with_email_already_exists(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => $user->email,
                'password' => 'pass123456',
                'password_confirmation' => 'pass123456',
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'email' => __('validation.unique', ['attribute' => 'email']),
            ]);
    }

    public function test_can_not_create_a_new_user_without_password(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.1@example.com',
                'password_confirmation' => 'pass123456',
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'password' => __('validation.required', ['attribute' => 'password']),
            ]);
    }

    public function test_can_not_create_a_new_user_without_confirm_password(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.1@example.com',
                'password' => 'pass123456',
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'password' => __('validation.confirmed', ['attribute' => 'password']),
            ]);
    }

    public function test_can_not_create_a_new_user_without_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.2@example.com',
                'password' => 'pass123456',
                'password_confirmation' => 'pass123456',
                'company_id' => Company::factory()->create()->id,
            ])
            ->assertForbidden();
    }

    public function test_can_assign_role_to_user(): void
    {
        $user = User::factory()->isSupperAdmin()->create();
        $role = Role::factory()->create([
            'name' => 'Role testing',
        ]);
        $team = Team::factory()->for(Company::factory())->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.store'), [
                'name' => 'Create New User',
                'email' => 'create_new_user.3@example.com',
                'password' => 'pass123456',
                'password_confirmation' => 'pass123456',
                'company_id' => Company::factory()->create()->id,
                'roles' => [$role->id],
                'teams' => [$team->id],
            ])
            ->assertCreated();
        $this->assertTrue(User::where('email', 'create_new_user.3@example.com')->first()->hasRole('Role testing'));
    }
}
