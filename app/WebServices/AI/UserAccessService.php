<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\MqAccounting;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class UserAccessService extends Service
{
    use HasMqDateTimeHandler;

    public function getListUserAccess(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $realAccessData = MqAccounting::where('store_id', $storeId)
            ->where('year', '>=', $dateRangeFilter['from_date']->year)
            ->where('month', '>=', $dateRangeFilter['from_date']->month)
            ->where('year', '<=', $dateRangeFilter['to_date']->year)
            ->where('month', '<=', $dateRangeFilter['to_date']->month)
            ->join('mq_access_num as ma', 'ma.mq_access_num_id', '=', 'mq_accounting.mq_access_num_id')
            ->selectRaw('
                store_id,
                CONCAT(year,"/",LPAD(month, 2, "0")) as date,
                ma.access_flow_sum
            ')
            ->get();
        $realAccessData = ! is_null($realAccessData) ? $realAccessData->toArray() : [];

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $realAccessData,
        ]);
    }

    public function getListUserAccessAds(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $data = collect();
        $result = MqAccounting::where('store_id', $storeId)
            ->where('year', '>=', $dateRangeFilter['from_date']->year)
            ->where('month', '>=', $dateRangeFilter['from_date']->month)
            ->where('year', '<=', $dateRangeFilter['to_date']->year)
            ->where('month', '<=', $dateRangeFilter['to_date']->month)
            ->join('mq_access_num as ma', 'ma.mq_access_num_id', '=', 'mq_accounting.mq_access_num_id')
            ->selectRaw('
                store_id,
                CONCAT(year,"/",LPAD(month, 2, "0")) as date,
                ma.access_flow_sum,
                ma.cpc_num
            ')
            ->get();
        $result = ! is_null($result) ? $result->toArray() : [];
        foreach ($result as $item) {
            $data->add([
                'store_id' => $storeId,
                'date' => Arr::get($item, 'date'),
                'access_flow_sum' => Arr::get($item, 'access_flow_sum'),
                'cpc_num' => Arr::get($item, 'cpc_num'),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }
}
