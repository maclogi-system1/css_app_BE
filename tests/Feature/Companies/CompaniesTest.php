<?php

namespace Tests\Feature\Companies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompaniesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_list_of_company(): void
    {
        $user = User::factory()->isSupperAdmin()->create();
        Company::factory(20)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.companies.index'))
            ->assertOk()
            ->assertJson([
                'companies' => true,
                'links' => true,
                'meta' => true,
            ]);
    }

    public function test_can_search_company_by_name(): void
    {
        $user = User::factory()->isSupperAdmin()->create();
        $companies = Company::factory(20)->create();
        $search = $companies->first();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.companies.index', ['search' => ['name' => $search->name]]))
            ->assertOk()
            ->assertJson([
                'companies' => true,
                'links' => true,
                'meta' => true,
            ]);

        $result = $response->collect('companies');
        $this->assertTrue($result->isNotEmpty());
        $this->assertTrue($result->where('name', $search->name)->isNotEmpty());
    }

    public function test_can_search_company_by_company_id(): void
    {
        $user = User::factory()->isSupperAdmin()->create();
        $companies = Company::factory(20)->create();
        $search = $companies->first();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.companies.index', ['search' => ['company_id' => $search->company_id]]))
            ->assertOk()
            ->assertJson([
                'companies' => true,
                'links' => true,
                'meta' => true,
            ]);

        $result = $response->collect('companies');
        $this->assertTrue($result->isNotEmpty());
        $this->assertTrue($result->where('company_id', $search->company_id)->isNotEmpty());
    }

    public function test_can_not_view_list_of_company_without_permission(): void
    {
        $user = User::factory()->create();
        Company::factory(20)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.companies.index'))
            ->assertForbidden();
    }
}
