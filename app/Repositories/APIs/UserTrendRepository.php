<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\UserTrendRepository as UserTrendRepositoryContract;
use App\Repositories\Repository;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\AI\UserTrendService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class UserTrendRepository extends Repository implements UserTrendRepositoryContract
{
    use HasMqDateTimeHandler;

    public function __construct(
        protected UserTrendService $userTrendService
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return '';
    }

    /**
     * Get data user trends from AI.
     */
    public function getDataChartUserTrends(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        return $this->userTrendService->getListByStore($storeId, $filters);
    }
}