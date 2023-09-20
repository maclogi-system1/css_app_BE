<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\UserAccessRepository as UserAccessRepositoryContract;
use App\Repositories\Repository;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\AI\AccessSourceService;
use App\WebServices\AI\UserAccessService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class UserAccessRepository extends Repository implements UserAccessRepositoryContract
{
    use HasMqDateTimeHandler;

    public function __construct(
        protected AccessSourceService $accessSourceService,
        protected MqAccountingRepository $mqAccountingRepository,
        protected UserAccessService $userAccessService,
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return '';
    }

    public function getTotalUserAccess(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $actualUserAccess = $this->accessSourceService->getTotalAccess($storeId, $filters);
        $actualUserAccessNum = 0;

        if ($actualUserAccess->get('success')) {
            $actualUserAccessNum = $actualUserAccess->get('data')->get('total_access');
        }

        $now = now();
        $expectedMqAccessNum = $this->mqAccountingRepository->model()
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->where('store_id', $storeId)
            ->join('mq_access_num as ma', 'ma.id', '=', 'mq_accounting.mq_access_num_id')
            ->selectRaw('SUM(ma.access_flow_sum) as access_flow_sum')
            ->first();

        return collect([
            'actual_user_access_num' => $actualUserAccessNum,
            'expected_user_access_num' => $expectedMqAccessNum?->access_flow_sum ?? 0,
        ]);
    }

    /**
     * Get data user access from AI.
     */
    public function getDataChartUserAccess(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }
        $result = $this->userAccessService->getListUserAccess($storeId, $filters)->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $result = $result->merge($this->userAccessService->getListUserAccess($storeId, $filters)->get('data'));
        }

        return $result;
    }

    /**
     * Get data user access with ads and none ads from AI.
     */
    public function getDataChartUserAccessAds(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }
        $result = $this->userAccessService->getListUserAccessAds($storeId, $filters)->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $result = $result->merge($this->userAccessService->getListUserAccessAds($storeId, $filters)->get('data'));
        }

        return $result;
    }

    /**
     * Get chart data access source from AI.
     */
    public function getDataChartAccessSource(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }
        $result = $this->accessSourceService->getListAccessSource($storeId, $filters)->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $result = $result->merge($this->accessSourceService->getListAccessSource($storeId, $filters)->get('data'));
        }

        return $result;
    }

    /**
     * Get table data access source from AI.
     */
    public function getDataTableAccessSource(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        return $this->accessSourceService->getTableAccessSource($storeId, $filters);
    }
}
