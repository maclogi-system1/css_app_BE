<?php

namespace App\Repositories\Contracts;

use App\Models\ValueChain;

interface ValueChainRepository extends Repository
{
    /**
     * Get the list of value chains by store.
     */
    public function getListByStore(string $storeId, array $filters = []);

    /**
     * Get value chain detail by store.
     */
    public function getDetailByStore(string $storeId, array $filters = []): ?ValueChain;

    /**
     * Handle create a new value chain.
     */
    public function create(array $data): ?ValueChain;

    /**
     * Handles creating default value chains.
     */
    public function handleCreateDefault(string $storeId, array $filters = []);

    /**
     * Get data for monthly evaluation.
     */
    public function monthlyEvaluation(string $storeId, array $filters = []);

    /**
     * Get the list of monthly evaluation scores for the chart.
     */
    public function chartEvaluate(string $storeId, array $filters = []);
}
