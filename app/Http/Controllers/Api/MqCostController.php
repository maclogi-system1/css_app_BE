<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MqCostRepository;
use Illuminate\Http\Request;

class MqCostController extends Controller
{
    public function __construct(
        protected MqCostRepository $mqCostRepository
    ) {
    }

    /**
     * Get ad cost from mq_cost by store_id.
     */
    public function getAdCostByStore(Request $request, string $storeId)
    {
        $adCost = $this->mqCostRepository->getAdCostByStore($storeId, $request->query());

        return response()->json([
            'ad_cost' => $adCost,
        ]);
    }
}
