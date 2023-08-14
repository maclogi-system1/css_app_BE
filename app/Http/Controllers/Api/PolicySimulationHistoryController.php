<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PolicySimulationHistoryResource;
use App\Models\Policy;
use App\Repositories\Contracts\PolicySimulationHistoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicySimulationHistoryController extends Controller
{
    public function __construct(
        protected PolicySimulationHistoryRepository $policySimulationHistoryRepository,
    ) {
    }

    /**
     * Get a list of policy simulation histories by store id in this site.
     */
    public function getListByStore(Request $request, string $storeId): JsonResource|JsonResponse
    {
        $policySimulationHistories = PolicySimulationHistoryResource::collection(
            $this->policySimulationHistoryRepository->getListByStore($storeId, $request->query()),
        );
        $policySimulationHistories->wrap('policy_simulation_histories');

        return $policySimulationHistories;
    }
}
