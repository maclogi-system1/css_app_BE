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

        $data = collect($this->userTrendService->getListByStore($storeId, $filters));

        // Get compared data user trends
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->userTrendService->getListByStore($storeId, $filters));
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => ['chart_user_trends' => $data],
        ]);
    }
}
