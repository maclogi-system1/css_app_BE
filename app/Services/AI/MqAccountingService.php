<?php

namespace App\Services\AI;

use App\Services\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class MqAccountingService extends Service
{
    public function getListByStore(string $storeId, array $filter = [])
    {
        return [];
    }

    public function getMonthlyChangesInFinancialIndicators(string $storeId, array $filter = [])
    {
        return [];
    }
}
