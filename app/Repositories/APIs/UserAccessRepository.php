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
        $actualUserAccess = $this->accessSourceService->getTotalAccess($storeId, $filters);
        $actualUserAccessNum = 0;

        if ($actualUserAccess->get('success')) {
            $actualUserAccessNum = $actualUserAccess->get('data')->get('total_access');
        }

        $now = now();
        $mqSheetId = Arr::get($filters, 'mq_sheet_id');
        $expectedMqAccessNum = $this->mqAccountingRepository->model()
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->where('store_id', $storeId)
            ->where('mq_sheet_id', $mqSheetId)
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

        $result = $this->userAccessService->getListUserAccess($storeId, $filters);
        $realData = $result->get('data');
        $expectedData = $this->getExpectedAccessData($storeId, $filters);
        $data = $this->buildUserAccessData($realData, $expectedData);

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $comparedRealData = $this->userAccessService->getListUserAccess($storeId, $filters)->get('data');
            $comparedExpectedData = $this->getExpectedAccessData($storeId, $filters);

            $data = $data->merge($this->buildUserAccessData($comparedRealData, $comparedExpectedData));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get data user access with ads and none ads from AI.
     */
    public function getDataChartUserAccessAds(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }
        $result = $this->userAccessService->getListUserAccessAds($storeId, $filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->userAccessService->getListUserAccessAds($storeId, $filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get chart data access source from AI.
     */
    public function getDataChartAccessSource(string $storeId, array $filters = []): Collection
    {
        // Check if the input matches the 'yyyy-MM' format
        $isMonthQuery = false;
        if (Arr::has($filters, ['from_date', 'to_date'])) {
            if (
                preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'from_date'))
                && preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'to_date'))
            ) {
                $isMonthQuery = true;
            }
        }

        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }
        $result = $this->accessSourceService->getListAccessSource($storeId, $filters, $isMonthQuery);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->accessSourceService->getListAccessSource($storeId, $filters, $isMonthQuery)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get table data access source from AI.
     */
    public function getDataTableAccessSource(string $storeId, array $filters = []): Collection
    {
        // Check if the input matches the 'yyyy-MM' format
        $isMonthQuery = false;
        if (Arr::has($filters, ['from_date', 'to_date'])) {
            if (
                preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'from_date'))
                && preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'to_date'))
            ) {
                $isMonthQuery = true;
            }
        }
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }

        return $this->accessSourceService->getTableAccessSource($storeId, $filters, $isMonthQuery);
    }

    /**
     * Get expected data from CSS.
     */
    private function getExpectedAccessData($storeId, $filters)
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $mqSheetId = Arr::get($filters, 'mq_sheet_id');

        $expectedData = $this->mqAccountingRepository->model()
            ->where('store_id', $storeId)
            ->where('year', '>=', $dateRangeFilter['from_date']->year)
            ->where('month', '>=', $dateRangeFilter['from_date']->month)
            ->where('year', '<=', $dateRangeFilter['to_date']->year)
            ->where('month', '<=', $dateRangeFilter['to_date']->month)
            ->where('mq_sheet_id', $mqSheetId)
            ->join('mq_access_num as ma', 'ma.id', '=', 'mq_accounting.mq_access_num_id')
            ->selectRaw('
                store_id,
                CONCAT(year,"/",LPAD(month, 2, "0")) as date,
                ma.access_flow_sum
            ')
            ->get()->toArray();

        return $expectedData;
    }

    /**
     * Build response data with access rate compared from plan and actual data.
     */
    private function buildUserAccessData($realData, $expectedData)
    {
        $data = collect();
        foreach ($realData as $realDataItem) {
            $userAccessRate = 0;
            $expectedItem = Arr::where($expectedData, function ($item) use ($realDataItem) {
                return Arr::get($item, 'date') == Arr::get($realDataItem, 'date');
            });
            if (
                ! is_null($expectedItem)
                && count($expectedItem) > 0
            ) {
                $realAccessAmount = Arr::get($realDataItem, 'access_flow_sum') ?? 0;
                $expectedAccessAmount = Arr::get($expectedItem[0], 'access_flow_sum') ?? 0;
                if ($expectedAccessAmount > 0) {
                    $userAccessRate = round($realAccessAmount / $expectedAccessAmount, 1) * 100;
                }
            }
            $data->add([
                'store_id' => Arr::get($realDataItem, 'store_id'),
                'date' => Arr::get($realDataItem, 'date'),
                'user_access_num' => Arr::get($realDataItem, 'access_flow_sum'),
                'user_access_rate' => $userAccessRate,
            ]);
        }

        return $data;
    }
}
