<?php

namespace App\Repositories\Eloquents;

use App\Models\MqAccessNum;
use App\Models\MqAccounting;
use App\Models\MqAdSalesAmnt;
use App\Models\MqCost;
use App\Models\MqKpi;
use App\Models\MqUserTrend;
use App\Repositories\Contracts\MqAccountingRepository as MqAccountingRepositoryContract;
use App\Repositories\Repository;
use App\Services\AI\MqAccountingService;
use App\Support\MqAccountingCsv;
use App\Support\Traits\HasMqDateTimeHandler;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class MqAccountingRepository extends Repository implements MqAccountingRepositoryContract
{
    use HasMqDateTimeHandler;

    protected array $validationRules = [
        'sales_amnt'                => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sales_num'                 => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'access_num'                => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'conversion_rate'           => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'sales_amnt_per_user'       => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'access_flow_sum'           => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'search_flow_num'           => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ranking_flow_num'          => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'instagram_flow_num'        => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'google_flow_num'           => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'cpc_num'                   => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'display_num'               => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sales_amnt_via_ad'         => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sales_amnt_seasonal'       => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sales_amnt_event'          => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'tda_access_num'            => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'tda_v_sales_amnt'          => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'tda_v_roas'                => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'new_sales_amnt'            => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'new_sales_num'             => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'new_price_per_user'        => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        're_sales_amnt'             => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        're_sales_num'              => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        're_price_per_user'         => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'coupon_points_cost'        => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'coupon_points_cost_rate'   => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'ad_cost'                   => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_cpc_cost'               => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_season_cost'            => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_event_cost'             => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_tda_cost'               => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_cost_rate'              => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'cost_price'                => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'cost_price_rate'           => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'postage'                   => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'postage_rate'              => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'commision'                 => ['nullable', 'integer', 'between:-999999,999999'],
        'commision_rate'            => ['nullable', 'decimal:0,6', 'between:-2000000000,2000000000'],
        'gross_profit'              => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'gross_profit_rate'         => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'variable_cost_sum'         => ['nullable'],
        'management_agency_fee'     => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'reserve1'                  => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'reserve2'                  => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'csv_usage_fee'             => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'store_opening_fee'         => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'management_agency_fee_rate'=> ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'fixed_cost'                => ['nullable'],
        'profit'                    => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sum_profit'                => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ltv_2y_amnt'               => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'lim_cpa'                   => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'cpo_via_ad'                => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'cost_sum'                  => ['nullable'],
    ];

    public function __construct(
        protected MqAccountingService $mqAccountingService
    ) {}

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MqAccounting::class;
    }

    /**
     * Get a list of items that can be shown.
     */
    public function getShowableRows(): array
    {
        return array_keys($this->validationRules);
    }

    /**
     * Get a list of validation rules for validator
     */
    public function getValidationRules() : array
    {
        return $this->validationRules +
            [
                'year'  => ['required', 'integer', 'max:' . now()->addYear()->year, 'min:' . now()->subYear(2)->year],
                'month' => ['required', 'integer', 'min:1', 'max:12'],
            ];
    }

    /**
     * Handle data validation to update mq_accounting.
     */
    public function handleValidationUpdate($data, $storeId): array
    {
        $validator = Validator::make($data, $this->getValidationRules());

        if ($validator->fails()) {
            return [
                'error' => [
                    'store_id' => $storeId,
                    'year' => Arr::get($data, 'year'),
                    'month' => Arr::get($data, 'month'),
                    'messages' => $validator->getMessageBag(),
                ],
            ];
        }

        return [
            'data' => $validator->validated(),
        ];
    }

    /**
     * Get mq_accounting details by storeId.
     */
    public function getListByStore(string $storeId, array $filter = []): ?Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filter);

        return $this->useWith(['mqKpi', 'mqAccessNum', 'mqAdSalesAmnt', 'mqUserTrends', 'mqCost'])
            ->useScope(['dateRange' => [$dateRangeFilter['from_date'], $dateRangeFilter['to_date']]])
            ->queryBuilder()
            ->where('store_id', $storeId)
            ->get()
            ->map(function ($item) {
                $item->fixed_cost = is_null($item->fixed_cost)
                    ? $item->mqCost?->cost_sum + ($item->csv_usage_fee ?? 0) + ($item->store_opening_fee ?? 0)
                    : $item->fixed_cost;
                $item->mqCost->variable_cost_sum = is_null($item->mqCost?->variable_cost_sum)
                    ? ($item->mqCost?->coupon_points_cost ?? 0)
                        + ($item->mqCost?->ad_cost ?? 0)
                        + ($item->mqCost?->cost_price ?? 0)
                        + ($item->mqCost?->postage ?? 0)
                        + ($item->mqCost?->commision ?? 0)
                    : $item->mqCost?->variable_cost_sum;

                return $item;
            });
    }

    /**
     * Get mq_accounting details from AI by storeId.
     */
    public function getListFromAIByStore(string $storeId, array $filter = []): ?array
    {
        return $this->mqAccountingService->getListByStore($storeId, $filter);
    }

    /**
     * Return a callback handle stream csv file.
     */
    public function streamCsvFile(array $filter = [], ?string $storeId = ''): Closure
    {
        $mqAccounting = collect();
        $dateRangeFilter = $this->getDateRangeFilter($filter);
        $dateRange = $this->getDateTimeRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date']);
        $options = Arr::get($filter, 'options', []);

        if ($storeId) {
            $mqAccounting = $this->getListByStore($storeId, $filter);
        }

        return function () use ($mqAccounting, $options, $dateRange) {
            $file = fopen('php://output', 'w');
            $mqAccountingCsv = new MqAccountingCsv();
            $mqAccountingRows = $mqAccountingCsv->makeRowsCsvFile($mqAccounting, $dateRange, $options);

            foreach ($mqAccountingRows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };
    }

    /**
     * Read and parse csv file contents.
     */
    public function readAndParseCsvFileContents(array $rows)
    {
        $data = [];
        $mqAccountingCsv = new MqAccountingCsv($rows);

        for ($column = 2; $column < count($rows[0]); $column++) {
            $mqKpi = $mqAccountingCsv->getDataMqKpi($column);
            $mqAccessNum = $mqAccountingCsv->getDataMqAccessNum($column);
            $mqAdSalesAmnt = $mqAccountingCsv->getDataMqAdSalesAmnt($column);
            $mqUserTrends = $mqAccountingCsv->getDataMqUserTrends($column);
            $mqCost = $mqAccountingCsv->getDataMqCost($column);
            $tmpData = [
                'year' => intval(str_replace('年', '', $rows[0][$column])),
                'month' => intval(str_replace('月', '', $rows[1][$column])),
                'store_opening_fee' => $this->removeStrangeCharacters($rows[46][$column]),
                'csv_usage_fee' => $this->removeStrangeCharacters($rows[47][$column]),
                'ltv_2y_amnt' => $this->removeStrangeCharacters($rows[52][$column]),
                'lim_cpa' => $this->removeStrangeCharacters($rows[53][$column]),
                'cpo_via_ad' => $this->removeStrangeCharacters($rows[54][$column]),
            ];
            if (! empty($mqKpi)) {
                $tmpData = array_merge($tmpData, $mqKpi);
            }

            if (! empty($mqAccessNum)) {
                $tmpData = array_merge($tmpData, $mqAccessNum);
            }

            if (! empty($mqAdSalesAmnt)) {
                $tmpData = array_merge($tmpData, $mqAdSalesAmnt);
            }

            if (! empty($mqUserTrends)) {
                $tmpData = array_merge($tmpData, $mqUserTrends);
            }

            if (! empty($mqCost)) {
                $tmpData = array_merge($tmpData, $mqCost);
            }

            $data[] = $tmpData;
        }

        return $data;
    }

    /**
     * Remove strange characters in csv file content to save to database.
     */
    protected function removeStrangeCharacters($value)
    {
        return ! is_null($value) ? str_replace([',', '%', ' '], ['', '', ''], $value) : null;
    }

    /**
     * Update an existing model or create a new model.
     */
    public function updateOrCreate(array $rows, $storeId): ?MqAccounting
    {
        return $this->handleSafely(function () use ($rows, $storeId) {
            $year = $rows['year'];
            $month = $rows['month'];

            $mqAccounting = MqAccounting::where('store_id', $storeId)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            $kpi = MqKpi::updateOrCreate([
                'id' => $mqAccounting?->mq_kpi_id,
            ], $rows['mq_kpi']);
            $accessNum = MqAccessNum::updateOrCreate([
                'id' => $mqAccounting?->mq_access_num_id,
            ], $rows['mq_access_num']);
            $adSalesAmnt = MqAdSalesAmnt::updateOrCreate([
                'id' => $mqAccounting?->mq_ad_sales_amnt_id
            ], $rows['mq_ad_sales_amnt']);
            $userTrends = MqUserTrend::updateOrCreate([
                'id' => $mqAccounting?->mq_user_trends_id
            ], $rows['mq_user_trends']);

            $cost = $this->updateOrCreateMqCost($rows['mq_cost'], $mqAccounting?->mq_cost_id);

            Arr::set($rows, 'fixed_cost', $cost->cost_sum
                + Arr::get($rows, 'csv_usage_fee', $mqAccounting?->csv_usage_fee ?? 0)
                + Arr::get($rows, 'store_opening_fee', $mqAccounting?->store_opening_fee ?? 0));

            if (is_null($mqAccounting)) {
                $mqAccounting = new MqAccounting();
                $mqAccounting->forceFill([
                    'store_id' => $storeId,
                    'year' => $year,
                    'month' => $month,
                    'mq_kpi_id' => $kpi->id,
                    'mq_access_num_id' => $accessNum->id,
                    'mq_ad_sales_amnt_id' => $adSalesAmnt->id,
                    'mq_user_trends_id' => $userTrends->id,
                    'mq_cost_id' => $cost->id,
                ]);
            }

            $mqAccounting->forceFill(Arr::only($rows, [
                'ltv_2y_amnt',
                'lim_cpa',
                'cpo_via_ad',
                'csv_usage_fee',
                'store_opening_fee',
                'fixed_cost',
            ]))->save();

            return $mqAccounting;
        }, 'Update or create mq accounting');
    }

    /**
     * Update an existing mq_cost or create a new mq_cost.
     */
    private function updateOrCreateMqCost(array $data, ?string $mqCostId = null): MqCost
    {
        if (! is_null($mqCostId)) {
            $cost = MqCost::find($mqCostId);
        }

        $variableCostSum = Arr::get($data, 'coupon_points_cost', $cost?->coupon_points_cost ?? 0)
            + Arr::get($data, 'ad_cost', $cost?->ad_cost ?? 0)
            + Arr::get($data, 'cost_price', $cost?->cost_price ?? 0)
            + Arr::get($data, 'postage', $cost?->postage ?? 0)
            + Arr::get($data, 'commision', $cost?->commision ?? 0);
        $costSum = Arr::get($data, 'management_agency_fee', $cost?->management_agency_fee ?? 0)
            + Arr::get($data, 'reserve1', $cost?->reserve1 ?? 0)
            + Arr::get($data, 'reserve2', $cost?->reserve2 ?? 0);
        Arr::set($data, 'variable_cost_sum', $variableCostSum);
        Arr::set($data, 'cost_sum', $costSum);

        return MqCost::updateOrCreate([
            'id' => $mqCostId,
        ], $data);
    }

    /**
     * Read and parse data for update.
     */
    public function getDataForUpdate(array $data): array
    {
        $rows = Arr::only($data, [
            'year',
            'month',
            'ltv_2y_amnt',
            'lim_cpa',
            'cpo_via_ad',
            'csv_usage_fee',
            'store_opening_fee',
        ]);
        $kpi = Arr::only($data, (new MqKpi())->getFillable());
        $accessNum = Arr::only($data, (new MqAccessNum())->getFillable());
        $adSalesAmnt = Arr::only($data, (new MqAdSalesAmnt())->getFillable());
        $userTrends = Arr::only($data, (new MqUserTrend())->getFillable());
        $cost = Arr::only($data, (new MqCost())->getFillable());

        $rows['mq_kpi'] = $kpi;
        $rows['mq_access_num'] = $accessNum;
        $rows['mq_ad_sales_amnt'] = $adSalesAmnt;
        $rows['mq_user_trends'] = $userTrends;
        $rows['mq_cost'] = $cost;

        return $rows;
    }

    /**
     * Get total sale amount, cost and profit by store id.
     */
    public function getTotalParamByStore(string $storeId, array $filter = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filter);

        $query = $this->useScope(['dateRange' => [$dateRangeFilter['from_date'], $dateRangeFilter['to_date']]])
            ->queryBuilder()
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->selectRaw("
                store_id,
                sum(mk.sales_amnt) as sales_amnt_total,
                sum(mq_accounting.fixed_cost) as cost_sum_total,
                sum(mc.variable_cost_sum) as variable_cost_sum_total,
                sum(mc.profit) as profit_total
            ")
            ->groupBy('mq_accounting.store_id')
            ->where('store_id', $storeId);

        return $query->get();
    }

    /**
     * Get forecast vs actual.
     */
    public function getForecastVsActual(string $storeId, array $filter = []): array
    {
        $dateRangeFilter = $this->getDateRangeFilter($filter);

        $expected = $this->useScope([
                'dateRange' => [
                    $dateRangeFilter['from_date'],
                    $dateRangeFilter['to_date'],
                ]
            ])
            ->queryBuilder()
            ->where('store_id', $storeId)
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->select('mk.sales_amnt', 'mc.profit')
            ->get()
            ->reduce(function ($pre, $item) {
                return [
                    'sales_amnt' => $pre['sales_amnt'] + $item->sales_amnt,
                    'profit' => $pre['profit'] + $item->profit,
                ];
            }, [
                'sales_amnt' => 0,
                'profit' => 0,
            ]);
        $actual = $this->mqAccountingService->getForecastVsActual($storeId, $filter);
        $salesAmntRate = $expected['sales_amnt']
            ? ($actual['sales_amnt'] - $expected['sales_amnt']) * 100 / $expected['sales_amnt']
            : 0;
        $profitRate = $expected['profit']
            ? ($actual['profit'] - $expected['profit']) * 100 / $expected['profit']
            : 0;

        return [
            'sales_amnt_rate' => round($salesAmntRate, 2),
            'profit_rate' => round($profitRate, 2),
            'actual' => $actual,
            'expected' => $expected,
        ];
    }
}
