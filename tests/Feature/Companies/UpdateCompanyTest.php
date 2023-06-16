<?php

namespace Tests\Feature\Companies;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_company_detail(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $company = Company::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.companies.show', $company))
            ->assertOk()
            ->assertJson([
                'company' => true,
            ]);
    }

    public function test_can_not_found_company(): void
    {
        $user = $this->createUser(['is_admin' => true]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.companies.show', ['company' => 999999]))
            ->assertNotFound();
    }

    public function test_can_update_company(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $company = Company::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.companies.update', $company), [
                'company_id' => 'company_updated',
                'name' => 'Company Updated',
            ])
            ->assertOk()
            ->assertJson([
                'company' => true,
            ]);
        $this->assertEquals($company->refresh()->name, 'Company Updated');
        $this->assertEquals($company->refresh()->company_id, 'company_updated');
    }
}
