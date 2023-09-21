<?php

namespace App\Repositories\APIs;

use App\Repositories\Contracts\AdsAnalysisRepository as AdsAnalysisRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\AdsAnalysisService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AdsAnalysisRepository extends Repository implements AdsAnalysisRepositoryContract
{
    public function __construct(
        protected AdsAnalysisService $adsAnalysisService,
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
     * Get data ads analysis summary from AI.
     */
    public function getAdsAnalysisSummary(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }
        $result = $this->adsAnalysisService->getAdsAnalysisSummary($storeId, $filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->adsAnalysisService->getAdsAnalysisSummary($storeId, $filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get data Chart and Table Ads conversion from AI.
     */
    public function getListAdsConversion(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }
        $result = $this->adsAnalysisService->getListAdsConversion($storeId, $filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->adsAnalysisService->getListAdsConversion($storeId, $filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get list highest and lowest product by ROAS from AI.
     */
    public function getListProductByRoas(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }
        $result = $this->adsAnalysisService->getListProductByRoas($storeId, $filters);
        $data = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data = $data->merge($this->adsAnalysisService->getListProductByRoas($storeId, $filters)->get('data'));
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }

    /**
     * Get data chart sales and access impact from AI.
     */
    public function getDataChartSalesAndAccess(string $storeId, array $filters = []): Collection
    {
        if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
            $filters['to_date'] = now()->format('Y-m');
        }
        $result = $this->adsAnalysisService->getDataChartSalesAndAccess($storeId, $filters);
        $data[] = $result->get('data');

        // Get compared data category analysis
        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m');
            }

            $data[] = $this->adsAnalysisService->getDataChartSalesAndAccess($storeId, $filters)->get('data');
        }

        return collect([
            'data' => $data,
            'status' => $result->get('status'),
        ]);
    }
}
