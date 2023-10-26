<?php

namespace App\WebServices\AI;

use App\Constants\DatabaseConnectionConstant;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Facades\DB;

class ItemsPred2mService extends Service
{
    use HasMqDateTimeHandler;

    public function getItemsPred2m(string $itemsPred2mId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $itemsPred2m = DB::connection(DatabaseConnectionConstant::INFERENCE_CONNECTION)
            ->table('items_pred_2m')
            ->where('pred_id', $itemsPred2mId)
            ->whereBetween('date', [
                $dateRangeFilter['from_date']->format('Y-m-d'),
                $dateRangeFilter['to_date']->format('Y-m-d'),
            ])
            ->get();

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $itemsPred2m,
        ]);
    }
}
