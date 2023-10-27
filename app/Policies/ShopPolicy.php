<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Arr;

class ShopPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, array $shop): bool
    {
        if ($user->can('view_all_shops')) {
            return true;
        }

        $shopCompanyId = Arr::get($shop, 'company_id');
        if ($user->can('view_all_company_shops') && $shopCompanyId == $user->company_id) {
            return true;
        }

        $isContract = Arr::get($shop, 'is_contract');
        if ($user->can('view_company_contract_shops') && $isContract && $shopCompanyId == $user->company_id) {
            return true;
        }

        $createdById = Arr::get($shop, 'created_by.id');
        if ($user->can('view_shops') && $user->id == $createdById) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, int $companyId): bool
    {
        if ($user->can('create_all_shops')) {
            return true;
        }

        if ($user->can('create_all_company_shops') && $user->company_id == $companyId) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int $companyId, array $createdUserIds = []): bool
    {
        if ($user->can('edit_all_shops')) {
            return true;
        }

        if ($user->can('edit_all_company_shops') && $user->company_id == $companyId) {
            return true;
        }

        if ($user->can('edit_shops') && in_array($user->id, $createdUserIds)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int $companyId): bool
    {
        if ($user->can('delete_all_shops')) {
            return true;
        }

        if ($user->can('delete_all_company_shops') && $user->company_id == $companyId) {
            return true;
        }

        return false;
    }
}
