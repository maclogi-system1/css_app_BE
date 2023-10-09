<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\UserTrends;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Collection;

class UserTrendService extends Service
{
    use HasMqDateTimeHandler;

    public function getListByStore(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $result = UserTrends::where('store_id', $storeId)
                    ->whereRaw('date >= ? AND date <= ?', [$fromDateStr, $toDateStr])
                    ->selectRaw('
                            store_id,
                            CONCAT(LEFT(date, 4), "/", RIGHT(date, 2)) AS date,
                            new_sales_amnt,
                            re_sales_amnt,
                            sales_amnt_per_new_user,
                            sales_amnt_per_re_user,
                            new_user_num,
                            re_user_num,
                            new_purchase_num AS new_purchase_user_num,
                            re_purchase_num AS re_purchase_user_num
                    ')
                    ->get();

        return $result;
    }
}
