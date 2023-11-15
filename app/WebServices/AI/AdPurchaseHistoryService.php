<?php

namespace App\WebServices\AI;

use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AdPurchaseHistoryService extends Service
{
    public function getTotalCouponAdvanceAd(array $filters = [])
    {
        $storeId = Arr::get($filters, 'store_id');
        $currentDate = str_replace('-', '', Arr::get($filters, 'current_date', now()->format('Y-m')));

        return DB::kpiTable('ad_purchase_history')
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->whereBetween('date', ["{$currentDate}-01", "{$currentDate}-31"])
            ->select(
                'store_id',
                'coupon_advance_ad',
            )
            ->get();
    }
}
