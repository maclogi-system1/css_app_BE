<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\AccessSource;
use App\Models\KpiRealData\MqAccounting;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccessSourceService extends Service
{
    use HasMqDateTimeHandler;

    public function getTotalAccess(string $storeId, array $filters = [])
    {
        $now = now();
        $result = MqAccounting::where('store_id', $storeId)
                    ->where('year', $now->year)
                    ->where('month', $now->month)
                    ->join('mq_access_num as ma', 'ma.mq_access_num_id', '=', 'mq_accounting.mq_access_num_id')
                    ->selectRaw('SUM(ma.access_flow_sum) as access_flow_sum')
                    ->first();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'store_id' => $storeId,
                'date' => now()->format('Y-m-d H:i:s'),
                'total_access' => Arr::get($result, 'access_flow_sum', 0),
            ]),
        ]);
    }

    /**
     * Query chart access source data by date.
     */
    public function getListAccessSource(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getListYearMonthAccessSource($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $result = AccessSource::where('store_id', $storeId)
                    ->where('date', '>=', $fromDateStr)
                    ->where('date', '<=', $toDateStr)
                    ->join('access_rakuten as ark', 'ark.rakuten_id', '=', 'access_source.rakuten_id')
                    ->selectRaw('
                        store_id,
                        CONCAT(SUBSTRING(date, 1, 4), "/", SUBSTRING(date, 5, 2), "/", SUBSTRING(date, 7, 2)) AS date,
                        ark.rakuten_search,
                        ark.store_item_page,
                        ark.exp_rakuten_service,
                        ark.rakuten_market,
                        ark.rakuten_gold,
                        ark.rakuten_event,
                        ark.faivorite,
                        ark.basket,
                        ark.review,
                        ark.view_history,
                        ark.purchase_history,
                        ark.in_store_search,
                        ark.store_category_page,
                        ark.store_top,
                        ark.discount,
                        ark.room,
                        ark.ranking_market,
                        instagram,
                        google,
                        yahoo,
                        line,
                        twitter,
                        facebook
                    ')
                    ->get();
        $result = ! is_null($result) ? $result->toArray() : [];

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect($result),
        ]);
    }

    /**
     * Query table access source data by date.
     */
    public function getTableAccessSource(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getTableYearMonthAccessSource($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $result = AccessSource::where('store_id', $storeId)
                    ->where('date', '>=', $fromDateStr)
                    ->where('date', '<=', $toDateStr)
                    ->join('access_rakuten as ark', 'ark.rakuten_id', '=', 'access_source.rakuten_id')
                    ->selectRaw('
                        store_id,
                        SUM(ark.rakuten_search) AS rakuten_search,
                        SUM(ark.store_item_page) AS store_item_page,
                        SUM(ark.exp_rakuten_service) AS exp_rakuten_service,
                        SUM(ark.rakuten_market) AS rakuten_market,
                        SUM(ark.rakuten_gold) AS rakuten_gold,
                        SUM(ark.rakuten_event) AS rakuten_event,
                        SUM(ark.faivorite) AS faivorite,
                        SUM(ark.basket) AS basket,
                        SUM(ark.review) AS review,
                        SUM(ark.view_history) AS view_history,
                        SUM(ark.purchase_history) AS purchase_history,
                        SUM(ark.in_store_search) AS in_store_search,
                        SUM(ark.store_category_page) AS store_category_page,
                        SUM(ark.store_top) AS store_top,
                        SUM(ark.discount) AS discount,
                        SUM(ark.room) AS room,
                        SUM(ark.ranking_market) AS ranking_market,
                        SUM(instagram) AS instagram,
                        SUM(google) AS google,
                        SUM(yahoo) AS yahoo,
                        SUM(line) AS line,
                        SUM(twitter) AS twitter,
                        SUM(facebook) AS facebook
                    ')
                    ->groupBy('access_source.store_id')
                    ->first();
        $result = ! is_null($result) ? $result->toArray() : [];

        $chartAccessSource = collect();
        if (
            ! is_null($result)
            && count($result) > 0
        ) {
            $arrValues = array_values($result);
            $filteredArray = array_filter($arrValues, 'is_numeric');
            $sum = array_sum($filteredArray);

            foreach ($result as $key => $value) {
                if ($key != 'store_id') {
                    $chartAccessSource->add([
                        'display_name' => trans('kpi-labels.access_source.'.$key),
                        'name' => $key,
                        'value' => intval($value),
                        'rate' => round((intval($value) / $sum) * 100, 2),
                    ]);
                }
            }
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'store_id' => $storeId,
                'chart_access_source' => $chartAccessSource->sortByDesc('value')->values(),
            ]),
        ]);
    }

    /**
     * Query chart access source data by year-month.
     */
    private function getListYearMonthAccessSource(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $result = AccessSource::where('store_id', $storeId)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
                    ->join('access_rakuten as ark', 'ark.rakuten_id', '=', 'access_source.rakuten_id')
                    ->selectRaw('
                        store_id,
                        CONCAT(SUBSTRING(date, 1, 4), "/", SUBSTRING(date, 5, 2)) AS date,
                        ark.rakuten_search,
                        ark.store_item_page,
                        ark.exp_rakuten_service,
                        ark.rakuten_market,
                        ark.rakuten_gold,
                        ark.rakuten_event,
                        ark.faivorite,
                        ark.basket,
                        ark.review,
                        ark.view_history,
                        ark.purchase_history,
                        ark.in_store_search,
                        ark.store_category_page,
                        ark.store_top,
                        ark.discount,
                        ark.room,
                        ark.ranking_market,
                        instagram,
                        google,
                        yahoo,
                        line,
                        twitter,
                        facebook
                    ')
                    ->get();
        $result = ! is_null($result) ? $result->toArray() : [];

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect($result),
        ]);
    }

    /**
     * Query table access source data by year-month.
     */
    private function getTableYearMonthAccessSource(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $result = AccessSource::where('store_id', $storeId)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
                    ->join('access_rakuten as ark', 'ark.rakuten_id', '=', 'access_source.rakuten_id')
                    ->selectRaw('
                        store_id,
                        SUM(ark.rakuten_search) AS rakuten_search,
                        SUM(ark.store_item_page) AS store_item_page,
                        SUM(ark.exp_rakuten_service) AS exp_rakuten_service,
                        SUM(ark.rakuten_market) AS rakuten_market,
                        SUM(ark.rakuten_gold) AS rakuten_gold,
                        SUM(ark.rakuten_event) AS rakuten_event,
                        SUM(ark.faivorite) AS faivorite,
                        SUM(ark.basket) AS basket,
                        SUM(ark.review) AS review,
                        SUM(ark.view_history) AS view_history,
                        SUM(ark.purchase_history) AS purchase_history,
                        SUM(ark.in_store_search) AS in_store_search,
                        SUM(ark.store_category_page) AS store_category_page,
                        SUM(ark.store_top) AS store_top,
                        SUM(ark.discount) AS discount,
                        SUM(ark.room) AS room,
                        SUM(ark.ranking_market) AS ranking_market,
                        SUM(instagram) AS instagram,
                        SUM(google) AS google,
                        SUM(yahoo) AS yahoo,
                        SUM(line) AS line,
                        SUM(twitter) AS twitter,
                        SUM(facebook) AS facebook
                    ')
                    ->groupBy('access_source.store_id')
                    ->first();
        $result = ! is_null($result) ? $result->toArray() : [];

        $chartAccessSource = collect();
        if (
            ! is_null($result)
            && count($result) > 0
        ) {
            $arrValues = array_values($result);
            $filteredArray = array_filter($arrValues, 'is_numeric');
            $sum = array_sum($filteredArray);

            foreach ($result as $key => $value) {
                if ($key != 'store_id') {
                    $chartAccessSource->add([
                        'display_name' => trans('kpi-labels.access_source.'.$key),
                        'name' => $key,
                        'value' => intval($value),
                        'rate' => round((intval($value) / $sum) * 100, 2),
                    ]);
                }
            }
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'store_id' => $storeId,
                'chart_access_source' => $chartAccessSource->sortByDesc('value')->values(),
            ]),
        ]);
    }

    public function getTotalAccessGoogleAndInstagram(array $filters = [])
    {
        $storeId = Arr::get($filters, 'store_id');
        $currentDate = str_replace('-', '', Arr::get($filters, 'current_date', now()->format('Y-m')));

        return DB::kpiTable('access_source')
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->where('date', 'like', "{$currentDate}%")
            ->select(
                'store_id',
                DB::raw("DATE_FORMAT(STR_TO_DATE(`date`, '%Y%m%d'), '%Y-%m') as ym"),
                DB::raw('SUM(google) as google'),
                DB::raw('SUM(instagram) as instagram'),
            )
            ->groupBy('store_id', DB::raw("DATE_FORMAT(STR_TO_DATE(`date`, '%Y%m%d'), '%Y-%m')"))
            ->get();
    }
}
