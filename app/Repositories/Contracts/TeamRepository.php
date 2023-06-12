<?php

namespace App\Repositories\Contracts;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TeamRepository extends Repository
{
    /**
     * Get the list of the team with pagination and handle filter.
     */
    public function getList(array $filters = [], array $columns = ['*']): LengthAwarePaginator|Collection;

    /**
     * Get the list of the team with pagination and handle filter by company.
     */
    public function getListByCompany(Company $company, array $filters = []): LengthAwarePaginator|Collection;

    /**
     * Find a specified team with roles or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?Team;

    /**
     * Handle create a new team.
     */
    public function create(array $data, User $auth): ?Team;

    /**
     * Handle update the specified team.
     */
    public function update(array $data, Team $team): ?Team;

    /**
     * Handle delete the specified team.
     */
    public function delete(Team $team): ?Team;
}
