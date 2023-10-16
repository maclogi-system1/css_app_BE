<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\ItemsData;
use App\Models\KpiRealData\ItemsSales;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CategoryAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    private $categoryIds;
    private $categories;

    /**
     * Get categories analysis summary by store_id.
     */
    public function getCategorySummary($storeId, array $filters = []): Collection
    {
        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = array_filter(explode(',', $categoryIds));
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = Arr::get($dateRangeFilter, 'from_date')->format('Y-m-d');
        $toDate = Arr::get($dateRangeFilter, 'to_date')->format('Y-m-d');

        $itemsData = DB::connection('kpi_real_data')->table('items_data as id')
            ->where('id.store_id', $storeId)
            ->where('id.catalog_id', '!=', '')
            ->join('items_data_all as ida', 'ida.items_data_all_id', '=', 'id.items_data_all_id')
            ->when(! empty($categoryIdsArr), function (Builder $query) use ($categoryIdsArr) {
                $query->whereIn('id.catalog_id', $categoryIdsArr);
            })
            ->whereRaw("STR_TO_DATE(`id`.`date`, '%Y%m%d') >= '{$fromDate}'")
            ->whereRaw("STR_TO_DATE(`id`.`date`, '%Y%m%d') <= '{$toDate}'")
            ->selectRaw('
                SUM(CASE WHEN ida.visit_all > 0 THEN 1 ELSE 0 END) as active_category_count_all,
                SUM(CASE WHEN ida.visit_all = 0 THEN 1 ELSE 0 END) as unactive_category_count_all,
                SUM(id.zero_inventory_days) as zero_inventory_num
            ')
            ->first();
        $totalCategoryCountAll = $itemsData->active_category_count_all + $itemsData->unactive_category_count_all;
        $itemsData->active_ratio = $totalCategoryCountAll
            ? round($itemsData->active_category_count_all / $totalCategoryCountAll * 100, 2)
            : 0;
        $itemsData->from_date = $fromDate;
        $itemsData->to_date = $toDate;
        $itemsData->categories = $this->getCategories($storeId, [
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ], $categoryIdsArr);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $itemsData,
        ]);
    }

    /**
     * Get a list of the catalog in items_data and items_sales filter by storeId and dateRangeFilter.
     */
    public function getCategories(string $storeId, array $dateRangeFilter, array $categoryIdsArr = []): Collection
    {
        $fromDate = Arr::get($dateRangeFilter, 'from_date');
        $toDate = Arr::get($dateRangeFilter, 'to_date');

        return DB::connection('kpi_real_data')
            ->table(function (Builder $query) use ($storeId, $fromDate, $toDate, $categoryIdsArr) {
                $query->from('items_data', 'id2')
                    ->join('items_data_all as ida', 'ida.items_data_all_id', '=', 'id2.items_data_all_id')
                    ->when(! empty($categoryIdsArr), function ($query) use ($categoryIdsArr) {
                        $query->whereIn('id2.catalog_id', $categoryIdsArr);
                    })
                    ->where('id2.store_id', $storeId)
                    ->where('id2.catalog_id', '!=', '')
                    ->whereRaw("STR_TO_DATE(`id2`.`date`, '%Y%m%d') >= '{$fromDate}'")
                    ->whereRaw("STR_TO_DATE(`id2`.`date`, '%Y%m%d') <= '{$toDate}'")
                    ->groupBy('id2.catalog_id')
                    ->select(
                        'id2.catalog_id',
                        DB::raw('SUM(`ida`.`visit_all`) as visit_all'),
                        DB::raw('SUM(`ida`.`sales_all`) as sales_all'),
                    );
            }, 'id1')
            ->joinSub(function ($query) use ($storeId, $fromDate, $toDate, $categoryIdsArr) {
                $query->from('items_sales', 'is2')
                    ->when(! empty($categoryIdsArr), function ($query) use ($categoryIdsArr) {
                        $query->whereIn('is2.catalog_id', $categoryIdsArr);
                    })
                    ->where('is2.store_id', $storeId)
                    ->where('is2.catalog_id', '!=', '')
                    ->whereRaw("STR_TO_DATE(`is2`.`date`, '%Y%m%d') >= '{$fromDate}'")
                    ->whereRaw("STR_TO_DATE(`is2`.`date`, '%Y%m%d') <= '{$toDate}'")
                    ->groupBy('is2.catalog_id')
                    ->select(
                        DB::raw("CONCAT(`is2`.`catalog_id`, '.0') as catalog_id"),
                        DB::raw('SUM(`is2`.`sales_amnt_per_user`) as sales_amnt_per_user'),
                        DB::raw('ROUND(AVG(`is2`.`conversion_rate`), 2) * 100 as conversion_rate'),
                    );
            }, 'is1', 'is1.catalog_id', '=', 'id1.catalog_id')
            ->select(
                'id1.catalog_id',
                'id1.visit_all',
                'id1.sales_all',
                'is1.sales_amnt_per_user',
                'is1.conversion_rate',
            )
            ->get();
    }

    /**
     * Get chart selected categories sales per month from AI.
     */
    public function getChartSelectedCategories(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = array_filter(explode(',', $categoryIds));
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = Arr::get($dateRangeFilter, 'from_date')->format('Y-m-d');
        $toDate = Arr::get($dateRangeFilter, 'to_date')->format('Y-m-d');

        $data = DB::connection('kpi_real_data')
            ->table(function (Builder $query) use ($storeId, $fromDate, $toDate, $categoryIdsArr) {
                $query->from('items_data', 'id2')
                    ->join('items_data_all as ida', 'ida.items_data_all_id', '=', 'id2.items_data_all_id')
                    ->when(! empty($categoryIdsArr), function ($query) use ($categoryIdsArr) {
                        $query->whereIn('id2.catalog_id', $categoryIdsArr);
                    })
                    ->where('id2.store_id', $storeId)
                    ->whereRaw("STR_TO_DATE(`id2`.`date`, '%Y%m%d') >= '{$fromDate}'")
                    ->whereRaw("STR_TO_DATE(`id2`.`date`, '%Y%m%d') <= '{$toDate}'")
                    ->groupBy('id2.date')
                    ->select(
                        'id2.date',
                        DB::raw('SUM(`ida`.`visit_all`) as visit_all'),
                        DB::raw('SUM(`ida`.`sales_all`) as sales_all'),
                    );
            }, 'id1')
            ->leftjoinSub(function ($query) use ($storeId, $fromDate, $toDate, $categoryIdsArr) {
                $query->from('items_sales', 'is2')
                    ->when(! empty($categoryIdsArr), function ($query) use ($categoryIdsArr) {
                        $query->whereIn('is2.catalog_id', $categoryIdsArr);
                    })
                    ->where('is2.store_id', $storeId)
                    ->whereRaw("STR_TO_DATE(`is2`.`date`, '%Y%m%d') >= '{$fromDate}'")
                    ->whereRaw("STR_TO_DATE(`is2`.`date`, '%Y%m%d') <= '{$toDate}'")
                    ->groupBy('is2.date')
                    ->select(
                        'is2.date',
                        DB::raw('SUM(`is2`.`sales_amnt_per_user`) as sales_amnt_per_user'),
                        DB::raw('ROUND(AVG(`is2`.`conversion_rate`), 2) * 100 as conversion_rate'),
                    );
            }, 'is1', 'is1.date', '=', 'id1.date')
            ->select(
                DB::raw("DATE_FORMAT(STR_TO_DATE(`id1`.`date`, '%Y%m%d'), '%Y/%m') as date"),
                DB::raw('SUM(`id1`.`sales_all`) as total_sales_all'),
                DB::raw('SUM(`id1`.`visit_all`) as total_visit_all'),
                DB::raw('ROUND(AVG(CASE WHEN `is1`.`conversion_rate` IS NULL THEN 0 ELSE `is1`.`conversion_rate` END), 2) as conversion_rate'),
                DB::raw('SUM((CASE WHEN `is1`.`sales_amnt_per_user` IS NULL THEN 0 ELSE `is1`.`sales_amnt_per_user` END)) as sales_amnt_per_user'),
            )
            ->groupBy(DB::raw("DATE_FORMAT(STR_TO_DATE(`id1`.`date`, '%Y%m%d'), '%Y/%m')"))
            ->get();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get chart categories's trends from AI.
     */
    public function getChartCategoriesTrends(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = array_filter(explode(',', $categoryIds));
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = Arr::get($dateRangeFilter, 'from_date')->format('Y-m-d');
        $toDate = Arr::get($dateRangeFilter, 'to_date')->format('Y-m-d');

        [$salesAll, $visitAll] = $this->getItemsDataTrend($storeId, $fromDate, $toDate, $categoryIdsArr);
        [$conversionRate, $salesAmntPerUser] = $this->getItemsSalesTrend($storeId, $fromDate, $toDate, $categoryIdsArr);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => [
                'sales_all' => $salesAll,
                'visit_all' => $visitAll,
                'conversion_rate' => $conversionRate,
                'sales_amnt_per_user' => $salesAmntPerUser,
            ],
        ]);
    }

    protected function getItemsDataTrend($storeId, $fromDate, $toDate, array $categoryIdsArr = [])
    {
        $itemsData = DB::connection('kpi_real_data')
            ->table('items_data', 'id1')
            ->where('id1.store_id', $storeId)
            ->join('items_data_all as ida', 'ida.items_data_all_id', '=', 'id1.items_data_all_id')
            ->when(! empty($categoryIdsArr), function (Builder $query) use ($categoryIdsArr) {
                $query->whereIn('id1.catalog_id', $categoryIdsArr);
            })
            ->where('id1.catalog_id', '!=', '')
            ->whereRaw("STR_TO_DATE(`id1`.`date`, '%Y%m%d') >= '{$fromDate}'")
            ->whereRaw("STR_TO_DATE(`id1`.`date`, '%Y%m%d') <= '{$toDate}'")
            ->select(
                DB::raw("DATE_FORMAT(STR_TO_DATE(`id1`.`date`, '%Y%m%d'), '%Y/%m/%d') as date"),
                'id1.catalog_id',
                DB::raw('SUM(ida.sales_all) as sales_all'),
                DB::raw('SUM(ida.visit_all) as visit_all'),
            )
            ->groupBy('id1.date', 'id1.catalog_id')
            ->get()
            ->groupBy('date');

        $salesAll = $itemsData->map(fn ($item) => ([
                    'date' => $item->first()->date,
                    'categories' => $item->map(fn ($category) => ([$category->catalog_id => $category->sales_all]))
                        ->collapse()
                        ->toArray(),
                ]))->values()->toArray();
        $visitAll = $itemsData->map(fn ($item) => ([
                'date' => $item->first()->date,
                'categories' => $item->map(fn ($category) => ([$category->catalog_id => $category->visit_all]))
                    ->collapse()
                    ->toArray(),
            ]))->values()->toArray();

        return [$salesAll, $visitAll];
    }

    protected function getItemsSalesTrend($storeId, $fromDate, $toDate, array $categoryIdsArr = [])
    {
        $itemsSales = DB::connection('kpi_real_data')
            ->table('items_sales', 'is1')
            ->where('is1.store_id', $storeId)
            ->when(! empty($categoryIdsArr), function (Builder $query) use ($categoryIdsArr) {
                $query->whereIn('is1.catalog_id', $categoryIdsArr);
            })
            ->where('is1.catalog_id', '!=', '')
            ->whereRaw("STR_TO_DATE(`is1`.`date`, '%Y%m%d') >= '{$fromDate}'")
            ->whereRaw("STR_TO_DATE(`is1`.`date`, '%Y%m%d') <= '{$toDate}'")
            ->select(
                DB::raw("DATE_FORMAT(STR_TO_DATE(`is1`.`date`, '%Y%m%d'), '%Y/%m/%d') as date"),
                'is1.catalog_id',
                DB::raw('SUM(is1.sales_amnt_per_user) as sales_amnt_per_user'),
                DB::raw('ROUND(AVG(is1.conversion_rate), 2) * 100 as conversion_rate'),
            )
            ->groupBy('is1.date', 'is1.catalog_id')
            ->get()
            ->groupBy('date');

        $conversionRate = $itemsSales->map(function ($item) {
            $values = $item->map(fn ($category) => ([$category->catalog_id => $category->conversion_rate]));
            $categories = [];

            foreach ($values as $value) {
                foreach ($value as $key => $v) {
                    $categories[$key] = $v;
                }
            }

            return [
                'date' => $item->first()->date,
                'categories' => $categories,
            ];
        })->values()->toArray();
        $salesAmntPerUser = $itemsSales->map(function ($item) {
            $values = $item->map(fn ($category) => ([$category->catalog_id => $category->sales_amnt_per_user]));
            $categories = [];

            foreach ($values as $value) {
                foreach ($value as $key => $v) {
                    $categories[$key] = $v;
                }
            }

            return [
                'date' => $item->first()->date,
                'categories' => $categories,
            ];
        })->values()->toArray();

        return [$conversionRate, $salesAmntPerUser];
    }

    /**
     * Get chart categories's stay times from AI.
     */
    public function getChartCategoriesStayTimes(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = array_filter(explode(',', $categoryIds));
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = Arr::get($dateRangeFilter, 'from_date')->format('Y-m-d');
        $toDate = Arr::get($dateRangeFilter, 'to_date')->format('Y-m-d');

        $itemsSales = ItemsSales::where('store_id', $storeId)
            ->when(! empty($categoryIdsArr), function ($query) use ($categoryIdsArr) {
                $query->whereIn('catalog_id', $categoryIdsArr);
            })
            ->where('catalog_id', '!=', '')
            ->whereRaw("STR_TO_DATE(`date`, '%Y%m%d') >= '{$fromDate}'")
            ->whereRaw("STR_TO_DATE(`date`, '%Y%m%d') <= '{$toDate}'")
            ->select(
                'catalog_id',
                DB::raw('SUM(`stay_duration`) as stay_duration'),
                DB::raw('ROUND(AVG(`churn_rate`), 2) * 100 as churn_rate'),
            )
            ->groupBy('catalog_id')
            ->get()
            ->map(function ($item) use ($fromDate, $toDate) {
                $item->from_date = $fromDate;
                $item->to_date = $toDate;

                return $item;
            });

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $itemsSales,
        ]);
    }

    /**
     * Get chart categories's reviews trends from AI.
     */
    public function chartCategoriesReviewsTrends(array $filters = []): Collection
    {
        $storeId = Arr::get($filters, 'store_id');
        $categoryIds = Arr::get($filters, 'category_ids');
        $categoryIdsArr = array_filter(explode(',', $categoryIds));
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = Arr::get($dateRangeFilter, 'from_date')->format('Y-m-d');
        $toDate = Arr::get($dateRangeFilter, 'to_date')->format('Y-m-d');

        $itemsdata = ItemsData::where('store_id', $storeId)
            ->when(! empty($categoryIdsArr), function ($query) use ($categoryIdsArr) {
                $query->whereIn('catalog_id', $categoryIdsArr);
            })
            ->where('catalog_id', '!=', '')
            ->whereRaw("STR_TO_DATE(`date`, '%Y%m%d') >= '{$fromDate}'")
            ->whereRaw("STR_TO_DATE(`date`, '%Y%m%d') <= '{$toDate}'")
            ->select(
                'catalog_id',
                DB::raw("DATE_FORMAT(STR_TO_DATE(`date`, '%Y%m%d'), '%Y/%m/%d') as date"),
                DB::raw('SUM(`review_count`) as review_count'),
                DB::raw('SUM(`review_point`) as review_point'),
            )
            ->groupBy('catalog_id', 'date')
            ->get()
            ->groupBy('date');
        $review = $itemsdata->map(fn ($item) => ([
            'date' => $item->first()->date,
            'review_count' => $item->map(fn ($category) => ([$category->catalog_id => $category->review_count]))
                ->collapse()
                ->toArray(),
            'review_point' => $item->map(fn ($category) => ([$category->catalog_id => $category->review_point]))
                ->collapse()
                ->toArray(),
        ]))->values();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $review,
        ]);
    }

    /**
     * Get categories's sales info from AI.
     */
    public function getCategorySalesInfo(array $filters = []): Collection
    {
        $categoryIds = Arr::get($filters, 'catalog_ids');
        $categoryIdsArr = explode(',', $categoryIds);
        $currentDate = Carbon::now();
        $currentYearMonth = sprintf('%04d%02d', $currentDate->year, $currentDate->month);

        $previousDate = $currentDate->subMonth();
        $previousYearMonth = sprintf('%04d%02d', $previousDate->year, $previousDate->month);

        $monthBeforePreviousDate = $previousDate->subMonth();
        $monthBeforePreviousYearMonth = sprintf('%04d%02d', $monthBeforePreviousDate->year, $monthBeforePreviousDate->month);

        $itemsSales = ItemsSales::whereIn('items_sales.catalog_id', $categoryIdsArr)
            ->whereRaw(
                '(SUBSTRING(items_sales.date, 1, 6) = ?
                OR SUBSTRING(items_sales.date, 1, 6) = ?
                OR SUBSTRING(items_sales.date, 1, 6) = ?)',
                [$currentYearMonth, $previousYearMonth, $monthBeforePreviousYearMonth]
            )
            ->selectRaw(
                'items_sales.catalog_id,
                SUM(CASE WHEN SUBSTRING(items_sales.date, 1, 6) = ? THEN sales_amnt ELSE 0 END) as current_month_sales,
                SUM(CASE WHEN SUBSTRING(items_sales.date, 1, 6) = ? THEN sales_amnt ELSE 0 END) as previous_month_sales,
                SUM(CASE WHEN SUBSTRING(items_sales.date, 1, 6) = ? THEN sales_amnt ELSE 0 END) as month_before_previous_sales
            ',
                [$currentYearMonth, $previousYearMonth, $monthBeforePreviousYearMonth]
            )
            ->groupBy('items_sales.catalog_id');

        $result = ItemsData::whereIn('items_data.catalog_id', $categoryIdsArr)
            ->select(
                'items_data.catalog_id',
                'items_sales.current_month_sales',
                'items_sales.previous_month_sales',
                'items_sales.month_before_previous_sales'
            )
            ->distinct('items_data.catalog_id')
            ->leftJoinSub($itemsSales, 'items_sales', function ($join) {
                $join->on('items_data.catalog_id', '=', 'items_sales.catalog_id');
            })
            ->get();
        $result = ! is_null($result) ? $result->toArray() : [];

        $data = [];
        foreach ($result as $item) {
            $data[Arr::get($item, 'catalog_id', '')] = [
                    'catalog_id' => Arr::get($item, 'catalog_id', ''),
                    'catalog_name' => Arr::get($item, 'item_name', ''),
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
}
