<?php

namespace App\Repositories\Eloquents;

use App\Models\InferenceRealData\SuggestPolicies;
use App\Models\KpiRealData\ShopAnalyticsDaily;
use App\Models\Policy;
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
            ->with(['policy', 'manager'])
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

    /**
     * Generate data to add policies from history.
     */
    public function makeDataPolicy(PolicySimulationHistory $policySimulationHistory): array
    {
        $startDate = Carbon::create($policySimulationHistory->start_date);
        $endDate = Carbon::create($policySimulationHistory->end_date);
        $simulation = $policySimulationHistory->policy;

        $result = [
            'store_id' => $simulation->store_id,
            'category' => Policy::MEASURES_CATEGORY,
            'immediate_reflection' => 1,
            'status' => -5,
            'job_group_code' => 'jg-',
            'job_group_title' => $policySimulationHistory->title,
            'job_group_explanation' => null,
            'managers' => [$policySimulationHistory->manager?->id],
            'template_id' => $policySimulationHistory->templatePolicy(),
            'job_title' => null,
            'execution_date' => $startDate->format('Y-m-d'),
            'execution_time' => $startDate->format('H:i'),
            'undo_date' => $endDate->format('Y-m-d'),
            'undo_time' => $endDate->format('H:i'),
            'type_item_url' => 0,
            'item_urls' => null,
            'has_banner' => 0,
            'remark' => null,
            'catch_copy_pc_text' => null,
            'catch_copy_pc_error' => null,
            'catch_copy_sp_text' => null,
            'catch_copy_sp_error' => null,
            'item_name_text' => null,
            'item_name_text_error' => null,
            'point_magnification' => null,
            'point_start_date' => null,
            'point_start_time' => null,
            'point_end_date' => null,
            'point_end_time' => null,
            'point_error' => null,
            'point_operational' => null,
            'discount_type' => null,
            'discount_rate' => null,
            'discount_price' => null,
            'discount_undo_type' => null,
            'discount_error' => null,
            'discount_display_price' => null,
            'double_price_text' => null,
            'shipping_fee' => null,
            'stock_specify' => null,
            'time_sale_start_date' => null,
            'time_sale_start_time' => null,
            'time_sale_end_date' => null,
            'time_sale_end_time' => null,
            'is_unavailable_for_search' => null,
            'description_for_pc' => null,
            'description_for_sp' => null,
            'description_by_sales_method' => null,
        ];

        if (
            $policySimulationHistory->class == SuggestPolicies::POINT_CLASS
            && $policySimulationHistory->service == SuggestPolicies::GIVE_DOUBLE_POINTS_SERVICE
        ) {
            $result['point_magnification'] = $policySimulationHistory->value;
            $result['point_start_date'] = $startDate->format('Y-m-d');
            $result['point_start_time'] = $startDate->format('H:i');
            $result['point_end_date'] = $endDate->format('Y-m-d');
            $result['point_end_time'] = $endDate->format('H:i');
        } elseif (
            $policySimulationHistory->class == SuggestPolicies::TIME_SALE_CLASS
            && $policySimulationHistory->service == SuggestPolicies::FIXED_PRICE_DISCOUNT_SERVICE
        ) {
            $result['discount_type'] = 1;
            $result['discount_price'] = $policySimulationHistory->value;
        } elseif (
            $policySimulationHistory->class == SuggestPolicies::TIME_SALE_CLASS
            && $policySimulationHistory->service == SuggestPolicies::FIXED_RATE_DISCOUNT_SERVICE
        ) {
            $result['discount_type'] = 0;
            $result['discount_rate'] = $policySimulationHistory->value;
        }

        if (
            ! is_null($policySimulationHistory->condition_value_2)
            && $policySimulationHistory->condition_value_2 != '全商品'
        ) {
            $result['type_item_url'] = 1;
            $result['item_urls'] = $policySimulationHistory->condition_value_2;
        }

        return $result;
    }

    /**
     * Get data charting the relationship between sales and rate.
     */
    public function chartSalesAndRateByStore(string $storeId)
    {
        return Policy::with('rules')->where('store_id', $storeId)
            ->where('category', Policy::SIMULATION_CATEGORY)
            ->get()
            ->map(function ($item) {
                $startDate = $item['simulation_start_date'];
                $endDate = $item['simulation_end_date'];

                $shopAnalyticsDailyAllValue = ShopAnalyticsDaily::where('store_id', $item->store_id)
                    ->whereRaw("STR_TO_DATE(`date`, '%Y%m%d') >= '{$startDate}'")
                    ->whereRaw("STR_TO_DATE(`date`, '%Y%m%d') <= '{$endDate}'")
                    ->join(
                        'shop_analytics_daily_sales_num as sadsn',
                        'sadsn.sales_num_id',
                        '=',
                        'shop_analytics_daily.sales_num_id'
                    )
                    ->sum('sadsn.all_value');

                return [
                    'policy_value' => $item->rules->sum('value'),
                    'all_value_sum' => $shopAnalyticsDailyAllValue,
                ];
            });
    }
}
