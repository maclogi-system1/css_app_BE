<?php

namespace Tests\Feature\Companies;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_speccify_company(): void
    {
        $user = $this->createUser(['is_admin' => true]);
        $companyDelete = Company::factory([
            'name' => 'Company Delete'
        ])->create();

        $this->actingAs($user)
            ->deleteJson(route('api.companies.destroy', $companyDelete))
            ->assertOk();
        $this->assertNotEmpty($companyDelete->refresh()->deleted_at);
    }

    public function test_can_not_delete_user_without_permission(): void
    {
        $user = $this->createUser();
        $companyDelete = Company::factory([
            'name' => 'Company Delete'
        ])->create();

        $this->actingAs($user)
            ->deleteJson(route('api.companies.destroy', $companyDelete))
            ->assertForbidden();
        $this->assertEmpty($companyDelete->refresh()->deleted_at);
    }
}
