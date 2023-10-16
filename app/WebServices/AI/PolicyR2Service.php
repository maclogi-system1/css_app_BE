<?php

namespace App\WebServices\AI;

use App\Models\PolicyRealData\PolicyR2;
use App\WebServices\Service;

class PolicyR2Service extends Service
{
    public function getListRecommendByStore($storeId)
    {
        return PolicyR2::with(['rule1', 'rule2', 'rule3'])
            ->where('store_id', $storeId)
            ->paginate();
    }
}
