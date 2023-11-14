<?php

namespace App\Repositories\Eloquents;

use App\Models\StandardDeviation;
use App\Repositories\Contracts\StandardDeviationRepository as StandardDeviationRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\CategoryAnalysisService;
use App\WebServices\AI\PolicyR2Service;
use App\WebServices\AI\ProductAnalysisService;
use App\WebServices\AI\RgroupAdService;
use App\WebServices\AI\RppAdService;

class StandardDeviationRepository extends Repository implements StandardDeviationRepositoryContract
{
    public function __construct(
        private CategoryAnalysisService $categoryAnalysisService,
        private ProductAnalysisService $productAnalysisService,
        private PolicyR2Service $policyR2Service,
        private RppAdService $rppAdService,
        private RgroupAdService $rgroupAdService,
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return StandardDeviation::class;
    }

    /**
     * Get the first record matching the attributes. If the record is not found, create it.
     */
    public function firstOrCreate(array $data): ?StandardDeviation
    {
        $date = Arr::get($data, 'date');
        $itemsSales = $this->productAnalysisService->getProductAccessNumAndConversionRate(['current_date' => $date]);
        $standardDeviation = $this->model()->firstOrCreate(
            [
                'date' => $date,
            ],
            [
                'number_of_categories' => $this->getNumberOfCategories($date),
                'number_of_items' => $this->getNumberOfItems($date),
                'product_utilization_rate' => $this->getProductUtilizationRate($date),

                'product_page_conversion_rate' => $this->getProductPageConversionRate($itemsSales),
                'access_number' => $this->getAccessNumber($itemsSales),

                'event_sales_ratio' => $this->getEventSalesRatio($date),
                'sales_ratio_day_endings_0_5' => $this->getEventSalesRatio($date, true),
                'coupon_effect' => null,

                'rpp_ad' => $this->getRppAd($date),
                'coupon_advance' => null,
                'rgroup_ad' => $this->getRgroupAd($date),
                'tda_ad' => null,
                'sns_ad' => null,
                'google_access' => null,
                'instagram_access' => null,
            ]
        );

        return $standardDeviation->refresh();
    }

    public function getNumberOfCategories(string $date)
    {
        $totalCatategoryOfStores = $this->categoryAnalysisService->getTotalCategoryOfStores(['current_date' => $date]);
        $totalShop = $totalCatategoryOfStores->count();

        if (! $totalShop) {
            return null;
        }

        $average = $totalCatategoryOfStores->reduce(
            fn (?int $carry, $item) => $carry + $item->total_cate,
            0
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($totalCatategoryOfStores as $item) {
            $standardDeviation += pow($item->total_cate - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getNumberOfItems(string $date)
    {
        $totalProductOfStores = $this->productAnalysisService->getTotalProductOfStores(['current_date' => $date]);
        $totalShop = $totalProductOfStores->count();

        if (! $totalShop) {
            return null;
        }

        $average = $totalProductOfStores->reduce(
            fn (?int $carry, $item) => $carry + $item->total_prod,
            0
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($totalProductOfStores as $item) {
            $standardDeviation += pow($item->total_prod - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getProductUtilizationRate(string $date)
    {
        $utilizationRateOfStore = $this->productAnalysisService->getUtilizationRate(['current_date' => $date]);
        $totalShop = $utilizationRateOfStore->count();

        if (! $totalShop) {
            return null;
        }

        $average = $utilizationRateOfStore->reduce(
            fn (?int $carry, $item) => $carry + $item->utilization_rate,
            0,
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($utilizationRateOfStore as $item) {
            $standardDeviation += pow($item->utilization_rate - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getProductPageConversionRate(Collection $itemsSales)
    {
        $totalShop = $itemsSales->count();

        if (! $totalShop) {
            return null;
        }

        $average = $itemsSales->reduce(
            fn (?int $carry, $item) => $carry + $item->conversion_rate,
            0,
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($itemsSales as $item) {
            $standardDeviation += pow($item->conversion_rate - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getAccessNumber(Collection $itemsSales)
    {
        $totalShop = $itemsSales->count();

        if (! $totalShop) {
            return null;
        }

        $average = $itemsSales->reduce(
            fn (?int $carry, $item) => $carry + $item->access_num,
            0,
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($itemsSales as $item) {
            $standardDeviation += pow($item->access_num - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getEventSalesRatio(string $date, bool $endings05 = false)
    {
        $salesAmnt = $this->policyR2Service->getListEventTimePeriods([
            'current_date' => $date,
            'endings_0_5' => $endings05,
        ]);

        $totalShop = count($salesAmnt);

        if (! $totalShop) {
            return null;
        }

        $average = array_reduce($salesAmnt, function ($carry, $item) {
            return $carry + $item;
        }, 0) / $totalShop;
        $standardDeviation = 0;

        foreach ($salesAmnt as $value) {
            $standardDeviation += pow($value - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getRppAd(string $date)
    {
        $rppAdTotal = $this->rppAdService->getRppAdTotal(['current_date' => $date]);
        $totalShop = $rppAdTotal->count();

        if (! $totalShop) {
            return null;
        }

        $average = $rppAdTotal->reduce(function ($carry, $item) {
            return $carry + $item->rpp_ad_total;
        }, 0) / $totalShop;
        $standardDeviation = 0;

        foreach ($rppAdTotal as $item) {
            $standardDeviation += pow($item->rpp_ad_total - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getRgroupAd(string $date)
    {
        $rgroupAdTotal = $this->rgroupAdService->getRgroupAdTotal(['current_date' => $date]);
        $totalShop = $rgroupAdTotal->count();

        if (! $totalShop) {
            return null;
        }

        $average = $rgroupAdTotal->reduce(function ($carry, $item) {
            return $carry + $item->rgroup_ad_total;
        }, 0) / $totalShop;
        $standardDeviation = 0;

        foreach ($rgroupAdTotal as $item) {
            $standardDeviation += pow($item->rgroup_ad_total - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getTdaAd(string $date)
    {
        // code...
    }
}
