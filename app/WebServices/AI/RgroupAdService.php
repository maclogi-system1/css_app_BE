<?php

namespace App\WebServices\AI;

use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RgroupAdService extends Service
{
    public function getTotalRgroupAd(array $filters = [])
    {
        $storeId = Arr::get($filters, 'store_id');
        $currentDate = str_replace('-', '', Arr::get($filters, 'current_date', now()->format('Y-m')));

        return DB::kpiTable('rgroup_ad', 'ra')
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->whereBetween('date', ["{$currentDate}-01", "{$currentDate}-31"])
            ->join('rgroup_ad_sales_amnt as rsa', 'rsa.sales_amnt_id', '=', 'ra.sales_amnt_id')
            ->select(
                'store_id',
                DB::raw("DATE_FORMAT(STR_TO_DATE(`ra`.`date`, '%Y%m%d'), '%Y-%m') as ym"),
                DB::raw('(SUM(rsa.roas) + SUM(rsa.price_per_order) + SUM(rsa.cvr) + SUM(ra.cpc))/4 as rgroup_ad_total'),
            )
            ->groupBy('store_id', DB::raw("DATE_FORMAT(STR_TO_DATE(`ra`.`date`, '%Y%m%d'), '%Y-%m')"))
            ->get();
    }
}
