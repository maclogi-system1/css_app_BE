<?php

namespace App\Repositories\Contracts;

use App\Models\ValueChain;
use Illuminate\Support\Collection;

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
     * Format the value chain detail.
     */
    public function formatDetail(ValueChain $valueChain): array;

    /**
     * Handle create a new value chain.
     */
    public function create(array $data): ?ValueChain;

    /**
     * Handle update a specified value chain.
     */
    public function update(array $data, ValueChain $valueChain): ?ValueChain;

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array;

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

    /**
     * Check and supplement data for empty months.
     */
    public function checkAndSupplementData(Collection $valueChains, array $filters): array;
}
