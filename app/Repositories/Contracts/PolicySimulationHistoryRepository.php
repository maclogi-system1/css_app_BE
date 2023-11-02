<?php

namespace App\Repositories\Contracts;

use App\Models\PolicySimulationHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PolicySimulationHistoryRepository extends Repository
{
    /**
     * Get a list of the policy by store_id.
     */
    public function getListByStore($storeId, array $filters = []): Collection|LengthAwarePaginator;

    /**
     * Handle create a new policy simulation history.
     */
    public function create(array $data): ?PolicySimulationHistory;

    /**
     * Get a specified policy simulation history.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?PolicySimulationHistory;

    /**
     * Generate data to add policies from history.
     */
    public function makeDataPolicy(PolicySimulationHistory $policySimulationHistory): array;
}
