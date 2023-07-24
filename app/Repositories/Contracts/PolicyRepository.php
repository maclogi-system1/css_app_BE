<?php

namespace App\Repositories\Contracts;

use App\Models\Policy;
use Illuminate\Support\Collection;

interface PolicyRepository extends Repository
{
    /**
     * Get a list of the policy by store_id.
     */
    public function getListByStore($storeId, array $filters = []): Collection;

    /**
     * Get a list of AI recommendations.
     */
    public function getAiRecommendation($storeId, array $filters = []);

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
     * Handle create multiple policy.
     */
    public function create(array $data, string $storeId): ?Policy;
}
