<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface MyPageRepository extends Repository
{
    /**
     * Get my page options.
     */
    public function getOptions(): array;

    public function getStoreProfitReference(array $params): Collection;

    public function getStoreProfitTable(array $params): Collection;

    public function getTasks(array $params): Collection;

    public function getAlerts(array $params): Collection;

    public function getSales4QuadrantMap(array $params): Collection;
}
