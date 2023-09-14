<?php

namespace App\Repositories\Contracts;

use App\Models\Policy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PolicyRepository extends Repository
{
    /**
     * Get a list of the policy by store_id.
     */
    public function getListByStore($storeId, array $filters = []): Collection|LengthAwarePaginator;

    /**
     * Get a list of AI recommendations.
     */
    public function getAiRecommendation($storeId, array $filters = []);

    /**
     * Find a specified policy.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?Policy;

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array;

    /**
     * Handle delete the specified policy.
     */
    public function delete(Policy $policy): ?Policy;

    /**
     * Handle data validation to update/create policy.
     */
    public function handleValidation(array $data, int $index): array;

    /**
     * Handle data validation to create simulation policy.
     */
    public function handleValidationSimulationStore(array $data): array;

    /**
     * Get the policy input validation rules.
     */
    public function getValidationRules(array $data): array;

    /**
     * Get the data and parse it into a data structure for job_group.
     */
    public function getDataForJobGroup(array $data): array;

    /**
     * Handle create multiple policy.
     */
    public function create(array $data, string $storeId): ?array;

    /**
     * Handle create a new simulation policy.
     */
    public function createSimulation(array $data, string $storeId): ?Policy;

    /**
     * Handle update a specified policy.
     */
    public function update(array $data, ?Policy $policy): ?bool;

    /**
     * Handle update a specified policy simulation.
     */
    public function updateSimulation(array $data, Policy $policySimulation): ?Policy;

    /**
     * Handle delete multiple policies at the same time.
     */
    public function deleteMultiple(array $policyIds): ?bool;

    /**
     * Run multiple policy simulation.
     */
    public function runMultipleSimulation(array $data);

    /**
     * Run policy simulation.
     */
    public function runSimulation($id);

    /**
     * Get list of work breakdown structure.
     */
    public function workBreakdownStructure(string $storeId, array $filters);
}
