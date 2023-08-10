<?php

namespace App\Repositories\Eloquents;

use App\Models\PolicySimulationHistory;
use App\Repositories\Contracts\PolicySimulationHistoryRepository as PolicySimulationHistoryRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class PolicySimulationHistoryRepository extends Repository implements PolicySimulationHistoryRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return PolicySimulationHistory::class;
    }

    /**
     * Get a list of the policy by store_id.
     */
    public function getListByStore($storeId, array $filters = []): Collection|LengthAwarePaginator
    {
        $page = Arr::get($filters, 'page', 1);
        $perPage = Arr::get($filters, 'per_page', 10);

        $query = $this->model()->join('policies as p', 'p.id', '=', 'policy_simulation_histories.policy_id')
            ->where('p.store_id', $storeId)
            ->select('policy_simulation_histories.*', 'p.name');

        if ($perPage < 0) {
            return $query->get();
        }

        return $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
    }

    /**
     * Handle create a new policy simulation history.
     */
    public function create(array $data): ?PolicySimulationHistory
    {
        $policySimulationHistory = $this->model()->fill($data);
        $policySimulationHistory->save();

        return $policySimulationHistory;
    }
}
