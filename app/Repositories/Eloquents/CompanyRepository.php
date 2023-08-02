<?php

namespace App\Repositories\Eloquents;

use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
        $this->enableUseWith(['teams', 'users'], $filters);

        return parent::getList($filters, $columns);
    }

    /**
     * Find a specified user with roles or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?Company
    {
        $this->enableUseWith(['teams', 'users'], $filters);

        return $this->queryBuilder()->where('id', $id)->first($columns);
    }

    /**
     * Handle create a new company.
     */
    public function create(array $data, User $auth): ?Company
    {
        return $this->handleSafely(function () use ($data, $auth) {
            $company = $this->model();
            $company->fill($data);
            $company->save();

            if (Arr::has($data, 'team_names')) {
                $teams = [];

                foreach (Arr::get($data, 'team_names', []) as $teamName) {
                    $teams[] = [
                        'name' => $teamName,
                        'created_by' => $auth->id,
                    ];
                }

                $company->teams()->createMany($teams);
            }

            $company = $company->withAllRels();

            return $company;
        }, 'Create company');
    }

    /**
     * Handle update the specified company.
     */
    public function update(array $data, Company $company, User $auth): ?Company
    {
        return $this->handleSafely(function () use ($data, $company, $auth) {
            $company->fill($data);
            $company->save();
            $teamNames = collect(Arr::get($data, 'team_names', []));

            $this->syncTeams($company, $teamNames, $auth);

            $company = $company->withAllRels();

            return $company;
        }, 'Update company');
    }

    /**
     * Synchronize the company's teams.
     */
    private function syncTeams(Company $company, $teamNames, User $auth): void
    {
        $teams = $company->teams();

        if ($teamNames->isEmpty()) {
            DB::table('team_user')->whereIn('team_id', $teams->pluck('id'))->delete();

            $teams->delete();
        } else {
            $teamNameCreated = $teamNames->diff($teams->pluck('name'))->all();
            $teamNameDeleted = $teams->pluck('name')->diff($teamNames)->all();

            $this->createTeamsByCompany($company, $teamNameCreated, $auth->id);
            $this->deleteTeamsByCompany($company, $teamNameDeleted);
        }
    }

    /**
     * Create teams by company.
     */
    private function createTeamsByCompany(Company $company, $teamNameCreated, $createdBy): void
    {
        $teamCreated = [];

        foreach ($teamNameCreated as $teamName) {
            $teamCreated[] = [
                'name' => $teamName,
                'created_by' => $createdBy,
            ];
        }

        $company->teams()->createMany($teamCreated);
    }

    /**
     * Delete teams by company.
     */
    private function deleteTeamsByCompany(Company $company, $teamNameDeleted): void
    {
        $teamDeleted = $company->teams()->whereIn('name', $teamNameDeleted);

        DB::table('team_user')->whereIn('team_id', $teamDeleted->pluck('id'))->delete();

        $teamDeleted->delete();
    }

    /**
     * Delete records from the database.
     */
    public function delete(Company $company): ?Company
    {
        $company->teams()->delete();
        $company->delete();

        return $company;
    }

    /**
     * Handle restore a spcified deleted company.
     */
    public function restore($id): ?Company
    {
        $company = $this->model()->onlyTrashed()->where('id', $id)->first();

        if ($company) {
            $company->restore();

            return $company->refresh();
        }

        return null;
    }

    /**
     * Handle force delete a specified company.
     */
    public function forceDelete(Company $company): void
    {
        $company->teams()->delete();
        $company->forceDelete();
    }
}
