<?php

namespace App\WebServices\AI;

use App\Models\PolicyRealData\PolicyR2;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PolicyR2Service extends Service
{
    public function getListRecommendByStore($storeId)
    {
        return PolicyR2::with(['rule1', 'rule2', 'rule3'])
            ->where('store_id', $storeId)
            ->paginate();
    }

    public function getListEventTimePeriods(array $filters)
    {
        $storeId = Arr::get($filters, 'store_id');
        $currentDate = Carbon::create(Arr::get($filters, 'current_date'))->toImmutable();

        $eventTime = DB::policyTable('policy_r2')
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->where(function ($query) use ($currentDate) {
                $query->where(function ($query) use ($currentDate) {
                    $query->whereYear('start_date', $currentDate->year)
                        ->whereMonth('start_date', $currentDate->month);
                })->orWhere(function ($query) use ($currentDate) {
                    $query->whereYear('end_date', $currentDate->year)
                        ->whereMonth('end_date', $currentDate->month);
                });
            })
            ->select(
                'store_id',
                DB::raw("DATE_FORMAT(`start_date`, '%Y-%m-%d') as start_date"),
                DB::raw("DATE_FORMAT(`end_date`, '%Y-%m-%d') as end_date"),
            )
            ->groupBy(
                'store_id',
                DB::raw("DATE_FORMAT(`start_date`, '%Y-%m-%d')"),
                DB::raw("DATE_FORMAT(`end_date`, '%Y-%m-%d')"),
            )
            ->get()
            ->map(function ($item) use ($currentDate) {
                if (Carbon::create($item->start_date)->month != $currentDate->month) {
                    $item->start_date = $currentDate->firstOfMonth()->format('Y-m-d');
                }

                if (Carbon::create($item->end_date)->month != $currentDate->month) {
                    $item->end_date = $currentDate->endOfMonth()->format('Y-m-d');
                }

                return $item;
            })
            ->groupBy('store_id');

        $sales = [];
        $endings05 = Arr::get($filters, 'endings_0_5');

        foreach ($eventTime as $storeId => $items) {
            $sales[$storeId] = 0;

            foreach ($items as $item) {
                $sales[$storeId] += DB::kpiTable('shop_analytics_daily', 'ad')
                    ->join('shop_analytics_daily_sales_amnt as sa', 'sa.sales_amnt_id', '=', 'ad.sales_amnt_id')
                    ->where('ad.store_id', $storeId)
                    ->whereBetween('ad.date', [
                        Carbon::create($item->start_date)->format('Ymd'),
                        Carbon::create($item->end_date)->format('Ymd'),
                    ])
                    ->when($endings05, function ($query) {
                        $query->whereRaw('ad.date MOD 5 = 0');
                    })
                    ->select(
                        DB::raw('SUM(sa.all_value) as all_value'),
                    )
                    ->first()
                    ?->all_value ?? 0;
            }
        }

        return $sales;
    }
}
