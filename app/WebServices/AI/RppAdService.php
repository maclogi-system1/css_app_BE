<?php

namespace App\WebServices\AI;

use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RppAdService extends Service
{
    public function getTotalRppAd(array $filters = [])
    {
        $storeId = Arr::get($filters, 'store_id');
        $currentDate = str_replace('-', '', Arr::get($filters, 'current_date', now()->format('Y-m')));

        return DB::kpiTable('rpp_ad', 'ra')
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->where('date', 'like', "{$currentDate}%")
            ->join('rpp_roas as rr', 'rr.rpp_roas_id', '=', 'ra.rpp_roas_id')
            ->join('rpp_cvr_rate as rc', 'rc.rpp_cvr_rate_id', '=', 'ra.rpp_cvr_rate_id')
            ->join('rpp_price_per_order as ro', 'ro.rpp_price_per_order_id', '=', 'ra.rpp_price_per_order_id')
            ->select(
                'store_id',
                DB::raw("DATE_FORMAT(STR_TO_DATE(`ra`.`date`, '%Y%m%d'), '%Y-%m') as ym"),
                DB::raw('(SUM(rr.sum_720h) + SUM(ra.ctr) + SUM(rc.sum_720h) + SUM(ra.actual_cpc_sum) + SUM(ro.sum_720h))/5 as rpp_ad_total'),
            )
            ->groupBy('store_id', DB::raw("DATE_FORMAT(STR_TO_DATE(`ra`.`date`, '%Y%m%d'), '%Y-%m')"))
            ->get();
    }
}
