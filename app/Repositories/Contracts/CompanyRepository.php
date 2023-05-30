<?php

namespace App\Repositories\Contracts;

use App\Models\Company;
use App\Models\User;

interface CompanyRepository extends Repository
{
    /**
     * Handle create a new company.
     */
    public function create(array $data, User $auth): ?Company;

    /**
     * Handle update the specified company.
     */
    public function update(array $data, Company $company, User $auth): ?Company;

    /**
     * Delete records from the database.
     */
    public function delete(Company $company): ?Company;

    /**
     * Handle restore a spcified deleted company.
     */
    public function restore($id): ?Company;

    /**
     * Handle force delete a specified company.
     */
    public function forceDelete(Company $company): void;
}
