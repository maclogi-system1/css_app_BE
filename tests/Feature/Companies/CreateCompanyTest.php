<?php

namespace Tests\Feature\Companies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_new_company(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.companies.store'), [
                'company_id' => str()->random(8),
                'name' => $this->faker()->company(),
            ])
            ->assertCreated()
            ->assertJson([
                'company' => true,
            ]);
    }

    public function test_can_not_create_a_new_company_without_name(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.companies.store'), [
                'company_id' => str()->random(8),
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'name' => __('validation.required', ['attribute' => 'name']),
            ]);
    }

    public function test_can_not_create_a_new_company_with_company_id_already_exists(): void
    {
        $user = User::factory()->isSupperAdmin()->create();
        $company = Company::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.companies.store'), [
                'company_id' => $company->company_id,
                'name' => $this->faker()->company(),
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'company_id' => __('validation.unique', ['attribute' => 'company id']),
            ]);
    }

    public function test_can_not_create_a_new_company_without_company_id(): void
    {
        $user = User::factory()->isSupperAdmin()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.companies.store'), [
                'name' => $this->faker()->company(),
            ])
            ->assertUnprocessable()
            ->assertInvalid([
                'company_id' => __('validation.required', ['attribute' => 'company id']),
            ]);
    }

    public function test_can_not_create_a_new_company_without_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.companies.store'), [
                'name' => $this->faker()->company(),
                'email' => $this->faker()->safeEmail(),
            ])
            ->assertForbidden();
    }
}
