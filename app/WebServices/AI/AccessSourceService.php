<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\AccessSource;
use App\Models\KpiRealData\MqAccounting;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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
                'total_access' => Arr::get($result, 'access_flow_sum'),
            ]),
        ]);
    }

    public function getListAccessSource(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDateStr = $dateRangeFilter['from_date']->year
                        .str_pad($dateRangeFilter['from_date']->month, 2, '0', STR_PAD_LEFT);
        $toDateStr = $dateRangeFilter['to_date']->year
                        .str_pad($dateRangeFilter['to_date']->month, 2, '0', STR_PAD_LEFT);

        $result = AccessSource::where('store_id', $storeId)
                    ->where('date', '>=', $fromDateStr)
                    ->where('date', '<=', $toDateStr)
                    ->join('access_rakuten as ark', 'ark.rakuten_id', '=', 'access_source.rakuten_id')
                    ->selectRaw('
                        store_id,
                        CONCAT(LEFT(date, 4), "/", RIGHT(date, 2)) AS date,
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
                    ->get()->toArray();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect($result),
        ]);
    }

    public function getTableAccessSource(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDateStr = $dateRangeFilter['from_date']->year
                        .str_pad($dateRangeFilter['from_date']->month, 2, '0', STR_PAD_LEFT);
        $toDateStr = $dateRangeFilter['to_date']->year
                        .str_pad($dateRangeFilter['to_date']->month, 2, '0', STR_PAD_LEFT);

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
                    ->first()->toArray();
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
                'chart_access_source' => $chartAccessSource->sortByDesc('value'),
            ]),
        ]);
    }
}
