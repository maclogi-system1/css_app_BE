<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\ItemsData;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccessAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    /**
     * Query access by category data.
     */
    public function getDataTableAccessAnalysis(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataYearMonthTableAccessAnalysis($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $accessResults = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                'items_data.catalog_id',
                DB::raw('SUM(item_all.visit_all) AS click_num'),
                DB::raw('SUM(item_all.visit_unpurchaser_all) AS click_unpurchase_num'),
                DB::raw('SUM(item_all.new_purchaser_all) AS new_user_sales_num'),
                DB::raw('SUM(item_all.all_purchaser_all) AS all_purchaser'),
                DB::raw('SUM(item_all.exit_count_all) AS exit_count_all'),
                DB::raw('AVG(item_all.exit_rate_all) AS exit_rate_all')
            )
            ->groupBy('items_data.catalog_id');

        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = explode(',', $categoryIds);
        if (! empty($categoryIds)) {
            $accessResults->whereIn('catalog_id', $categoryIdsArr);
        }

        $accessResults = ! is_null($accessResults->get()) ? $accessResults->get()->toArray() : [];

        $data = collect();
        $tableReportSearch = collect();
        foreach ($accessResults as $accessItem) {
            $clickNum = Arr::get($accessItem, 'click_num', 0);
            $clickUnpurchaseNum = Arr::get($accessItem, 'click_unpurchase_num', 0);
            $newPurchase = Arr::get($accessItem, 'new_user_sales_num', 0);
            $allPurchase = Arr::get($accessItem, 'all_purchaser', 0);

            $tableReportSearch->add([
                'category_id' => Arr::get($accessItem, 'catalog_id', ''),
                'display_name' => Arr::get($accessItem, 'catalog_id', ''),
                'category' => Arr::get($accessItem, 'catalog_id', ''),
                'click_num' => intval(Arr::get($accessItem, 'click_num', 0)),
                'ctr_rate' => $clickNum > 0 ? round(($clickNum - $clickUnpurchaseNum) / $clickNum, 2) : 0,
                'new_user_sales_num' => intval($newPurchase),
                'new_user_sales_rate' => $allPurchase > 0 ? round($newPurchase / $allPurchase, 2) : 0,
                'exist_user_sales_num' => intval(Arr::get($accessItem, 'exit_count_all', 0)),
                'exist_user_sales_rate' => round(Arr::get($accessItem, 'exit_rate_all', 0) * 100, 2),
            ]);
        }

        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'table_report_search' => $tableReportSearch,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query chart new user access data.
     */
    public function getDataChartNewUserAccess(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataYearMonthChartNewUserAccess($storeId, $filters);
        }
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $accessResults = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                'date',
                'items_data.catalog_id',
                DB::raw('SUM(item_all.visit_all) AS click_num')
            )
            ->groupBy('items_data.catalog_id', 'date')
            ->orderBy('date')
            ->get();
        $accessResults = ! is_null($accessResults) ? $accessResults->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($accessResults as $date => $accessItem) {
            $totalClick = collect($accessItem)->filter(function ($item) {
                return ! empty(Arr::get($item, 'catalog_id', ''));
            })->sum('click_num');
            $categories = [
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'new_user_sales_num' => $totalClick,
            ];
            foreach ($accessItem as $category) {
                $categoryId = Arr::get($category, 'catalog_id', '');
                if (! empty($categoryId)) {
                    $categories[$categoryId] = intval(Arr::get($category, 'click_num', 0));
                }
            }
            $data->add($categories);
        }

        return $data;
    }

    /**
     * Query repeater chart data.
     */
    public function getDataChartExistUserAccess(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataYearMonthChartExistUserAccess($storeId, $filters);
        }
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $accessResults = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                'date',
                'items_data.catalog_id',
                DB::raw('SUM(item_all.repeater_purchaser_all) AS repeater_purchase')
            )
            ->groupBy('items_data.catalog_id', 'date')
            ->orderBy('date')
            ->get();
        $accessResults = ! is_null($accessResults) ? $accessResults->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($accessResults as $date => $accessItem) {
            $totalClick = collect($accessItem)->filter(function ($item) {
                return ! empty(Arr::get($item, 'catalog_id', ''));
            })->sum('repeater_purchase');
            $categories = [
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'exist_user_sales_num' => $totalClick,
            ];
            foreach ($accessItem as $category) {
                $categoryId = Arr::get($category, 'catalog_id', '');
                if (! empty($categoryId)) {
                    $categories[$categoryId] = intval(Arr::get($category, 'repeater_purchase', 0));
                }
            }
            $data->add($categories);
        }

        return $data;
    }

    /**
     * Query access by category data with year-month.
     */
    private function getDataYearMonthTableAccessAnalysis(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $accessResults = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                'items_data.catalog_id',
                DB::raw('SUM(item_all.visit_all) AS click_num'),
                DB::raw('SUM(item_all.visit_unpurchaser_all) AS click_unpurchase_num'),
                DB::raw('SUM(item_all.new_purchaser_all) AS new_user_sales_num'),
                DB::raw('SUM(item_all.all_purchaser_all) AS all_purchaser'),
                DB::raw('SUM(item_all.exit_count_all) AS exit_count_all'),
                DB::raw('AVG(item_all.exit_rate_all) AS exit_rate_all'),
            )
            ->groupBy('items_data.catalog_id');

        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = explode(',', $categoryIds);
        if (! empty($categoryIds)) {
            $accessResults->whereIn('catalog_id', $categoryIdsArr);
        }

        $accessResults = ! is_null($accessResults->get()) ? $accessResults->get()->toArray() : [];

        $data = collect();
        $tableReportSearch = collect();
        foreach ($accessResults as $accessItem) {
            $clickNum = Arr::get($accessItem, 'click_num', 0);
            $clickUnpurchaseNum = Arr::get($accessItem, 'click_unpurchase_num', 0);
            $newPurchase = Arr::get($accessItem, 'new_user_sales_num', 0);
            $allPurchase = Arr::get($accessItem, 'all_purchaser', 0);

            $tableReportSearch->add([
                'category_id' => Arr::get($accessItem, 'catalog_id', ''),
                'display_name' => Arr::get($accessItem, 'catalog_id', ''),
                'category' => Arr::get($accessItem, 'catalog_id', ''),
                'click_num' => intval(Arr::get($accessItem, 'click_num', 0)),
                'ctr_rate' => $clickNum > 0 ? round(($clickNum - $clickUnpurchaseNum) / $clickNum, 2) : 0,
                'new_user_sales_num' => intval($newPurchase),
                'new_user_sales_rate' => $allPurchase > 0 ? round($newPurchase / $allPurchase, 2) : 0,
                'exist_user_sales_num' => intval(Arr::get($accessItem, 'exit_count_all', 0)),
                'exist_user_sales_rate' => round(Arr::get($accessItem, 'exit_rate_all', 0) * 100, 2),
            ]);
        }

        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'table_report_search' => $tableReportSearch,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query chart new user access data by year-month.
     */
    private function getDataYearMonthChartNewUserAccess(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $accessResults = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                DB::raw('SUBSTRING(date, 1, 6) as date'),
                'items_data.catalog_id',
                DB::raw('SUM(item_all.visit_all) AS click_num')
            )
            ->groupBy('items_data.catalog_id', DB::raw('SUBSTRING(date, 1, 6)'))
            ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'))
            ->get();
        $accessResults = ! is_null($accessResults) ? $accessResults->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($accessResults as $date => $accessItem) {
            $totalClick = collect($accessItem)->filter(function ($item) {
                return ! empty(Arr::get($item, 'catalog_id', ''));
            })->sum('click_num');
            $categories = [
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'new_user_sales_num' => $totalClick,
            ];
            foreach ($accessItem as $category) {
                $categoryId = Arr::get($category, 'catalog_id', '');
                if (! empty($categoryId)) {
                    $categories[$categoryId] = intval(Arr::get($category, 'click_num', 0));
                }
            }
            $data->add($categories);
        }

        return $data;
    }

    /**
     * Query repeater chart data by year-month.
     */
    private function getDataYearMonthChartExistUserAccess(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $accessResults = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(
                DB::raw('SUBSTRING(date, 1, 6) as date'),
                'items_data.catalog_id',
                DB::raw('SUM(item_all.repeater_purchaser_all) AS repeater_purchase')
            )
            ->groupBy('items_data.catalog_id', DB::raw('SUBSTRING(date, 1, 6)'))
            ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'))
            ->get();
        $accessResults = ! is_null($accessResults) ? $accessResults->groupBy('date')->toArray() : [];
        $data = collect();
        foreach ($accessResults as $date => $accessItem) {
            $totalClick = collect($accessItem)->filter(function ($item) {
                return ! empty(Arr::get($item, 'catalog_id', ''));
            })->sum('repeater_purchase');
            $categories = [
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'exist_user_sales_num' => $totalClick,
            ];
            foreach ($accessItem as $category) {
                $categoryId = Arr::get($category, 'catalog_id', '');
                if (! empty($categoryId)) {
                    $categories[$categoryId] = intval(Arr::get($category, 'repeater_purchase', 0));
                }
            }
            $data->add($categories);
        }

        return $data;
    }
}
