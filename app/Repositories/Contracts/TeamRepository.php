<?php

namespace App\Repositories\Contracts;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TeamRepository extends Repository
{
    /**
     * Get the list of the team with pagination and handle filter by company.
     */
    public function getListByCompany(Company $company, array $filters = []): LengthAwarePaginator|Collection;
}
