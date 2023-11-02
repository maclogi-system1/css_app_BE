<?php

namespace App\Repositories\Eloquents;

use App\Models\PolicySimulationHistory;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\PolicySimulationHistoryRepository as PolicySimulationHistoryRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\AI\ItemsPred2mService;
use App\WebServices\AI\StorePred2mService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PolicySimulationHistoryRepository extends Repository implements PolicySimulationHistoryRepositoryContract
{
    public function __construct(
        protected ItemsPred2mService $itemsPred2mService,
        protected StorePred2mService $storePred2mService,
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return PolicySimulationHistory::class;
    }

    /**
     * Get a list of the policy by store_id.
     */
    public function getListByStore($storeId, array $filters = []): Collection|LengthAwarePaginator
    {
        $page = Arr::get($filters, 'page', 1);
        $perPage = Arr::get($filters, 'per_page', 10);

        $query = $this->model()->join('policies as p', 'p.id', '=', 'policy_simulation_histories.policy_id')
            ->where('p.store_id', $storeId)
            ->select('policy_simulation_histories.*', 'p.name');

        if ($perPage < 0) {
            return $query->get();
        }

        return $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
    }

    /**
     * Handle create a new policy simulation history.
     */
    public function create(array $data): ?PolicySimulationHistory
    {
        $policySimulationHistory = $this->model()->fill($data);
        $policySimulationHistory->save();

        return $policySimulationHistory;
    }

    /**
     * Get a specified policy simulation history.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?PolicySimulationHistory
    {
        $policySimulationHistory = $this->model()
            ->with('policy')
            ->where('id', $id)
            ->first($columns);

        if (is_null($policySimulationHistory)) {
            return null;
        }

        $predSalesAmnt = 0;

        if ($storePred2mId = $policySimulationHistory?->store_pred_2m) {
            $resultStorePred2m = $this->storePred2mService->getTotalSales($storePred2mId, [
                'from_date' => $policySimulationHistory->execution_time,
                'to_date' => $policySimulationHistory->undo_time,
            ]);
            if ($resultStorePred2m->get('success')) {
                $predSalesAmnt = $resultStorePred2m->get('data');
            }
        }

        /** @var \App\Repositories\Contracts\MqAccountingRepository */
        $mqAccountingRepository = app(MqAccountingRepository::class);
        $mqSalesAmnt = $mqAccountingRepository->getSalesAmntByStore($policySimulationHistory->policy->store_id, [
            'from_date' => $policySimulationHistory->execution_time,
            'to_date' => Carbon::create($policySimulationHistory->undo_time)->addMonths(2)->format('Y-m-d H:i:s'),
        ]);

        $growthRatePrediction = $mqSalesAmnt
            ? round($predSalesAmnt / $mqSalesAmnt, 2) - 1
            : 0;

        if ($policySimulationHistory->sale_effect != $growthRatePrediction) {
            $policySimulationHistory->sale_effect = $growthRatePrediction;
            $policySimulationHistory->save();
        }

        $policySimulationHistory->mq_sales_amnt = $mqSalesAmnt;
        $policySimulationHistory->pred_sales_amnt = $predSalesAmnt;
        $policySimulationHistory->growth_rate_prediction = $growthRatePrediction;

        return $policySimulationHistory;
    }
}
