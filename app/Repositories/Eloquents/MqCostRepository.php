<?php

namespace App\Repositories\Eloquents;

use App\Models\MqCost;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\MqCostRepository as MqCostRepositoryContract;
use App\Repositories\Repository;
use App\Support\Traits\HasMqDateTimeHandler;
use Illuminate\Support\Arr;

class MqCostRepository extends Repository implements MqCostRepositoryContract
{
    use HasMqDateTimeHandler;

    public function __construct(
        protected MqAccountingRepository $mqAccountingRepository
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MqCost::class;
    }

    /**
     * Get ad cost from mq_cost by store_id.
     */
    public function getAdCostByStore(string $storeId, array $filters = []): int
    {
        if (! Arr::hasAny($filters, ['from_date', 'end_date'])) {
            return 0;
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $mqCost = $this->mqAccountingRepository->model()
            ->dateRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date'])
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->selectRaw('
                SUM(mc.ad_cost) as ad_cost
            ')
            ->first();

        return $mqCost?->ad_cost ?? 0;
    }
}
