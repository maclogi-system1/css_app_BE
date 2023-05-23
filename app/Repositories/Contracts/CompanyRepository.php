<?php

namespace App\Repositories\Contracts;

use App\Models\Company;

interface CompanyRepository extends Repository
{
    /**
     * Handle restore a spcified deleted company.
     */
    public function restore($id): ?Company;

    /**
     * Handle force delete a specified company.
     */
    public function forceDelete(Company $company): void;
}
