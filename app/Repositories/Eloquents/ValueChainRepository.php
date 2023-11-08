<?php

namespace App\Repositories\Eloquents;

use App\Models\ValueChain;
use App\Repositories\Contracts\ValueChainRepository as ValueChainRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\CategoryAnalysisService;
use App\WebServices\AI\MqAccountingService;
use App\WebServices\AI\ProductAnalysisService;
use App\WebServices\OSS\ShopService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ValueChainRepository extends Repository implements ValueChainRepositoryContract
{
    public function __construct(
        protected CategoryAnalysisService $categoryAnalysisService,
        protected ProductAnalysisService $productAnalysisService,
        protected MqAccountingService $mqAccountingService,
        protected ShopService $shopService,
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
     * Get data for monthly evaluation chart.
     */
    public function chartMonthlyEvaluation(string $storeId, array $filters = [])
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
                'product_page_conversion_rate' => $valueChain->product_page_conversion_rate,
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
                    + $valueChain->product_page_conversion_rate
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
                    + $valueChain->shipping_according_to_the_delivery_date_point
                ) / 9, 2),
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
                'rpp_cvr_rate_point' => $valueChain->rpp_cvr_rate_point,
                'review_writing_rate_point' => $valueChain->review_writing_rate_point,
                'review_measures_point' => $valueChain->review_measures_point,
                'line_official' => $valueChain->line_official_point,
                'instagram_followers_point' => $valueChain->instagram_followers_point,
                'ltv_point' => $valueChain->ltv_point,
                'average' => round((
                    $valueChain->email_newsletter_point
                    + $valueChain->rpp_cvr_rate_point
                    + $valueChain->review_writing_rate_point
                    + $valueChain->review_measures_point
                    + $valueChain->line_official_point
                    + $valueChain->instagram_followers_point
                    + $valueChain->ltv_point
                ) / 7, 2),
            ],
        ];
    }

    public function handleCreateDefault(string $storeId, array $filters = [])
    {
        $date = Carbon::create(Arr::get($filters, 'current_date'));
        $ratingPointCategory = $this->getRatingPointCategory($storeId, $filters);
        $ratingPointProduct = $this->getRatingPointProduct($storeId, $filters);
        $ratingPointOccupancyRate = $this->getOccupancyRate($storeId, $filters);
        $ratingPointCostRate = $this->getRatingPointCostRate($storeId, $filters);

        $data = [
            'store_id' => $storeId,
            'date' => $date,
            'number_of_categories_point' => $ratingPointCategory,
            'number_of_items_point' => $ratingPointProduct,
            'product_utilization_rate_point' => $ratingPointOccupancyRate,
            'product_cost_rate_point' => $ratingPointCostRate,
        ];

        $valueChain = $this->model()->create($data);

        return $valueChain;
    }

    /**
     * Get the rating of the category.
     */
    private function getRatingPointCategory(string $storeId, array $filters = [])
    {
        $totalCatategoryOfStores = $this->categoryAnalysisService->getTotalCategoryOfStores($filters);
        $totalShop = $totalCatategoryOfStores->count();

        if (! $totalShop) {
            return 1;
        }

        $categoryAverage = $totalCatategoryOfStores->reduce(
            fn (?int $carry, $item) => $carry + $item->total_cate,
            0
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($totalCatategoryOfStores as $item) {
            $standardDeviation += pow($item->total_cate - $categoryAverage, 2);
        }

        $standardDeviation = sqrt($standardDeviation / $totalShop);
        $totalCategoryOfAStore = $totalCatategoryOfStores->where('store_id', $storeId)->first()?->total_cate;

        return match (true) {
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
    private function getRatingPointProduct(string $storeId, array $filters = [])
    {
        $totalProductOfStores = $this->productAnalysisService->getTotalProductOfStores($filters);
        $totalShop = $totalProductOfStores->count();

        if (! $totalShop) {
            return 1;
        }

        $productAverage = $totalProductOfStores->reduce(
            fn (?int $carry, $item) => $carry + $item->total_prod,
            0
        ) / $totalShop;
        $standardDeviation = 0;

        foreach ($totalProductOfStores as $item) {
            $standardDeviation += pow($item->total_prod - $productAverage, 2);
        }

        $standardDeviation = sqrt($standardDeviation / $totalShop);
        $totalProductOfAStore = $totalProductOfStores->where('store_id', $storeId)->first()?->total_prod;

        return match (true) {
            $totalProductOfAStore >= 2 * $standardDeviation => 5,
            $totalProductOfAStore >= $standardDeviation && $totalProductOfAStore < 2 * $standardDeviation => 4,
            $totalProductOfAStore >= 0 && $totalProductOfAStore < $standardDeviation => 3,
            $totalProductOfAStore >= -$standardDeviation && $totalProductOfAStore <= 0 => 2,
            $totalProductOfAStore < -$standardDeviation => 1,
        };
    }

    public function getOccupancyRate(string $storeId, array $filters = [])
    {
        $occupancyRateOfStore = $this->productAnalysisService->getUtilizationRate($filters);
        $totalShop = $occupancyRateOfStore->count();

        if (! $totalShop) {
            return 1;
        }

        $averageProductUtilizationRate = $occupancyRateOfStore->reduce(
            fn (?int $carry, $item) => $carry + $item->occupancy_rate,
            0,
        );
        $standardDeviation = 0;

        foreach ($occupancyRateOfStore as $item) {
            $standardDeviation += pow($item->occupancy_rate - $averageProductUtilizationRate, 2);
        }

        $standardDeviation = sqrt($standardDeviation / $totalShop);
        $occupancyRateOfAStore = $occupancyRateOfStore->where('store_id', $storeId)->first()?->occupancy_rate;

        return match (true) {
            $occupancyRateOfAStore >= 2 * $standardDeviation => 5,
            $occupancyRateOfAStore >= $standardDeviation && $occupancyRateOfAStore < 2 * $standardDeviation => 4,
            $occupancyRateOfAStore >= 0 && $occupancyRateOfAStore < $standardDeviation => 3,
            $occupancyRateOfAStore >= -$standardDeviation && $occupancyRateOfAStore <= 0 => 2,
            $occupancyRateOfAStore < -$standardDeviation => 1,
        };
    }

    private function getRatingPointCostRate(string $storeId, array $filters = [])
    {
        $currentDate = Arr::get($filters, 'current_date', now()->format('Y-m'));
        $mqAccounting = $this->mqAccountingService->getListByStore($storeId, [
            'from_date' => $currentDate,
            'to_date' => $currentDate,
        ]);

        $costRate = Arr::get(Arr::first($mqAccounting), 'mq_cost.cost_price_rate') * 100;

        return match (true) {
            $costRate < 20 => 5,
            $costRate >= 20 && $costRate <= 40 => 4,
            $costRate > 40 && $costRate <= 50 => 3,
            $costRate > 50 && $costRate <= 70 => 2,
            $costRate > 70 => 1,
        };
    }
}
