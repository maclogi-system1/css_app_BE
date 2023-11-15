<?php

namespace App\Repositories\Eloquents;

use App\Models\StandardDeviation;
use App\Repositories\Contracts\StandardDeviationRepository as StandardDeviationRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\AccessSourceService;
use App\WebServices\AI\AdPurchaseHistoryService;
use App\WebServices\AI\CategoryAnalysisService;
use App\WebServices\AI\MqAccountingService;
use App\WebServices\AI\PolicyR2Service;
use App\WebServices\AI\ProductAnalysisService;
use App\WebServices\AI\RgroupAdService;
use App\WebServices\AI\RppAdService;
use App\WebServices\AI\TdaAdService;
use Illuminate\Support\Arr;

class StandardDeviationRepository extends Repository implements StandardDeviationRepositoryContract
{
    public function __construct(
        private CategoryAnalysisService $categoryAnalysisService,
        private ProductAnalysisService $productAnalysisService,
        private PolicyR2Service $policyR2Service,
        private RppAdService $rppAdService,
        private RgroupAdService $rgroupAdService,
        private TdaAdService $tdaAdService,
        private AdPurchaseHistoryService $adPurchaseHistoryService,
        private AccessSourceService $accessSourceService,
        private MqAccountingService $mqAccountingService,
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
        $googleAndInstagramAccessNum = $this->accessSourceService->getTotalAccessGoogleAndInstagram(['current_date' => $date]);
        $standardDeviation = $this->model()->firstOrCreate(
            [
                'date' => $date.'-01',
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
                'coupon_advance' => $this->getCouponAdvanceAd($date),
                'rgroup_ad' => $this->getRgroupAd($date),
                'tda_ad' => $this->getTdaAd($date),
                'sns_ad' => null,
                'google_access' => $this->getGoogleAccess($googleAndInstagramAccessNum),
                'instagram_access' => $this->getInstagramAccess($googleAndInstagramAccessNum),

                'shipping_fee' => null,
                'shipping_ratio' => null,
                'bundling_ratio' => null,

                'email_newsletter' => null,
                're_sales_num_rate' => $this->getReSalesNumRate($date),
                'line_official' => null,
                'ltv' => $this->getLtv2yAmnt($date),
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

    public function getProductPageConversionRate($itemsSales)
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

    public function getAccessNumber($itemsSales)
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
        $rppAdTotal = $this->rppAdService->getTotalRppAd(['current_date' => $date]);
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
        $rgroupAdTotal = $this->rgroupAdService->getTotalRgroupAd(['current_date' => $date]);
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
        $tdaAdTotal = $this->tdaAdService->getTotalTdaAd(['current_date' => $date]);
        $totalShop = $tdaAdTotal->count();

        if (! $totalShop) {
            return null;
        }

        $average = $tdaAdTotal->reduce(function ($carry, $item) {
            return $carry + $item->tda_ad_total;
        }, 0) / $totalShop;
        $standardDeviation = 0;

        foreach ($tdaAdTotal as $item) {
            $standardDeviation += pow($item->tda_ad_total - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getCouponAdvanceAd(string $date)
    {
        $couponAdvanceAdTotal = $this->adPurchaseHistoryService->getTotalCouponAdvanceAd(['current_date' => $date]);
        $totalShop = $couponAdvanceAdTotal->count();

        if (! $totalShop) {
            return null;
        }

        $average = $couponAdvanceAdTotal->reduce(function ($carry, $item) {
            return $carry + $item->coupon_advance_ad;
        }, 0) / $totalShop;
        $standardDeviation = 0;

        foreach ($couponAdvanceAdTotal as $item) {
            $standardDeviation += pow($item->coupon_advance_ad - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getGoogleAccess($googleAndInstagramAccessNum)
    {
        $totalShop = $googleAndInstagramAccessNum->count();

        if (! $totalShop) {
            return null;
        }

        $average = $googleAndInstagramAccessNum->reduce(
            fn (?int $carry, $item) => $carry + $item->google,
            0,
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($googleAndInstagramAccessNum as $item) {
            $standardDeviation += pow($item->google - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getInstagramAccess($googleAndInstagramAccessNum)
    {
        $totalShop = $googleAndInstagramAccessNum->count();

        if (! $totalShop) {
            return null;
        }

        $average = $googleAndInstagramAccessNum->reduce(
            fn (?int $carry, $item) => $carry + $item->instagram,
            0,
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($googleAndInstagramAccessNum as $item) {
            $standardDeviation += pow($item->instagram - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getReSalesNumRate(string $date)
    {
        $mqAccounting = $this->mqAccountingService->getListReSalesNum([
            'year_month' => $date,
        ]);
        $totalShop = $mqAccounting->count();

        if (! $totalShop) {
            return 0;
        }

        $average = $mqAccounting->reduce(
            fn (?int $carry, $item) => $carry + $item->re_sales_num_rate,
            0,
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($mqAccounting as $item) {
            $standardDeviation += pow($item->re_sales_num_rate - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }

    public function getLtv2yAmnt(string $date)
    {
        $result = $this->mqAccountingService->getList([
            'year_month' => $date,
        ]);
        $mqAccounting = collect();

        if ($result->get('success')) {
            $mqAccounting = $result->get('data');
        }

        $totalShop = $mqAccounting->count();

        if (! $totalShop) {
            return 0;
        }

        $average = $mqAccounting->reduce(
            fn (?int $carry, $item) => $carry + $item->mqCost->ltv_2y_amnt,
            0,
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($mqAccounting as $item) {
            $standardDeviation += pow($item->mqCost->ltv_2y_amnt - $average, 2);
        }

        return sqrt($standardDeviation / $totalShop);
    }
}
