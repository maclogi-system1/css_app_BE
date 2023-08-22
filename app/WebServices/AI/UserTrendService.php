<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Collection;

class UserTrendService extends Service
{
    use HasMqDateTimeHandler;

    public function getListByStore(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            'Y/m'
        );

        $dataFake = collect();

        foreach ($dateTimeRange as $date) {
            $dataFake->add([
                'store_id' => $storeId,
                'date' => $date,
                'new_sales_amnt' => rand(1000000, 3000000),
                're_sales_amnt' => rand(1000000, 3000000),
                'sales_amnt_per_new_user' => rand(10000, 30000),
                'sales_amnt_per_re_user' => rand(10000, 30000),
                'new_user_num' => rand(1000, 5000),
                're_user_num' => rand(1000, 5000),
                'new_purchase_user_num' => rand(1000, 3000),
                're_purchase_user_num' => rand(1000, 3000),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => collect([
                'chart_user_trend' => $dataFake,
            ]),
        ]);
    }
}
