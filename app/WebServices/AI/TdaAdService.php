<?php

namespace App\WebServices\AI;

use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TdaAdService extends Service
{
    public function getTotalTdaAd(array $filters = [])
    {
        $storeId = Arr::get($filters, 'store_id');
        $currentDate = str_replace('-', '', Arr::get($filters, 'current_date', now()->format('Y-m')));

        return DB::kpiTable('tda_ad', 'tda')
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->where('date', 'like', "{$currentDate}%")
            ->select(
                'store_id',
                DB::raw("DATE_FORMAT(STR_TO_DATE(`tda`.`date`, '%Y%m%d'), '%Y-%m') as ym"),
                DB::raw('(AVG(tda.ctr_rate) + SUM(tda.cpc))/2 as tda_ad_total'),
            )
            ->groupBy('store_id', DB::raw("DATE_FORMAT(STR_TO_DATE(`tda`.`date`, '%Y%m%d'), '%Y-%m')"))
            ->get();
    }
}
