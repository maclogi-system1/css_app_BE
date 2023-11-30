<?php

namespace App\WebServices\AI;

use App\Constants\DatabaseConnectionConstant;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class StorePred2mService extends Service
{
    use HasMqDateTimeHandler;

    public function runSimulation(array $data): Collection
    {
        $env = app()->environment('production') ? 'production' : 'staging';
        $url = config("ai.api_url.{$env}.predict_2_months_url");
        $response = Http::post($url, $data);

        return $this->toResponse($response);
    }

    public function getStorePred2m(string $storePred2mId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $storePred2m = DB::connection(DatabaseConnectionConstant::INFERENCE_CONNECTION)
            ->table('store_pred_2m')
            ->where('pred_id', $storePred2mId)
            ->whereBetween('date', [
                $dateRangeFilter['from_date']->format('Y-m-d'),
                $dateRangeFilter['to_date']->addMonths(2)->format('Y-m-d'),
            ])
            ->select(
                'store_id',
                DB::raw("DATE_FORMAT(`date`, '%Y/%m') as date_year_month"),
                DB::raw('SUM(`pred_sales_amnt`) as sales_amnt')
            )
            ->groupBy('store_id', DB::raw("DATE_FORMAT(`date`, '%Y/%m')"))
            ->get();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $storePred2m,
        ]);
    }

    public function getTotalSales(string $storePred2mId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $preSalesAmnt = DB::connection(DatabaseConnectionConstant::INFERENCE_CONNECTION)
            ->table('store_pred_2m')
            ->where('pred_id', $storePred2mId)
            ->whereBetween('date', [
                $dateRangeFilter['from_date']->format('Y-m-d'),
                $dateRangeFilter['to_date']->format('Y-m-d'),
            ])
            ->sum('pred_sales_amnt');

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $preSalesAmnt,
        ]);
    }

    public function getListByStore(string $storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $preSalesAmnt = DB::connection(DatabaseConnectionConstant::INFERENCE_CONNECTION)
            ->table('store_pred_2m')
            ->where('store_id', $storeId)
            ->whereBetween('date', [
                $dateRangeFilter['from_date']->format('Y-m-d'),
                $dateRangeFilter['to_date']->format('Y-m-d'),
            ])
            ->select(
                DB::raw('SUM(pred_sales_amnt) as pred_sales_amnt'),
                DB::raw("DATE_FORMAT(`date`, '%Y-%m') as year_month"),
            )
            ->groupBy("DATE_FORMAT(`date`, '%Y-%m')")
            ->get();

        return $preSalesAmnt;
    }
}
