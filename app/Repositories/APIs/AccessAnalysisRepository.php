<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\AccessAnalysisRepository as AccessAnalysisRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\AccessAnalysisService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AccessAnalysisRepository extends Repository implements AccessAnalysisRepositoryContract
{
    public function __construct(
        private  AccessAnalysisService $accessAnalysisService,
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
     * Get detail data table access analysis from AI.
     */
    public function getDataTableAccessAnalysis(string $storeId, array $filters = []): Collection
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
        $result = $this->accessAnalysisService->getDataTableAccessAnalysis($storeId, $filters, $isMonthQuery);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->accessAnalysisService->getDataTableAccessAnalysis($storeId, $filters, $isMonthQuery)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get chart data new user access for access analysis screen from AI.
     */
    public function getDataChartNewUserAccess(string $storeId, array $filters = []): Collection
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

        $data = collect($this->accessAnalysisService->getDataChartNewUserAccess($storeId, $filters, $isMonthQuery));

        // Get compared data new user access for access analysis screen
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->accessAnalysisService->getDataChartNewUserAccess($storeId, $filters, $isMonthQuery));
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => ['chart_new_user_access' => $data],
        ]);
    }

    /**
     * Get chart data exist user access for access analysis screen from AI.
     */
    public function getDataChartExistUserAccess(string $storeId, array $filters = []): Collection
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

        $data = collect($this->accessAnalysisService->getDataChartExistUserAccess($storeId, $filters, $isMonthQuery));

        // Get compared data exist user access for access analysis screen
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->accessAnalysisService->getDataChartExistUserAccess($storeId, $filters, $isMonthQuery));
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => ['chart_exist_user_access' => $data],
        ]);
    }
}
