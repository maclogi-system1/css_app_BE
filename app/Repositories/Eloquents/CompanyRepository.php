<?php

namespace App\Repositories\Eloquents;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class CompanyRepository extends Repository implements CompanyRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return Company::class;
    }

    /**
     * Get the list of the resource with pagination and handle filter.
     */
    public function getList(array $filters = [], array $columns = ['*']): LengthAwarePaginator|Collection
    {
        if (Arr::has($filters, 'with')) {
            $this->useWith($filters['with']);
        }

        return parent::getList($filters, $columns);
    }

    /**
     * Find a specified user with roles or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): Company|null
    {
        if (Arr::has($filters, 'with')) {
            $this->useWith($filters['with']);
        }

        return $this->queryBuilder()->where('id', $id)->first($columns);
    }

    /**
     * Handle create a new company.
     */
    public function create(array $data): ?Company
    {
        return $this->handleSafely(function () use ($data) {
            $company = $this->model();
            $company->fill($data);
            $company->save();

            return $company;
        }, 'Create company');
    }

    /**
     * Handle update the specified company.
     */
    public function update(array $data, Company $company): ?Company
    {
        $company->fill($data);
        $company->save();

        return $company;
    }

    /**
     * Delete records from the database.
     */
    public function delete(Company $company): ?Company
    {
        $company->delete();

        return $company;
    }
}
