<?php

namespace App\Repositories\Contracts;

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
}
