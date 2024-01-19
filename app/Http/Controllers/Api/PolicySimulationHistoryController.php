<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PolicyResource;
use App\Http\Resources\PolicySimulationHistoryResource;
use App\Models\InferenceRealData\SuggestPolicies;
use App\Models\Policy;
use App\Models\PolicyRule;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\MqSheetRepository;
use App\Repositories\Contracts\PolicyRepository;
use App\Repositories\Contracts\PolicySimulationHistoryRepository;
use App\Repositories\Contracts\SuggestPolicyRepository;
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
        protected SuggestPolicyRepository $suggestPolicyRepository,
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

    public function show(Request $request, string $id): JsonResponse
    {
        $policySimulationHistory = $this->policySimulationHistoryRepository->find($id);
        $suggestPolicy = $this->suggestPolicyRepository->getSuggestedPoliciesByPredId(
            $policySimulationHistory->policy_pred_id
        );
        $simulation = $this->policyRepository->useWith(['rules'])->find(
            id: $policySimulationHistory->policy_id,
            filters: ['withTrashed' => true],
        );

        $policy = (new PolicyResource($simulation))->toArray($request);

        if ($suggestPolicy) {
            $policy = [
                'id' => $suggestPolicy->pred_id,
                'store_id' => $suggestPolicy->store_id,
                'category' => Policy::AI_RECOMMENDATION_CATEGORY,
                'category_name' => Policy::CATEGORIES[Policy::AI_RECOMMENDATION_CATEGORY],
                'created_at' => $suggestPolicy->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $suggestPolicy->created_at->format('Y-m-d H:i:s'),
                'name' => $simulation->name,
                'simulation_start_date' => $simulation->simulation_start_date,
                'simulation_end_date' => $simulation->simulation_end_date,
                'simulation_promotional_expenses' => $simulation->simulation_promotional_expenses,
                'simulation_store_priority' => $simulation->simulation_store_priority,
                'simulation_product_priority' => $simulation->simulation_product_priority,
                'policy_rules' => $suggestPolicy->suggestedPolicies->map(fn ($item) => [
                    'id' => $item->idx,
                    'class' => $item->policy_class,
                    'class_name' => SuggestPolicies::CLASSES[$item->policy_class],
                    'service' => $item->service,
                    'service_name' => SuggestPolicies::SERVICES[$item->service],
                    'value' => $item->policy_value,
                    'condition_1' => PolicyRule::NONE_CONDITION,
                    'condition_name_1' => PolicyRule::TEXT_INPUT_CONDITIONS[PolicyRule::NONE_CONDITION],
                    'condition_value_1' => null,
                    'condition_2' => PolicyRule::SHIPPING_CONDITION,
                    'condition_name_2' => PolicyRule::UPLOADABLE_CONDITIONS[PolicyRule::SHIPPING_CONDITION],
                    'condition_value_2' => '全商品',
                    'condition_3' => PolicyRule::NONE_CONDITION,
                    'condition_name_3' => PolicyRule::TEXT_INPUT_CONDITIONS[PolicyRule::NONE_CONDITION],
                    'condition_value_3' => null,
                ]),
            ];
        }

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

        return response()->json([
            'policy' => $policy,
            'history' => new PolicySimulationHistoryResource($policySimulationHistory),
            'mq_accountings' => $mqAccountingActualsAndExpected ?? [],
            'chart_sales_rate' => $chartSalesAndRate,
        ]);
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
