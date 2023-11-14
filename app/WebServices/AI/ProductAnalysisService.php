<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\DailyRranking;
use App\Models\KpiRealData\ItemsData;
use App\Models\KpiRealData\ItemsSales;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    private $productIds;
    private $products;

    /**
     * Get product analysis summary by store_id.
     */
    public function getProductSummary($storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getProductYearMonthSummary($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $summaryInfo = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->selectRaw('
                COUNT(DISTINCT CASE WHEN item_all.visit_all > 0 THEN items_data.item_id END) as active_product,
                COUNT(DISTINCT CASE WHEN item_all.visit_all = 0 THEN items_data.item_id END) as un_active_product,
                COUNT(DISTINCT CASE WHEN items_data.inventory = 0 THEN items_data.item_id END) as zero_inventory
            ')
            ->groupBy('store_id')
            ->first();
        $summaryInfo = ! is_null($summaryInfo) ? $summaryInfo->toArray() : [];

        $activeProductNum = Arr::get($summaryInfo, 'active_product', 0);
        $unActiveProductNum = Arr::get($summaryInfo, 'un_active_product', 0);
        $totalProduct = $activeProductNum + $unActiveProductNum;
        $activeRatio = $totalProduct > 0 ? round(($activeProductNum / $totalProduct) * 100, 2) : 0;
        $zeroInventoryNum = Arr::get($summaryInfo, 'zero_inventory', 0);

        // Get products table
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $itemsSales = ItemsSales::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select(
                'item_no',
                DB::raw('AVG(conversion_rate) as conversion_rate'),
                DB::raw('AVG(sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->groupBy('item_no');
        if (! empty($productIds)) {
            $itemsSales->whereIn('item_no', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $itemsSales->whereIn('item_management_number', $managementNumsArr);
        }

        $productResult = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->leftJoinSub($itemsSales, 'items_sales', function ($join) {
                $join->on('items_data.item_id', '=', 'items_sales.item_no');
            })
            ->select(
                'item_id',
                'mng_number',
                'item_name',
                DB::raw('SUM(item_all.sales_all) as sales_all'),
                DB::raw('SUM(item_all.visit_all) as visit_all'),
                DB::raw('AVG(items_sales.conversion_rate) as conversion_rate'),
                DB::raw('AVG(items_sales.sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->orderByDesc('visit_all')
            ->groupBy('item_id', 'mng_number', 'item_name');
        if (! empty($productIds)) {
            $productResult->whereIn('item_id', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('mng_number', $managementNumsArr);
        }

        $productResult = ! is_null($productResult->get()) ? $productResult->get()->toArray() : [];
        $products = collect();
        foreach ($productResult as $index => $item) {
            if (! is_null(Arr::get($item, 'item_id'))) {
                $products->add([
                    'rank' => $index + 1,
                    'item_id' => Arr::get($item, 'item_id'),
                    'management_number' => Arr::get($item, 'mng_number'),
                    'item_name' => Arr::get($item, 'item_name'),
                    'sales_all' => intval(Arr::get($item, 'sales_all', 0)),
                    'visit_all' => intval(Arr::get($item, 'visit_all', 0)),
                    'conversion_rate' => round(Arr::get($item, 'conversion_rate', 0) * 100, 2),
                    'sales_amnt_per_user' => floatval(Arr::get($item, 'sales_amnt_per_user', 0)),
                ]);
            }
        }

        $data = [
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'active_product_count_all' => intval($activeProductNum),
            'unactive_product_count_all' => intval($unActiveProductNum),
            'active_ratio' => floatval($activeRatio),
            'zero_inventory_count' => intval($zeroInventoryNum),
            'products' => $products,
        ];

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get chart selected products sales per month from AI.
     */
    public function getChartSelectedProducts(array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getChartYearMonthSelectedProducts($filters);
        }

        $storeId = Arr::get($filters, 'store_id');
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $itemsSales = ItemsSales::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select(
                'date',
                DB::raw('AVG(conversion_rate) as conversion_rate'),
                DB::raw('AVG(sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->groupBy('date')
            ->orderBy('date');
        if (! empty($productIds)) {
            $itemsSales->whereIn('item_no', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $itemsSales->whereIn('item_management_number', $managementNumsArr);
        }
        $itemsSales = ! is_null($itemsSales->get()) ? $itemsSales->get()->toArray() : [];

        $productResult = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                'date',
                DB::raw('SUM(item_all.sales_all) as sales_all'),
                DB::raw('SUM(item_all.visit_all) as visit_all')
            )
            ->groupBy('date')
            ->orderBy('date');
        if (! empty($productIds)) {
            $productResult->whereIn('item_id', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('mng_number', $managementNumsArr);
        }
        $productResult = ! is_null($productResult->get()) ? $productResult->get()->toArray() : [];

        $data = collect($productResult)
            ->concat($itemsSales)
            ->groupBy('date')
            ->map(function ($items) {
                $date = Arr::get($items->first(), 'date');
                $salesAll = Arr::get($items->get(0), 'sales_all', 0);
                $visitAll = Arr::get($items->get(0), 'visit_all', 0);
                $conversionRate = Arr::get($items->get(1), 'conversion_rate', 0);
                $salesAmntPerUser = Arr::get($items->get(1), 'sales_amnt_per_user', 0);

                return [
                    'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                    'total_sales_all' => intval($salesAll),
                    'total_visit_all' => intval($visitAll),
                    'conversion_rate' => round(floatval($conversionRate) * 100, 2),
                    'sales_amnt_per_user' => floatval($salesAmntPerUser),
                ];
            })->values()
            ->all();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect($data),
        ]);
    }

    /**
     * Get chart products's trends from AI.
     */
    public function getChartProductsTrends(array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getChartYearMonthProductsTrends($filters);
        }
        $storeId = Arr::get($filters, 'store_id');

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        // Get products table
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $itemsSales = ItemsSales::where('store_id', $storeId)
            ->where('items_sales.date', '>=', $fromDateStr)
            ->where('items_sales.date', '<=', $toDateStr)
            ->select(
                'items_sales.date',
                'item_no',
                DB::raw('AVG(conversion_rate) as conversion_rate'),
                DB::raw('AVG(sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->groupBy('items_sales.date', 'item_no');
        if (! empty($productIds)) {
            $itemsSales->whereIn('item_no', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $itemsSales->whereIn('item_management_number', $managementNumsArr);
        }

        $productResult = ItemsData::where('store_id', $storeId)
            ->where('items_data.date', '>=', $fromDateStr)
            ->where('items_data.date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->leftJoinSub($itemsSales, 'items_sales', function ($join) {
                $join->on('items_data.item_id', '=', 'items_sales.item_no');
            })
            ->select(
                'items_data.date',
                'item_id',
                DB::raw('SUM(item_all.sales_all) as sales_all'),
                DB::raw('SUM(item_all.visit_all) as visit_all'),
                DB::raw('AVG(items_sales.conversion_rate) as conversion_rate'),
                DB::raw('AVG(items_sales.sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->orderBy('items_data.date')
            ->groupBy('items_data.date', 'item_id', 'mng_number', 'item_name');
        if (! empty($productIds)) {
            $productResult->whereIn('item_id', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('mng_number', $managementNumsArr);
        }

        $productResult = ! is_null($productResult->get()) ? $productResult->get()->groupBy('date')->toArray() : [];

        $data = collect();
        $dailySales = collect();
        $dailyAccess = collect();
        $dailyConvertionRate = collect();
        $dailySalesAmntPerUser = collect();

        foreach ($productResult as $key => $product) {
            $productsSales = collect();
            $productsAccess = collect();
            $productsConversionRate = collect();
            $productsSalesAmntPerUser = collect();

            foreach ($productIdsArr as $productId) {
                $filteredProduct = collect($product)->filter(function ($item) use ($productId) {
                    return Arr::get($item, 'item_id', '') == $productId;
                })->first();
                if (! empty($filteredProduct)) {
                    $productsSales->add([
                        Arr::get($filteredProduct, 'item_id') => intval(Arr::get($filteredProduct, 'sales_all', 0)),
                    ]);
                    $productsAccess->add([
                        Arr::get($filteredProduct, 'item_id') => intval(Arr::get($filteredProduct, 'visit_all', 0)),
                    ]);
                    $productsConversionRate->add([
                        Arr::get($filteredProduct, 'item_id') => round(Arr::get($filteredProduct, 'conversion_rate', 0) * 100, 2),
                    ]);
                    $productsSalesAmntPerUser->add([
                        Arr::get($filteredProduct, 'item_id') => floatval(Arr::get($filteredProduct, 'sales_amnt_per_user', 0)),
                    ]);
                } else {
                    $productsSales->add([
                        $productId => 0,
                    ]);
                    $productsAccess->add([
                        $productId => 0,
                    ]);
                    $productsConversionRate->add([
                        $productId => 0,
                    ]);
                    $productsSalesAmntPerUser->add([
                        $productId => 0,
                    ]);
                }
            }
            $date = substr($key, 0, 4).'/'.substr($key, 4, 2).'/'.substr($key, 6, 2);
            $dailySales->add([
                'date' => $date,
                'products' => $productsSales,
            ]);
            $dailyAccess->add([
                'date' => $date,
                'products' => $productsAccess,
            ]);
            $dailyConvertionRate->add([
                'date' => $date,
                'products' => $productsConversionRate,
            ]);
            $dailySalesAmntPerUser->add([
                'date' => $date,
                'products' => $productsSalesAmntPerUser,
            ]);
        }

        $data->add([
            'chart_sales_all' => $dailySales,
            'chart_visit_all' => $dailyAccess,
            'chart_conversion_rate' => $dailyConvertionRate,
            'chart_sales_amnt_per_user' => $dailySalesAmntPerUser,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get chart products's stay times from AI.
     */
    public function getChartProductsStayTimes(array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getChartYearMonthProductsStayTimes($filters);
        }

        $storeId = Arr::get($filters, 'store_id');
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $productResult = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                'item_id',
                DB::raw('AVG(item_all.duration_all) as duration_all'),
                DB::raw('AVG(item_all.exit_rate_all) as exit_rate_all'),
            )
            ->groupBy('item_id');
        if (! empty($productIds)) {
            $productResult->whereIn('item_id', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('mng_number', $managementNumsArr);
        }

        $productResult = ! is_null($productResult->get()) ? $productResult->get()->toArray() : [];

        $data = collect();
        foreach ($productResult as $product) {
            $data->add([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'item_id' => Arr::get($product, 'item_id'),
                'duration_all' => intval(Arr::get($product, 'duration_all', 0)),
                'exit_rate_all' => round(Arr::get($product, 'exit_rate_all', 0) * 100, 2),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get chart products's rakuten ranking from AI.
     */
    public function getChartProductsRakutenRanking(array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getChartYearMonthProductsRakutenRanking($filters);
        }

        $storeId = Arr::get($filters, 'store_id');
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $productResult = DailyRranking::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select(
                'itemid',
                'rank',
                'date'
            )
            ->orderBy('date');
        if (! empty($productIds)) {
            $productResult->whereIn('itemid', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('itemmngid', $managementNumsArr);
        }

        $productResult = ! is_null($productResult->get()) ? $productResult->get()->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($productResult as $key => $product) {
            $productRank = collect();
            foreach ($product as $item) {
                $productRank->add([
                    Arr::get($item, 'itemid') => Arr::get($item, 'rank'),
                ]);
            }

            $data->add([
                'date' => substr($key, 0, 4).'/'.substr($key, 4, 2).'/'.substr($key, 6, 2),
                'products_rank' => $productRank,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get chart products's reviews trends from AI.
     */
    public function getChartProductsReviewsTrends(array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getChartYearMonthProductsReviewsTrends($filters);
        }

        $storeId = Arr::get($filters, 'store_id');
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $purchaseNumResult = ItemsSales::where('store_id', $storeId)
            ->where('items_sales.date', '>=', $fromDateStr)
            ->where('items_sales.date', '<=', $toDateStr)
            ->select('item_no', 'items_sales.date', DB::raw('SUM(sum_purchase_num) as purchase_num'))
            ->groupBy('items_sales.date', 'item_no')
            ->orderBy('items_sales.date');
        if (! empty($productIds)) {
            $purchaseNumResult->whereIn('item_no', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $purchaseNumResult->whereIn('item_management_number', $managementNumsArr);
        }

        $productResult = ItemsData::where('store_id', $storeId)
            ->where('items_data.date', '>=', $fromDateStr)
            ->where('items_data.date', '<=', $toDateStr)
            ->leftJoinSub($purchaseNumResult, 'items_sales', function ($join) {
                $join->on('items_data.item_id', '=', 'items_sales.item_no')
                    ->on('items_data.date', '=', 'items_sales.date');
            })
            ->select(DB::raw('items_data.date as date'), 'item_id', DB::raw('SUM(review_all) as review_num'), 'items_sales.purchase_num')
            ->groupBy('items_data.date', 'item_id')
            ->orderBy('items_data.date');

        if (! empty($productIds)) {
            $productResult->whereIn('item_id', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('mng_number', $managementNumsArr);
        }

        $productResult = ! is_null($productResult->get()) ? $productResult->get()->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($productResult as $key => $product) {
            $productsReviewsNum = collect();
            $productsWritingRates = collect();

            foreach ($productIdsArr as $productId) {
                $filteredProduct = collect($product)->filter(function ($item) use ($productId) {
                    return Arr::get($item, 'item_id', '') == $productId;
                })->first();

                if (! empty($filteredProduct)) {
                    $reviewNum = intval(Arr::get($filteredProduct, 'review_num', 0));
                    $purchaseNum = intval(Arr::get($filteredProduct, 'purchase_num', 0));
                    $productsReviewsNum->add([
                        Arr::get($filteredProduct, 'item_id') => $reviewNum,
                    ]);

                    $productsWritingRates->add([
                        Arr::get($filteredProduct, 'item_id') => $purchaseNum > 0 ? round($reviewNum / $purchaseNum * 100, 2) : 0,
                    ]);
                } else {
                    $productsReviewsNum->add([
                        $productId => 0,
                    ]);

                    $productsWritingRates->add([
                        $productId => 0,
                    ]);
                }
            }

            $data->add([
                'date' => substr($key, 0, 4).'/'.substr($key, 4, 2).'/'.substr($key, 6, 2),
                'review_all' => $productsReviewsNum,
                'review_writing_rate' => $productsWritingRates,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get product year month analysis summary by store_id.
     */
    private function getProductYearMonthSummary($storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $summaryInfo = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->selectRaw('
                COUNT(DISTINCT CASE WHEN item_all.visit_all > 0 THEN items_data.item_id END) as active_product,
                COUNT(DISTINCT CASE WHEN item_all.visit_all = 0 THEN items_data.item_id END) as un_active_product,
                COUNT(DISTINCT CASE WHEN items_data.inventory = 0 THEN items_data.item_id END) as zero_inventory
            ')
            ->groupBy('store_id')
            ->first();
        $summaryInfo = ! is_null($summaryInfo) ? $summaryInfo->toArray() : [];

        $activeProductNum = Arr::get($summaryInfo, 'active_product', 0);
        $unActiveProductNum = Arr::get($summaryInfo, 'un_active_product', 0);
        $totalProduct = $activeProductNum + $unActiveProductNum;
        $activeRatio = $totalProduct > 0 ? round(($activeProductNum / $totalProduct) * 100, 2) : 0;
        $zeroInventoryNum = Arr::get($summaryInfo, 'zero_inventory', 0);

        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $itemsSales = ItemsSales::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->select(
                'item_no',
                DB::raw('AVG(conversion_rate) as conversion_rate'),
                DB::raw('AVG(sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->groupBy('item_no');
        $productResult = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->leftJoinSub($itemsSales, 'items_sales', function ($join) {
                $join->on('items_data.item_id', '=', 'items_sales.item_no');
            })
            ->select(
                'item_id',
                'mng_number',
                'item_name',
                DB::raw('SUM(item_all.sales_all) as sales_all'),
                DB::raw('SUM(item_all.visit_all) as visit_all'),
                DB::raw('AVG(items_sales.conversion_rate) as conversion_rate'),
                DB::raw('AVG(items_sales.sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->groupBy('item_id', 'mng_number', 'item_name')
            ->orderByDesc('visit_all')
            ->get();
        $productResult = ! is_null($productResult) ? $productResult->toArray() : [];
        $products = collect();
        foreach ($productResult as $index => $item) {
            if (! is_null(Arr::get($item, 'item_id'))) {
                $products->add([
                    'rank' => $index + 1,
                    'item_id' => Arr::get($item, 'item_id'),
                    'management_number' => Arr::get($item, 'mng_number'),
                    'item_name' => Arr::get($item, 'item_name'),
                    'sales_all' => intval(Arr::get($item, 'sales_all', 0)),
                    'visit_all' => intval(Arr::get($item, 'visit_all', 0)),
                    'conversion_rate' => round(Arr::get($item, 'conversion_rate', 0) * 100, 2),
                    'sales_amnt_per_user' => floatval(Arr::get($item, 'sales_amnt_per_user', 0)),
                ]);
            }
        }
        if (! empty($productIds)) {
            $products = $products->filter(function ($item) use ($productIdsArr) {
                return in_array(Arr::get($item, 'item_id'), $productIdsArr);
            });
        }

        if (! empty($managementNums)) {
            $products = $products->filter(function ($item) use ($managementNumsArr) {
                return in_array(Arr::get($item, 'management_number'), $managementNumsArr);
            });
        }

        $data = [
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'active_product_count_all' => intval($activeProductNum),
            'unactive_product_count_all' => intval($unActiveProductNum),
            'active_ratio' => floatval($activeRatio),
            'zero_inventory_count' => intval($zeroInventoryNum),
            'products' => $products,
        ];

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get chart year month selected product.
     */
    private function getChartYearMonthSelectedProducts(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $itemsSales = ItemsSales::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->select(
                DB::raw('SUBSTRING(date, 1, 6) as date'),
                DB::raw('AVG(conversion_rate) as conversion_rate'),
                DB::raw('AVG(sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'))
            ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'));
        if (! empty($productIds)) {
            $itemsSales->whereIn('item_no', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $itemsSales->whereIn('item_management_number', $managementNumsArr);
        }
        $itemsSales = ! is_null($itemsSales->get()) ? $itemsSales->get()->toArray() : [];

        $productResult = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                DB::raw('SUBSTRING(date, 1, 6) as date'),
                DB::raw('SUM(item_all.sales_all) as sales_all'),
                DB::raw('SUM(item_all.visit_all) as visit_all')
            )
            ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'))
            ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'));
        if (! empty($productIds)) {
            $productResult->whereIn('item_id', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('mng_number', $managementNumsArr);
        }
        $productResult = ! is_null($productResult->get()) ? $productResult->get()->toArray() : [];

        $data = collect($productResult)
            ->concat($itemsSales)
            ->groupBy('date')
            ->map(function ($items) {
                $date = Arr::get($items->first(), 'date', '');
                $salesAll = Arr::get($items->get(0), 'sales_all', 0);
                $visitAll = Arr::get($items->get(0), 'visit_all', 0);
                $conversionRate = Arr::get($items->get(1), 'conversion_rate', 0);
                $salesAmntPerUser = Arr::get($items->get(1), 'sales_amnt_per_user', 0);

                return [
                    'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                    'total_sales_all' => intval($salesAll),
                    'total_visit_all' => intval($visitAll),
                    'conversion_rate' => round(floatval($conversionRate) * 100, 2),
                    'sales_amnt_per_user' => floatval($salesAmntPerUser),
                ];
            })->values()
            ->all();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect($data),
        ]);
    }

    /**
     * Get chart year month products trends.
     */
    private function getChartYearMonthProductsTrends(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        // Get products table
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $itemsSales = ItemsSales::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(items_sales.date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(items_sales.date, 1, 6)'), '<=', $toDateStr)
            ->select(
                DB::raw('SUBSTRING(items_sales.date, 1, 6) as date'),
                'item_no',
                DB::raw('AVG(conversion_rate) as conversion_rate'),
                DB::raw('AVG(sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->groupBy(DB::raw('SUBSTRING(items_sales.date, 1, 6)'), 'item_no');
        if (! empty($productIds)) {
            $itemsSales->whereIn('item_no', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $itemsSales->whereIn('item_management_number', $managementNumsArr);
        }

        $productResult = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(items_data.date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(items_data.date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->leftJoinSub($itemsSales, 'items_sales', function ($join) {
                $join->on('items_data.item_id', '=', 'items_sales.item_no');
            })
            ->select(
                DB::raw('SUBSTRING(items_data.date, 1, 6) as date'),
                'item_id',
                DB::raw('SUM(item_all.sales_all) as sales_all'),
                DB::raw('SUM(item_all.visit_all) as visit_all'),
                DB::raw('AVG(items_sales.conversion_rate) as conversion_rate'),
                DB::raw('AVG(items_sales.sales_amnt_per_user) as sales_amnt_per_user')
            )
            ->orderBy(DB::raw('SUBSTRING(items_data.date, 1, 6)'))
            ->groupBy(DB::raw('SUBSTRING(items_data.date, 1, 6)'), 'item_id', 'mng_number', 'item_name');
        if (! empty($productIds)) {
            $productResult->whereIn('item_id', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('mng_number', $managementNumsArr);
        }

        $productResult = ! is_null($productResult->get()) ? $productResult->get()->groupBy('date')->toArray() : [];

        $data = collect();
        $dailySales = collect();
        $dailyAccess = collect();
        $dailyConvertionRate = collect();
        $dailySalesAmntPerUser = collect();

        foreach ($productResult as $key => $product) {
            $productsSales = collect();
            $productsAccess = collect();
            $productsConversionRate = collect();
            $productsSalesAmntPerUser = collect();

            foreach ($productIdsArr as $productId) {
                $filteredProduct = collect($product)->filter(function ($item) use ($productId) {
                    return Arr::get($item, 'item_id', '') == $productId;
                })->first();
                if (! empty($filteredProduct)) {
                    $productsSales->add([
                        Arr::get($filteredProduct, 'item_id') => intval(Arr::get($filteredProduct, 'sales_all', 0)),
                    ]);
                    $productsAccess->add([
                        Arr::get($filteredProduct, 'item_id') => intval(Arr::get($filteredProduct, 'visit_all', 0)),
                    ]);
                    $productsConversionRate->add([
                        Arr::get($filteredProduct, 'item_id') => round(Arr::get($filteredProduct, 'conversion_rate', 0) * 100, 2),
                    ]);
                    $productsSalesAmntPerUser->add([
                        Arr::get($filteredProduct, 'item_id') => floatval(Arr::get($filteredProduct, 'sales_amnt_per_user', 0)),
                    ]);
                } else {
                    $productsSales->add([
                        $productId => 0,
                    ]);
                    $productsAccess->add([
                        $productId => 0,
                    ]);
                    $productsConversionRate->add([
                        $productId => 0,
                    ]);
                    $productsSalesAmntPerUser->add([
                        $productId => 0,
                    ]);
                }
            }
            $date = substr($key, 0, 4).'/'.substr($key, 4, 2);
            $dailySales->add([
                'date' => $date,
                'products' => $productsSales,
            ]);
            $dailyAccess->add([
                'date' => $date,
                'products' => $productsAccess,
            ]);
            $dailyConvertionRate->add([
                'date' => $date,
                'products' => $productsConversionRate,
            ]);
            $dailySalesAmntPerUser->add([
                'date' => $date,
                'products' => $productsSalesAmntPerUser,
            ]);
        }

        $data->add([
            'chart_sales_all' => $dailySales,
            'chart_visit_all' => $dailyAccess,
            'chart_conversion_rate' => $dailyConvertionRate,
            'chart_sales_amnt_per_user' => $dailySalesAmntPerUser,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get chart year month products staytimes.
     */
    private function getChartYearMonthProductsStayTimes(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $productResult = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                'item_id',
                DB::raw('AVG(item_all.duration_all) as duration_all'),
                DB::raw('AVG(item_all.exit_rate_all) as exit_rate_all'),
            )
            ->groupBy('item_id');
        if (! empty($productIds)) {
            $productResult->whereIn('item_id', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('mng_number', $managementNumsArr);
        }

        $productResult = ! is_null($productResult->get()) ? $productResult->get()->toArray() : [];

        $data = collect();
        foreach ($productResult as $product) {
            $data->add([
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'item_id' => Arr::get($product, 'item_id'),
                'duration_all' => intval(Arr::get($product, 'duration_all', 0)),
                'exit_rate_all' => round(Arr::get($product, 'exit_rate_all', 0) * 100, 2),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get chart year month products rakuten ranking.
     */
    private function getChartYearMonthProductsRakutenRanking(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $productResult = DailyRranking::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->select(
                'itemid',
                'rank',
                DB::raw('SUBSTRING(date, 1, 6) as date')
            )
            ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'));
        if (! empty($productIds)) {
            $productResult->whereIn('itemid', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('itemmngid', $managementNumsArr);
        }

        $productResult = ! is_null($productResult->get()) ? $productResult->get()->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($productResult as $key => $product) {
            $productRank = collect();
            foreach ($product as $item) {
                $productRank->add([
                    Arr::get($item, 'itemid') => Arr::get($item, 'rank'),
                ]);
            }

            $data->add([
                'date' => substr($key, 0, 4).'/'.substr($key, 4, 2),
                'products_rank' => $productRank,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get chart year month products review trend.
     */
    private function getChartYearMonthProductsReviewsTrends(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));
        $productIds = Arr::get($filters, 'product_ids');
        $productIdsArr = explode(',', $productIds);
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);

        $purchaseNumResult = ItemsSales::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(items_sales.date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(items_sales.date, 1, 6)'), '<=', $toDateStr)
            ->select(
                'item_no',
                DB::raw('SUBSTRING(items_sales.date, 1, 6) as date'),
                DB::raw('SUM(sum_purchase_num) as purchase_num')
            )
            ->groupBy(DB::raw('SUBSTRING(items_sales.date, 1, 6)'), 'item_no')
            ->orderBy(DB::raw('SUBSTRING(items_sales.date, 1, 6)'));
        if (! empty($productIds)) {
            $purchaseNumResult->whereIn('item_no', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $purchaseNumResult->whereIn('item_management_number', $managementNumsArr);
        }

        $productResult = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(items_data.date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(items_data.date, 1, 6)'), '<=', $toDateStr)
            ->leftJoinSub($purchaseNumResult, 'items_sales', function ($join) {
                $join->on('items_data.item_id', '=', 'items_sales.item_no')
                    ->on(DB::raw('SUBSTRING(items_data.date, 1, 6)'), '=', 'items_sales.date');
            })
            ->select(
                DB::raw('SUBSTRING(items_data.date, 1, 6) as date'),
                'item_id',
                DB::raw('SUM(review_all) as review_num'),
                DB::raw('SUM(items_sales.purchase_num) as purchase_num')
            )
            ->groupBy(DB::raw('SUBSTRING(items_data.date, 1, 6)'), 'item_id')
            ->orderBy(DB::raw('SUBSTRING(items_data.date, 1, 6)'));

        if (! empty($productIds)) {
            $productResult->whereIn('item_id', $productIdsArr);
        }
        if (! empty($managementNums)) {
            $productResult->whereIn('mng_number', $managementNumsArr);
        }

        $productResult = ! is_null($productResult->get()) ? $productResult->get()->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($productResult as $key => $product) {
            $productsReviewsNum = collect();
            $productsWritingRates = collect();

            foreach ($productIdsArr as $productId) {
                $filteredProduct = collect($product)->filter(function ($item) use ($productId) {
                    return Arr::get($item, 'item_id', '') == $productId;
                })->first();

                if (! empty($filteredProduct)) {
                    $reviewNum = intval(Arr::get($filteredProduct, 'review_num', 0));
                    $purchaseNum = intval(Arr::get($filteredProduct, 'purchase_num', 0));
                    $productsReviewsNum->add([
                        Arr::get($filteredProduct, 'item_id') => $reviewNum,
                    ]);

                    $productsWritingRates->add([
                        Arr::get($filteredProduct, 'item_id') => $purchaseNum > 0 ? round($reviewNum / $purchaseNum * 100, 2) : 0,
                    ]);
                } else {
                    $productsReviewsNum->add([
                        $productId => 0,
                    ]);

                    $productsWritingRates->add([
                        $productId => 0,
                    ]);
                }
            }

            $data->add([
                'date' => substr($key, 0, 4).'/'.substr($key, 4, 2),
                'review_all' => $productsReviewsNum,
                'review_writing_rate' => $productsWritingRates,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get products's sales info from AI.
     */
    public function getProductSalesInfo(array $filters = []): Collection
    {
        $managementNums = Arr::get($filters, 'management_nums');
        $managementNumsArr = explode(',', $managementNums);
        $currentDate = Carbon::now();
        $currentYearMonth = sprintf('%04d%02d', $currentDate->year, $currentDate->month);

        $previousDate = $currentDate->subMonth();
        $previousYearMonth = sprintf('%04d%02d', $previousDate->year, $previousDate->month);

        $monthBeforePreviousDate = $previousDate->subMonth();
        $monthBeforePreviousYearMonth = sprintf('%04d%02d', $monthBeforePreviousDate->year, $monthBeforePreviousDate->month);

        $itemsSales = ItemsSales::whereIn('item_management_number', $managementNumsArr)
            ->whereRaw(
                '(SUBSTRING(items_sales.date, 1, 6) = ?
                OR SUBSTRING(items_sales.date, 1, 6) = ?
                OR SUBSTRING(items_sales.date, 1, 6) = ?)',
                [$currentYearMonth, $previousYearMonth, $monthBeforePreviousYearMonth]
            )
            ->selectRaw(
                'items_sales.item_no,
                SUM(CASE WHEN SUBSTRING(items_sales.date, 1, 6) = ? THEN sales_amnt ELSE 0 END) as current_month_sales,
                SUM(CASE WHEN SUBSTRING(items_sales.date, 1, 6) = ? THEN sales_amnt ELSE 0 END) as previous_month_sales,
                SUM(CASE WHEN SUBSTRING(items_sales.date, 1, 6) = ? THEN sales_amnt ELSE 0 END) as month_before_previous_sales',
                [$currentYearMonth, $previousYearMonth, $monthBeforePreviousYearMonth]
            )
            ->groupBy('items_sales.item_no');

        $result = ItemsData::whereIn('mng_number', $managementNumsArr)
            ->select(
                'items_data.mng_number',
                'items_data.item_name',
                'items_sales.current_month_sales',
                'items_sales.previous_month_sales',
                'items_sales.month_before_previous_sales'
            )
            ->distinct('item_id')
            ->leftJoinSub($itemsSales, 'items_sales', function ($join) {
                $join->on('items_data.item_id', '=', 'items_sales.item_no');
            })
            ->get();
        $result = ! is_null($result) ? $result->toArray() : [];

        $data = [];
        foreach ($result as $item) {
            $data[Arr::get($item, 'mng_number', '')] = [
                    'management_num' => Arr::get($item, 'mng_number', ''),
                    'item_name' => Arr::get($item, 'item_name', ''),
                    'current_month_sales' => intval(Arr::get($item, 'current_month_sales', 0)),
                    'previous_month_sales' => intval(Arr::get($item, 'previous_month_sales', 0)),
                    'month_before_previous_sales' => intval(Arr::get($item, 'month_before_previous_sales', 0)),
                ];
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect($data)->values()->all(),
        ]);
    }

    /**
     * Get the total number of products for each store.
     */
    public function getTotalProductOfStores(array $filters = [])
    {
        $storeId = Arr::get($filters, 'store_id');
        $currentDate = str_replace('-', '', Arr::get($filters, 'current_date', now()->format('Y-m')));

        return DB::kpiTable('items_data')
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->where('item_id', '!=', '')
            ->whereBetween('date', ["{$currentDate}-01", "{$currentDate}-31"])
            ->select(
                'store_id',
                DB::raw('COUNT(DISTINCT item_id) as total_prod'),
            )
            ->groupBy(
                'store_id',
                DB::raw("DATE_FORMAT(STR_TO_DATE(`date`, '%Y%m%d'), '%Y-%m')"),
            )
            ->get();
    }

    public function getUtilizationRate(array $filters = [])
    {
        $storeId = Arr::get($filters, 'store_id');
        $currentDate = str_replace('-', '', Arr::get($filters, 'current_date', now()->format('Y-m')));
        $registered = DB::kpiTable('items_data', 'id')
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->where('item_id', '!=', '')
            ->whereBetween('date', ["{$currentDate}-01", "{$currentDate}-31"])
            ->select(
                'store_id',
                DB::raw('COUNT(DISTINCT item_id) as total_prod'),
            )
            ->groupBy(
                'store_id',
                DB::raw("DATE_FORMAT(STR_TO_DATE(`date`, '%Y%m%d'), '%Y-%m')"),
            );
        $salesAllGt1 = $registered->clone()
            ->join('items_data_all as ida', function ($join) {
                $join->on('ida.items_data_all_id', '=', 'id.items_data_all_id')
                    ->where('ida.sales_all', '>', 1);
            });

        return DB::kpiTable($registered, 'id1')
            ->joinSub($salesAllGt1, 'id2', 'id1.store_id', '=', 'id2.store_id')
            ->select(
                'id1.store_id',
                DB::raw('(id1.total_prod - id2.total_prod) as utilization_rate')
            )
            ->get();
    }

    public function getProductAccessNumAndConversionRate(array $filters = [])
    {
        $storeId = Arr::get($filters, 'store_id');
        $currentDate = str_replace('-', '', Arr::get($filters, 'current_date', now()->format('Y-m')));

        return DB::kpiTable('items_sales')
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->whereBetween('date', ["{$currentDate}-01", "{$currentDate}-31"])
            ->select(
                'store_id',
                DB::raw("DATE_FORMAT(STR_TO_DATE(`date`, '%Y%m%d'), '%Y-%m') as ym"),
                DB::raw('ROUND(AVG(`conversion_rate`), 2) as conversion_rate'),
                DB::raw('SUM(`access_num`) as access_num'),
            )
            ->groupBy(
                'store_id',
                DB::raw("DATE_FORMAT(STR_TO_DATE(`date`, '%Y%m%d'), '%Y-%m')"),
            )
            ->get();
    }
}
