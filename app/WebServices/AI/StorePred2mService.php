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
        $response = Http::post(
            'https://g5p4jn0a41.execute-api.ap-northeast-1.amazonaws.com/default/predict_2months-dev001',
            $data,
        );

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
                $dateRangeFilter['to_date']->format('Y-m-d'),
            ])
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
}
