<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function create(User $user): bool
    {
        if ($user->can('edit_all_companies')) {
            return true;
        }

//        if ($user->can('edit_owned_company')) {
//            return true;
//        }

        return false;
    }

    public function update(User $user, Company $company): bool
    {
        if ($user->can('edit_all_companies')) {
            return true;
        }

        if ($user->can('edit_owned_company') && $user->company_id == $company->id) {
            return true;
        }

        return false;
    }
}
