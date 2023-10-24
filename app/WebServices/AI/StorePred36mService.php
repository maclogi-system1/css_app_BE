<?php

namespace App\WebServices\AI;

use App\Constants\DatabaseConnectionConstant;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Facades\DB;

class StorePred36mService extends Service
{
    use HasMqDateTimeHandler;

    public function getPredSalesAmntByStoreId(?string $storeId = null)
    {
        $storePred36m = DB::connection(DatabaseConnectionConstant::INFERENCE_CONNECTION)
            ->table('store_pred_36m as sp36')
            ->whereYear('target_date', '>=', now())
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->select(
                'store_id',
                DB::raw("DATE_FORMAT(`sp36`.`target_date`, '%Y-%m') as target_ym"),
                DB::raw('SUM(pred_sales_amnt) as sales_amnt')
            )
            ->groupBy('store_id', DB::raw("DATE_FORMAT(`sp36`.`target_date`, '%Y-%m')"))
            ->get();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $storePred36m,
        ]);
    }

    public function getInferenceSales(string $storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date'];
        $toDate = $dateRangeFilter['to_date'];

        $sales = DB::connection(DatabaseConnectionConstant::INFERENCE_CONNECTION)
            ->table('store_pred_36m as sp36')
            ->where('sp36.store_id', $storeId)
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereDate('sp36.target_date', '>=', $fromDate)
                    ->whereDate('sp36.target_date', '<=', $toDate->endOfMonth());
            })
            ->select(
                'sp36.store_id',
                DB::raw("DATE_FORMAT(`sp36`.`target_date`, '%Y/%m') as target_ym"),
                DB::raw('SUM(`sp36`.`pred_sales_amnt`) as sales_amnt')
            )
            ->groupBy('sp36.store_id', DB::raw("DATE_FORMAT(`sp36`.`target_date`, '%Y/%m')"))
            ->get();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $sales,
        ]);
    }
}
