<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PolicyResource;
use App\Http\Resources\PolicySimulationHistoryResource;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\MqSheetRepository;
use App\Repositories\Contracts\PolicyRepository;
use App\Repositories\Contracts\PolicySimulationHistoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PolicySimulationHistoryController extends Controller
{
    public function __construct(
        protected MqAccountingRepository $mqAccountingRepository,
        protected MqSheetRepository $mqSheetRepository,
        protected PolicyRepository $policyRepository,
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

    public function show(Request $request, string $id): JsonResource
    {
        $policySimulationHistory = $this->policySimulationHistoryRepository->find($id);
        $simulation = $this->policyRepository->useWith(['rules'])->find(
            id: $policySimulationHistory->policy_id,
            filters: ['withTrashed' => true],
        );

        if ($simulation->isProcessDone()) {
            $mqSheet = $this->mqSheetRepository->getDefaultByStore($simulation->store_id);
            $filters = $request->query() + [
                    'from_date' => $policySimulationHistory->execution_time,
                    'to_date' => Carbon::create($policySimulationHistory->undo_time)->addMonths(2)->format('Y-m-d H:i:s'),
                    'mq_sheet_id' => $mqSheet->id,
                ];
            $mqAccountingActualsAndExpected = $this->mqAccountingRepository->getListCompareSimulationWithExpectedValues(
                $simulation->store_id,
                $policySimulationHistory,
                $filters,
            );
        }

        $chartSalesAndRate = $this->policySimulationHistoryRepository->chartSalesAndRateByStore($simulation->store_id);

        $policyResource = (new PolicyResource($simulation))
            ->additional([
                'history' => new PolicySimulationHistoryResource($policySimulationHistory),
                'mq_accountings' => $mqAccountingActualsAndExpected ?? [],
                'chart_sales_rate' => $chartSalesAndRate,
            ]);

        return $policyResource;
    }

    /**
     * Generate data to add policies.
     */
    public function getPolicyData(string $id): JsonResponse
    {
        $policySimulationHistory = $this->policySimulationHistoryRepository->find($id);
        $policyData = $this->policySimulationHistoryRepository->makeDataPolicy($policySimulationHistory);

        return response()->json($policyData);
    }
}
