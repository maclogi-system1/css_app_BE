<?php

namespace App\Repositories\Eloquents;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use App\Repositories\Contracts\TeamRepository as TeamRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class TeamRepository extends Repository implements TeamRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return Team::class;
    }

    /**
     * Get the list of the team with pagination and handle filter.
     */
    public function getList(array $filters = [], array $columns = ['*']): LengthAwarePaginator|Collection
    {
        $this->enableUseWith(['company', 'users', 'owner'], $filters);

        return parent::getList($filters, $columns);
    }

    /**
     * Get the list of the team with pagination and handle filter by company.
     */
    public function getListByCompany(Company $company, array $filters = []): LengthAwarePaginator|Collection
    {
        $filters['filter']['company_id'] = $company->id;
        $this->useWith(['company']);

        return $this->getList($filters);
    }

    /**
     * Find a specified team with roles or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?Team
    {
        $this->enableUseWith(['company', 'users', 'owner'], $filters);

        return $this->queryBuilder()->where('id', $id)->first($columns);
    }

    /**
     * Handle create a new team.
     */
    public function create(array $data, User $auth): ?Team
    {
        return $this->handleSafely(function () use ($data, $auth) {
            $team = $this->model()->fill($data);
            $team->created_by = $auth->id;
            $team->save();

            // Add users to team.
            if (Arr::has($data, 'users')) {
                $team->users()->sync($data['users']);
            }

            return $team;
        }, 'Create team');
    }

    /**
     * Handle update the specified team.
     */
    public function update(array $data, Team $team): ?Team
    {
        return $this->handleSafely(function () use ($data, $team) {
            $team->fill($data);
            $team->save();

            if (Arr::has($data, 'users')) {
                $team->users()->sync($data['users']);
            }

            return $team->refresh();
        }, 'Update team');
    }

    /**
     * Handle delete the specified team.
     */
    public function delete(Team $team): ?Team
    {
        $team->users()->detach();
        $team->delete();

        return $team;
    }

    public function getTeamsWithIds(array $teamIds): Collection
    {
        return $this->model()->newQuery()->with(['users'])->whereIn('id', $teamIds)->get();
    }

    public function getTeamUserIdsWithTeamIds(array $teamIds): array
    {
        $userIds = [];
        $this->getTeamsWithIds($teamIds)->each(function (Team $team) use (&$userIds) {
            $userIds = array_merge($userIds, $team->users->pluck('id')->toArray());
        });

        return $userIds;
    }
}
