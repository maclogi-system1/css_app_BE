<?php

namespace App\Repositories\Eloquents;

use App\Models\StandardDeviation;
use App\Models\ValueChain;
use App\Repositories\Contracts\StandardDeviationRepository;
use App\Repositories\Contracts\ValueChainRepository as ValueChainRepositoryContract;
use App\Repositories\Repository;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\AI\CategoryAnalysisService;
use App\WebServices\AI\MqAccountingService;
use App\WebServices\AI\PolicyR2Service;
use App\WebServices\AI\ProductAnalysisService;
use App\WebServices\OSS\ShopService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ValueChainRepository extends Repository implements ValueChainRepositoryContract
{
    use HasMqDateTimeHandler;

    public function __construct(
        protected CategoryAnalysisService $categoryAnalysisService,
        protected ProductAnalysisService $productAnalysisService,
        protected MqAccountingService $mqAccountingService,
        protected ShopService $shopService,
        protected PolicyR2Service $policyR2Service,
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return ValueChain::class;
    }

    /**
     * Get the list of value chains by store.
     */
    public function getListByStore(string $storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        return $this->model()->where('store_id', $storeId)
            ->whereDate('date', '>=', $dateRangeFilter['from_date']->format('Y-m-d'))
            ->whereDate('date', '<=', $dateRangeFilter['to_date']->format('Y-m-d'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Get value chain detail by store.
     */
    public function getDetailByStore(string $storeId, array $filters = []): ?ValueChain
    {
        $currentDate = Arr::get($filters, 'current_date', now()->format('Y-m'));

        return $this->model()->where('store_id', $storeId)
            ->whereDate('date', $currentDate.'-01')
            ->first();
    }

    /**
     * Handle create a new value chain.
     */
    public function create(array $data): ?ValueChain
    {
        $valueChain = $this->model()->fill($data);
        $valueChain->save();

        return $valueChain->refresh();
    }

    /**
     * Get data for monthly evaluation.
     */
    public function monthlyEvaluation(string $storeId, array $filters = [])
    {
        $valueChain = $this->getDetailByStore($storeId, $filters);

        if (! $valueChain) {
            $valueChain = $this->handleCreateDefault($storeId, $filters);
        }

        return [
            'merchandise' => [
                'number_of_categories_point' => $valueChain->number_of_categories_point,
                'number_of_items_point' => $valueChain->number_of_items_point,
                'product_utilization_rate_point' => $valueChain->product_utilization_rate_point,
                'product_cost_rate_point' => $valueChain->product_cost_rate_point,
                'low_product_reviews_point' => $valueChain->low_product_reviews_point,
                'few_sold_out_items_point' => $valueChain->few_sold_out_items_point,
                'average' => round((
                    $valueChain->number_of_categories_point
                    + $valueChain->number_of_items_point
                    + $valueChain->product_utilization_rate_point
                    + $valueChain->product_cost_rate_point
                    + $valueChain->low_product_reviews_point
                    + $valueChain->few_sold_out_items_point
                ) / 6, 2),
            ],
            'purchase' => [
                'purchase_form_point' => $valueChain->purchase_form_point,
                'stock_value_point' => $valueChain->stock_value_point,
                'product_utilization_rate_point' => $valueChain->product_utilization_rate_point,
                'average' => round((
                    $valueChain->purchase_form_point
                    + $valueChain->stock_value_point
                    + $valueChain->product_utilization_rate_point
                ) / 3, 2),
            ],
            'construction_production' => [
                'top_page_point' => $valueChain->top_page_point,
                'category_page_point' => $valueChain->category_page_point,
                'header_point' => $valueChain->header_point,
                'product_page_point' => $valueChain->product_page_point,
                'product_page_conversion_rate' => $valueChain->product_page_conversion_rate_point,
                'product_thumbnail_point' => $valueChain->product_thumbnail_point,
                'access_number_point' => $valueChain->access_number_point,
                'featured_products_point' => $valueChain->featured_products_point,
                'left_navigation_point' => $valueChain->left_navigation_point,
                'header_large_banner_small_banner_point' => $valueChain->header_large_banner_small_banner_point,
                'average' => round((
                    $valueChain->top_page_point
                    + $valueChain->category_page_point
                    + $valueChain->header_point
                    + $valueChain->product_page_point
                    + $valueChain->product_page_conversion_rate_point
                    + $valueChain->product_thumbnail_point
                    + $valueChain->access_number_point
                    + $valueChain->featured_products_point
                    + $valueChain->left_navigation_point
                    + $valueChain->header_large_banner_small_banner_point
                ) / 10, 2),
            ],
            'event_sale' => [
                'event_sales_ratio_point' => $valueChain->event_sales_ratio_point,
                'sales_ratio_day_endings_0_5' => $valueChain->sales_ratio_day_endings_0_5_point,
                'implementation_of_measures_point' => $valueChain->implementation_of_measures_point,
                'coupon_effect_point' => $valueChain->coupon_effect_point,
                'average' => round((
                    $valueChain->event_sales_ratio_point
                    + $valueChain->sales_ratio_day_endings_0_5_point
                    + $valueChain->implementation_of_measures_point
                    + $valueChain->coupon_effect_point
                ) / 4, 2),
            ],
            'advertisement' => [
                'rpp_ad_point' => $valueChain->rpp_ad_point,
                'rpp_ad_operation' => $valueChain->rpp_ad_operation_point,
                'coupon_advance_point' => $valueChain->coupon_advance_point,
                'rgroup_ad_point' => $valueChain->rgroup_ad_point,
                'tda_ad_point' => $valueChain->tda_ad_point,
                'sns_ad_point' => $valueChain->sns_ad_point,
                'google_access_point' => $valueChain->google_access_point,
                'instagram_access_point' => $valueChain->instagram_access_point,
                'average' => round((
                    $valueChain->rpp_ad_point
                    + $valueChain->rpp_ad_operation_point
                    + $valueChain->coupon_advance_point
                    + $valueChain->rgroup_ad_point
                    + $valueChain->tda_ad_point
                    + $valueChain->sns_ad_point
                    + $valueChain->google_access_point
                    + $valueChain->instagram_access_point
                ) / 8, 2),
            ],
            'logistics' => [
                'compatible_point' => $valueChain->compatible_point,
                'shipping_fee_point' => $valueChain->shipping_fee_point,
                'shipping_ratio_point' => $valueChain->shipping_ratio_point,
                'mail_service_point' => $valueChain->mail_service_point,
                'bundling_ratio_point' => $valueChain->bundling_ratio_point,
                'gift_available_point' => $valueChain->gift_available_point,
                'delivery_on_specified_day_point' => $valueChain->delivery_on_specified_day_point,
                'delivery_preparation_period_point' => $valueChain->delivery_preparation_period_point,
                'shipping_on_the_specified_date_point' => $valueChain->shipping_on_the_specified_date_point,
                'shipping_according_to_the_delivery_date_point' => $valueChain->shipping_according_to_the_delivery_date_point,
                'average' => round((
                    $valueChain->compatible_point
                    + $valueChain->shipping_fee_point
                    + $valueChain->shipping_ratio_point
                    + $valueChain->mail_service_point
                    + $valueChain->bundling_ratio_point
                    + $valueChain->gift_available_point
                    + $valueChain->delivery_on_specified_day_point
                    + $valueChain->delivery_preparation_period_point
                    + $valueChain->shipping_on_the_specified_date_point
                    + $valueChain->shipping_according_to_the_delivery_date_point
                ) / 10, 2),
            ],
            'orders' => [
                'system_introduction' => $valueChain->system_introduction_point,
                'order_through_rate_point' => $valueChain->order_through_rate_point,
                'number_of_people_in_charge_of_ordering_point' => $valueChain->number_of_people_in_charge_of_ordering_point,
                'average' => round((
                    $valueChain->system_introduction_point
                    + $valueChain->order_through_rate_point
                    + $valueChain->number_of_people_in_charge_of_ordering_point
                ) / 3, 2),
            ],
            'customer_service' => [
                'thank_you_email_point' => $valueChain->thank_you_email_point,
                'what_s_included_point' => $valueChain->what_s_included_point,
                'follow_email_point' => $valueChain->follow_email_point,
                'order_email_point' => $valueChain->order_email_point,
                'shipping_email_point' => $valueChain->shipping_email_point,
                'few_user_complaints_point' => $valueChain->few_user_complaints_point,
                'average' => round((
                    $valueChain->thank_you_email_point
                    + $valueChain->what_s_included_point
                    + $valueChain->follow_email_point
                    + $valueChain->order_email_point
                    + $valueChain->shipping_email_point
                    + $valueChain->few_user_complaints_point
                ) / 6, 2),
            ],
            'crm' => [
                'email_newsletter_point' => $valueChain->email_newsletter_point,
                're_sales_num_rate_point' => $valueChain->re_sales_num_rate_point,
                'review_writing_rate_point' => $valueChain->review_writing_rate_point,
                'review_measures_point' => $valueChain->review_measures_point,
                'line_official' => $valueChain->line_official_point,
                'instagram_followers_point' => $valueChain->instagram_followers_point,
                'ltv_point' => $valueChain->ltv_point,
                'average' => round((
                    $valueChain->email_newsletter_point
                    + $valueChain->re_sales_num_rate_point
                    + $valueChain->review_writing_rate_point
                    + $valueChain->review_measures_point
                    + $valueChain->line_official_point
                    + $valueChain->instagram_followers_point
                    + $valueChain->ltv_point
                ) / 7, 2),
            ],
        ];
    }

    /**
     * Handles creating default value chains.
     */
    public function handleCreateDefault(string $storeId, array $filters = [])
    {
        $date = Carbon::create(Arr::get($filters, 'current_date', now()->subMonth()->format('Y-m')));

        if ($date->equalTo(now())) {
            return;
        }

        $filters['store_id'] = $storeId;
        $itemsSales = $this->productAnalysisService->getProductAccessNumAndConversionRate($filters)->first();

        /** @var \App\Repositories\Contracts\StandardDeviationRepository */
        $standardDeviationRepository = app(StandardDeviationRepository::class);
        $standardDeviation = $standardDeviationRepository->firstOrCreate([
            'date' => $date->format('Y-m'),
        ]);

        $valueChain = $this->model()->firstOrCreate([
            'store_id' => $storeId,
            'date' => $date,
        ], [
            'number_of_categories_point' => $this->getRatingPointCategory($standardDeviation, $filters),
            'number_of_items_point' => $this->getRatingPointProduct($standardDeviation, $filters),
            'product_utilization_rate_point' => $this->getProductUtilizationRate($standardDeviation, $filters),
            'product_cost_rate_point' => $this->getRatingPointCostRate($storeId, $filters),
            'low_product_reviews_point' => 0,
            'few_sold_out_items_point' => 0,

            'product_page_conversion_rate_point' => $this->getRatingPointProductPageConversionRate($standardDeviation, $itemsSales),
            'access_number_point' => $this->getRatingPointProductAccessNum($standardDeviation, $itemsSales),

            'event_sales_ratio_point' => $this->getRattingPointEventSalesRatio($standardDeviation, $filters),
            'sales_ratio_day_endings_0_5_point' => $this->getRattingPointEventSalesRatio($standardDeviation, $filters + ['endings_0_5' => true]),
            'coupon_effect_point' => 0,
            'rpp_ad_point' => $this->getRattingPointRppAd($standardDeviation, $filters),
            'coupon_advance_point' => 0,
            'rgroup_ad_point' => 0,
            'tda_ad_point' => 0,
            'sns_ad_point' => 0,
            'google_access_point' => 0,
            'instagram_access_point' => 0,

            'shipping_fee_point' => 0,
            'shipping_ratio_point' => 0,
            'bundling_ratio_point' => 0,
            'delivery_on_specified_day_point' => 0,
            'delivery_preparation_period_point' => 0,
            'shipping_according_to_the_delivery_date_point' => 0,

            'few_user_complaints_point' => 0,

            'email_newsletter_point' => 0,
            're_sales_num_rate_point' => $this->getReSalesNumRate($storeId, $filters),
            'review_writing_rate' => 0,
            'line_official_point' => 0,
            'instagram_followers' => 0,
            'ltv_point' => $this->getRatingPointLtv2yAmnt($storeId, $filters),
        ]);

        return $valueChain->refresh();
    }

    /**
     * Get the rating of the category.
     */
    public function getRatingPointCategory(StandardDeviation $standardDeviation, array $filters = [])
    {
        $totalCategoryOfAStore = $this->categoryAnalysisService->getTotalCategoryOfStores($filters);
        $standardDeviation = $standardDeviation?->number_of_categories;

        return is_null($standardDeviation) ? 0 : match (true) {
            $totalCategoryOfAStore >= 2 * $standardDeviation => 5,
            $totalCategoryOfAStore >= $standardDeviation && $totalCategoryOfAStore < 2 * $standardDeviation => 4,
            $totalCategoryOfAStore >= 0 && $totalCategoryOfAStore < $standardDeviation => 3,
            $totalCategoryOfAStore >= -$standardDeviation && $totalCategoryOfAStore <= 0 => 2,
            $totalCategoryOfAStore < -$standardDeviation => 1,
        };
    }

    /**
     * Get the rating of the product.
     */
    public function getRatingPointProduct(StandardDeviation $standardDeviation, array $filters = [])
    {
        $totalProductOfAStore = $this->productAnalysisService->getTotalProductOfStores($filters);
        $standardDeviation = $standardDeviation?->number_of_items;

        return is_null($standardDeviation) ? 0 : match (true) {
            $totalProductOfAStore >= 2 * $standardDeviation => 5,
            $totalProductOfAStore >= $standardDeviation && $totalProductOfAStore < 2 * $standardDeviation => 4,
            $totalProductOfAStore >= 0 && $totalProductOfAStore < $standardDeviation => 3,
            $totalProductOfAStore >= -$standardDeviation && $totalProductOfAStore <= 0 => 2,
            $totalProductOfAStore < -$standardDeviation => 1,
        };
    }

    public function getProductUtilizationRate(StandardDeviation $standardDeviation, array $filters = [])
    {
        $utilizationRateOfAStore = $this->productAnalysisService->getUtilizationRate($filters);
        $standardDeviation = $standardDeviation?->product_utilization_rate;

        return is_null($standardDeviation) ? 0 : match (true) {
            $utilizationRateOfAStore >= 2 * $standardDeviation => 5,
            $utilizationRateOfAStore >= $standardDeviation && $utilizationRateOfAStore < 2 * $standardDeviation => 4,
            $utilizationRateOfAStore >= 0 && $utilizationRateOfAStore < $standardDeviation => 3,
            $utilizationRateOfAStore >= -$standardDeviation && $utilizationRateOfAStore <= 0 => 2,
            $utilizationRateOfAStore < -$standardDeviation => 1,
        };
    }

    public function getRatingPointCostRate(string $storeId, array $filters = [])
    {
        $currentDate = Arr::get($filters, 'current_date', now()->format('Y-m'));
        $result = $this->mqAccountingService->getList([
            'store_id' => $storeId,
            'year_month' => $currentDate,
        ]);
        $costRate = 0;

        if ($result->get('success')) {
            $mqAccounting = $result->get('data')->first();
            $costRate = ($mqAccounting?->mq_cost?->cost_price_rate ?? 0) * 100;
        }

        return match (true) {
            $costRate < 20 => 5,
            $costRate >= 20 && $costRate <= 40 => 4,
            $costRate > 40 && $costRate <= 50 => 3,
            $costRate > 50 && $costRate <= 70 => 2,
            $costRate > 70 => 1,
        };
    }

    public function getRatingPointProductPageConversionRate(StandardDeviation $standardDeviation, $itemsSales)
    {
        $standardDeviation = $standardDeviation?->product_page_conversion_rate;
        $conversionRate = $itemsSales?->conversion_rate ?? 0;

        return is_null($standardDeviation) ? 0 : match (true) {
            $conversionRate >= 2 * $standardDeviation => 5,
            $conversionRate >= $standardDeviation && $conversionRate < 2 * $standardDeviation => 4,
            $conversionRate >= 0 && $conversionRate < $standardDeviation => 3,
            $conversionRate >= -$standardDeviation && $conversionRate <= 0 => 2,
            $conversionRate < -$standardDeviation => 1,
        };
    }

    public function getRatingPointProductAccessNum(StandardDeviation $standardDeviation, $itemsSales)
    {
        $standardDeviation = $standardDeviation?->access_number;
        $accessNum = $itemsSales?->access_num ?? 0;

        return is_null($standardDeviation) ? 0 : match (true) {
            $accessNum >= 2 * $standardDeviation => 5,
            $accessNum >= $standardDeviation && $accessNum < 2 * $standardDeviation => 4,
            $accessNum >= 0 && $accessNum < $standardDeviation => 3,
            $accessNum >= -$standardDeviation && $accessNum <= 0 => 2,
            $accessNum < -$standardDeviation => 1,
        };
    }

    public function getRattingPointEventSalesRatio(StandardDeviation $standardDeviation, array $filters = [])
    {
        $endings05 = Arr::get($filters, 'endings_0_5');
        $standardDeviation = $endings05
            ? $standardDeviation?->sales_ratio_day_endings_0_5
            : $standardDeviation?->event_sales_ratio;
        $salesAmnt = $this->policyR2Service->getListEventTimePeriods($filters);

        return is_null($standardDeviation) ? 0 : match (true) {
            $salesAmnt >= 2 * $standardDeviation => 5,
            $salesAmnt >= $standardDeviation && $salesAmnt < 2 * $standardDeviation => 4,
            $salesAmnt >= 0 && $salesAmnt < $standardDeviation => 3,
            $salesAmnt >= -$standardDeviation && $salesAmnt <= 0 => 2,
            $salesAmnt < -$standardDeviation => 1,
        };
    }

    public function getRattingPointRppAd(StandardDeviation $standardDeviation, array $filters = [])
    {
        $standardDeviation = $standardDeviation->rpp_ad;
        // $rppAd =
    }

    public function getReSalesNumRate(string $storeId, array $filters = [])
    {
        $currentDate = Arr::get($filters, 'current_date', now()->format('Y-m'));
        $mqAccounting = $this->mqAccountingService->getListReSalesNum([
            'year_month' => $currentDate,
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

        $standardDeviation = sqrt($standardDeviation / $totalShop);
        $reSalesNumRate = $mqAccounting->where('store_id', $storeId)->first()?->re_sales_num_rate ?? 0;

        return match (true) {
            $reSalesNumRate >= 2 * $standardDeviation => 5,
            $reSalesNumRate >= $standardDeviation && $reSalesNumRate < 2 * $standardDeviation => 4,
            $reSalesNumRate >= 0 && $reSalesNumRate < $standardDeviation => 3,
            $reSalesNumRate >= -$standardDeviation && $reSalesNumRate <= 0 => 2,
            $reSalesNumRate < -$standardDeviation => 1,
        };
    }

    public function getRatingPointLtv2yAmnt(string $storeId, array $filters = [])
    {
        $currentDate = Arr::get($filters, 'current_date', now()->format('Y-m'));
        $result = $this->mqAccountingService->getList([
            'year_month' => $currentDate,
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

        $standardDeviation = sqrt($standardDeviation / $totalShop);
        $ltv2yAmnt = $mqAccounting->where('store_id', $storeId)->first()?->mqCost?->ltv_2y_amnt ?? 0;

        return match (true) {
            $ltv2yAmnt >= 2 * $standardDeviation => 5,
            $ltv2yAmnt >= $standardDeviation && $ltv2yAmnt < 2 * $standardDeviation => 4,
            $ltv2yAmnt >= 0 && $ltv2yAmnt < $standardDeviation => 3,
            $ltv2yAmnt >= -$standardDeviation && $ltv2yAmnt <= 0 => 2,
            $ltv2yAmnt < -$standardDeviation => 1,
        };
    }

    /**
     * Get the list of monthly evaluation scores for the chart.
     */
    public function chartEvaluate(string $storeId, array $filters = [])
    {
        return $this->getListByStore($storeId, $filters)->map(fn ($valueChain) => [
            'store_id' => $valueChain->store_id,
            'date' => Carbon::create($valueChain->date)->format('Y/m'),
            'merchandise' => round((
                $valueChain->number_of_categories_point
                + $valueChain->number_of_items_point
                + $valueChain->product_utilization_rate_point
                + $valueChain->product_cost_rate_point
                + $valueChain->low_product_reviews_point
                + $valueChain->few_sold_out_items_point
            ) / 6, 2),
            'purchase' => round((
                $valueChain->purchase_form_point
                + $valueChain->stock_value_point
                + $valueChain->product_utilization_rate_point
            ) / 3, 2),
            'construction_production' => round((
                $valueChain->top_page_point
                + $valueChain->category_page_point
                + $valueChain->header_point
                + $valueChain->product_page_point
                + $valueChain->product_page_conversion_rate_point
                + $valueChain->product_thumbnail_point
                + $valueChain->access_number_point
                + $valueChain->featured_products_point
                + $valueChain->left_navigation_point
                + $valueChain->header_large_banner_small_banner_point
            ) / 10, 2),
            'event_sale' => round((
                $valueChain->event_sales_ratio_point
                + $valueChain->sales_ratio_day_endings_0_5_point
                + $valueChain->implementation_of_measures_point
                + $valueChain->coupon_effect_point
            ) / 4, 2),
            'advertisement' => round((
                $valueChain->rpp_ad_point
                + $valueChain->rpp_ad_operation_point
                + $valueChain->coupon_advance_point
                + $valueChain->rgroup_ad_point
                + $valueChain->tda_ad_point
                + $valueChain->sns_ad_point
                + $valueChain->google_access_point
                + $valueChain->instagram_access_point
            ) / 8, 2),
            'logistics' => round((
                $valueChain->compatible_point
                + $valueChain->shipping_fee_point
                + $valueChain->shipping_ratio_point
                + $valueChain->mail_service_point
                + $valueChain->bundling_ratio_point
                + $valueChain->gift_available_point
                + $valueChain->delivery_on_specified_day_point
                + $valueChain->delivery_preparation_period_point
                + $valueChain->shipping_on_the_specified_date_point
                + $valueChain->shipping_according_to_the_delivery_date_point
            ) / 10, 2),
            'orders' => round((
                $valueChain->system_introduction_point
                + $valueChain->order_through_rate_point
                + $valueChain->number_of_people_in_charge_of_ordering_point
            ) / 3, 2),
            'customer_service' => round((
                $valueChain->thank_you_email_point
                + $valueChain->what_s_included_point
                + $valueChain->follow_email_point
                + $valueChain->order_email_point
                + $valueChain->shipping_email_point
                + $valueChain->few_user_complaints_point
            ) / 6, 2),
            'crm' => round((
                $valueChain->email_newsletter_point
                + $valueChain->re_sales_num_rate_point
                + $valueChain->review_writing_rate_point
                + $valueChain->review_measures_point
                + $valueChain->line_official_point
                + $valueChain->instagram_followers_point
                + $valueChain->ltv_point
            ) / 7, 2),
        ]);
    }
}
