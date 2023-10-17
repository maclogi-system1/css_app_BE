<?php

namespace App\Repositories\Eloquents;

use App\Models\MqAccessNum;
use App\Models\MqAccounting;
use App\Models\MqAdSalesAmnt;
use App\Models\MqCost;
use App\Models\MqKpi;
use App\Models\MqSheet;
use App\Models\MqUserTrend;
use App\Repositories\Contracts\MqAccountingRepository as MqAccountingRepositoryContract;
use App\Repositories\Repository;
use App\Support\MqAccountingCsv;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\AI\MqAccountingService;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MqAccountingRepository extends Repository implements MqAccountingRepositoryContract
{
    use HasMqDateTimeHandler;

    protected array $validationRules = [
        'sales_amnt' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sales_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'access_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'conversion_rate' => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'sales_amnt_per_user' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'access_flow_sum' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'search_flow_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ranking_flow_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'instagram_flow_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'google_flow_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'cpc_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'display_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sales_amnt_via_ad' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sales_amnt_seasonal' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sales_amnt_event' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'tda_access_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'tda_v_sales_amnt' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'tda_v_roas' => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'new_sales_amnt' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'new_sales_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'new_price_per_user' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        're_sales_amnt' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        're_sales_num' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        're_price_per_user' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'coupon_points_cost' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'coupon_points_cost_rate' => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'ad_cost' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_cpc_cost' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_season_cost' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_event_cost' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_tda_cost' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ad_cost_rate' => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'cost_price' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'cost_price_rate' => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'postage' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'postage_rate' => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'commision' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'commision_rate' => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'gross_profit' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'gross_profit_rate' => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'variable_cost_sum' => ['nullable'],
        'management_agency_fee' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'reserve1' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'reserve2' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'csv_usage_fee' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'store_opening_fee' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'management_agency_fee_rate' => ['nullable', 'decimal:0,6', 'between:-999999,999999'],
        'fixed_cost' => ['nullable'],
        'profit' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'sum_profit' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'ltv_2y_amnt' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'lim_cpa' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'cpo_via_ad' => ['nullable', 'integer', 'between:-2000000000,2000000000'],
        'cost_sum' => ['nullable'],
    ];

    public function __construct(
        protected MqAccountingService $mqAccountingService
    ) {
    }

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
     * Get a list of validation rules for validator.
     */
    public function getValidationRules(string $storeId): array
    {
        return $this->validationRules +
            [
                'year' => ['required', 'integer', 'max:'.now()->addYear()->year, 'min:'.now()->subYear(2)->year],
                'month' => ['required', 'integer', 'min:1', 'max:12'],
                'mq_sheet_id' => [
                    'required',
                    'string',
                    'max:36',
                    Rule::exists('mq_sheets', 'id')->where(function ($query) use ($storeId) {
                        return $query->where('store_id', $storeId);
                    }),
                ],
            ];
    }

    /**
     * Handle data validation to update mq_accounting.
     */
    public function handleValidationUpdate($data, $storeId): array
    {
        $validator = Validator::make($data, $this->getValidationRules($storeId));

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
    public function getListByStore(string $storeId, array $filters = []): ?Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $mqSheetId = Arr::get($filters, 'mq_sheet_id');

        return $this->useWith(['mqKpi', 'mqAccessNum', 'mqAdSalesAmnt', 'mqUserTrends', 'mqCost', 'mqSheet'])
            ->useScope(['dateRange' => [$dateRangeFilter['from_date'], $dateRangeFilter['to_date']]])
            ->queryBuilder()
            ->where('store_id', $storeId)
            ->where('mq_sheet_id', $mqSheetId)
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
    public function getListFromAIByStore(string $storeId, array $filters = []): ?array
    {
        return $this->mqAccountingService->getListByStore($storeId, $filters);
    }

    /**
     * Get a list comparing the actual values with the expected values.
     */
    public function getListCompareActualsWithExpectedValues(string $storeId, array $filters = []): array
    {
        $actualMqAccounting = $this->getListFromAIByStore($storeId, $filters);
        $expectedMqAccounting = $this->getListByStore($storeId, $filters)->toArray();
        $difference = (new MqAccountingCsv())->compareActualsWithExpectedValues(
            $actualMqAccounting,
            $expectedMqAccounting
        );

        return [
            'actual_mq_accounting' => $actualMqAccounting,
            'expected_mq_accounting' => $expectedMqAccounting,
            'difference' => $difference,
        ];
    }

    /**
     * Return a callback handle stream csv file.
     */
    public function streamCsvFile(array $filters = [], ?string $storeId = ''): Closure
    {
        $mqAccounting = collect();
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateRange = $this->getDateTimeRange($dateRangeFilter['from_date'], $dateRangeFilter['to_date']);
        $mqSheetId = Arr::get($filters, 'mq_sheet_id');
        $options = Arr::get($filters, 'options', []);

        if ($storeId) {
            if (! $mqSheetId) {
                $mqSheetId = MqSheet::where('store_id', $storeId)->first()?->id;
            }

            $filters['mq_sheet_id'] = $mqSheetId;
            $mqAccounting = $this->getListByStore($storeId, $filters);
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
                'store_opening_fee' => $this->removeStrangeCharacters($rows[44][$column]),
                'csv_usage_fee' => $this->removeStrangeCharacters($rows[45][$column]),
                'ltv_2y_amnt' => $this->removeStrangeCharacters($rows[50][$column]),
                'lim_cpa' => $this->removeStrangeCharacters($rows[51][$column]),
                'cpo_via_ad' => $this->removeStrangeCharacters($rows[52][$column]),
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
            $mqSheetId = $rows['mq_sheet_id'];

            $mqAccounting = MqAccounting::where('store_id', $storeId)
                ->where('mq_sheet_id', $mqSheetId)
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
                'id' => $mqAccounting?->mq_ad_sales_amnt_id,
            ], $rows['mq_ad_sales_amnt']);
            $userTrends = MqUserTrend::updateOrCreate([
                'id' => $mqAccounting?->mq_user_trends_id,
            ], $rows['mq_user_trends']);

            $cost = $this->updateOrCreateMqCost($rows['mq_cost'], $mqAccounting?->mq_cost_id);

            Arr::set($rows, 'fixed_cost', $cost->cost_sum);

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
                'mq_sheet_id',
            ]))->save();

            return $mqAccounting;
        }, 'Update or create mq accounting');
    }

    /**
     * Update an existing mq_cost or create a new mq_cost.
     */
    private function updateOrCreateMqCost(array $data, ?string $mqCostId = null): MqCost
    {
        Arr::set($data, 'reserve1', Arr::get($data, 'store_opening_fee', 0));
        Arr::set($data, 'reserve2', Arr::get($data, 'csv_usage_fee', 0));

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
            'mq_sheet_id',
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
    public function getTotalParamByStore(string $storeId, array $filters = [])
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->toImmutable();
        $toDate = $dateRangeFilter['to_date']->toImmutable();
        $mqAccounting = $this->mqAccountingService->getTotalParamByStore($storeId, $filters);
        $salesAmntTotal = $mqAccounting?->sales_amnt_total ?? 0;
        $costSumTotal = $mqAccounting?->cost_sum_total ?? 0;
        $variableCostSumTotal = $mqAccounting?->variable_cost_sum_total ?? 0;
        $profitTotal = $mqAccounting?->profit_total ?? 0;
        $result = [
            'sales_amnt_total' => $salesAmntTotal,
            'cost_sum_total' => $costSumTotal,
            'variable_cost_sum_total' => $variableCostSumTotal,
            'profit_total' => $profitTotal,
        ];

        $mqAccountingLastMonth = $this->mqAccountingService->getTotalParamByStoreInAMonth(
            $storeId,
            $fromDate->subMonth(),
        );
        $lastMonthSalesAmntTotal = $mqAccountingLastMonth?->sales_amnt_total ?? 0;
        $lastMonthCostSumTotal = $mqAccountingLastMonth?->cost_sum_total ?? 0;
        $lastMonthVariableCostSumTotal = $mqAccountingLastMonth?->variable_cost_sum_total ?? 0;
        $lastMonthProfitTotal = $mqAccountingLastMonth?->profit_total ?? 0;
        $result['sales_amnt_total_compared_to_last_month'] = $lastMonthSalesAmntTotal
            ? round((100 * $salesAmntTotal / $lastMonthSalesAmntTotal) - 100, 2)
            : 0;
        $result['cost_sum_total_compared_to_last_month'] = $lastMonthCostSumTotal
            ? round((100 * $costSumTotal / $lastMonthCostSumTotal) - 100, 2)
            : 0;
        $result['variable_cost_sum_total_compared_to_last_month'] = $lastMonthVariableCostSumTotal
            ? round((100 * $variableCostSumTotal / $lastMonthVariableCostSumTotal) - 100, 2)
            : 0;
        $result['profit_total_compared_to_last_month'] = $lastMonthProfitTotal
            ? round((100 * $profitTotal / $lastMonthProfitTotal) - 100, 2)
            : 0;

        $filters['from_date'] = Arr::get($filters, 'compared_from_date', $fromDate->subYear());
        $filters['to_date'] = Arr::get($filters, 'compared_to_date', $toDate->subYear());
        $mqAccountingLastYear = $this->mqAccountingService->getTotalParamByStore($storeId, $filters);
        $lastYearSalesAmntTotal = $mqAccountingLastYear?->sales_amnt_total ?? 0;
        $lastYearCostSumTotal = $mqAccountingLastYear?->cost_sum_total ?? 0;
        $lastYearVariableCostSumTotal = $mqAccountingLastYear?->variable_cost_sum_total ?? 0;
        $lastYearProfitTotal = $mqAccountingLastYear?->profit_total ?? 0;
        $result['sales_amnt_total_compared_to_last_year'] = $lastYearSalesAmntTotal
            ? round((100 * $salesAmntTotal / $lastYearSalesAmntTotal) - 100, 2)
            : 0;
        $result['cost_sum_total_compared_to_last_year'] = $lastYearCostSumTotal
            ? round((100 * $costSumTotal / $lastYearCostSumTotal) - 100, 2)
            : 0;
        $result['variable_cost_sum_total_compared_to_last_year'] = $lastYearVariableCostSumTotal
            ? round((100 * $variableCostSumTotal / $lastYearVariableCostSumTotal) - 100, 2)
            : 0;
        $result['profit_total_compared_to_last_year'] = $lastYearProfitTotal
            ? round((100 * $profitTotal / $lastYearProfitTotal) - 100, 2)
            : 0;

        $salesAmntTotalThisMonth = $this->getSalesAmntTotalInAMonth($storeId, [
            'mq_sheet_id' => Arr::get($filters, 'mq_sheet_id'),
            'month' => now()->month,
            'year' => now()->year,
        ]);
        $salesAmntTotalLastMonth = $this->getSalesAmntTotalInAMonth($storeId, [
            'mq_sheet_id' => Arr::get($filters, 'mq_sheet_id'),
            'month' => now()->subMonth()->month,
            'year' => now()->year,
        ]);
        $salesAmntTotalLastYear = $this->getSalesAmntTotalInAMonth($storeId, [
            'mq_sheet_id' => Arr::get($filters, 'mq_sheet_id'),
            'month' => now()->month,
            'year' => now()->subYear()->year,
        ]);
        $result['sales_amnt_total_this_month'] = $salesAmntTotalThisMonth;
        $result['sales_amnt_total_last_month'] = $salesAmntTotalLastMonth;
        $result['sales_amnt_total_last_year'] = $salesAmntTotalLastYear;
        $result['sales_amnt_total_this_month_compared_to_last_month'] = $salesAmntTotalLastMonth
            ? round((100 * $salesAmntTotalThisMonth / $salesAmntTotalLastMonth) - 100, 2)
            : 0;
        $result['sales_amnt_total_this_month_compared_to_last_year'] = $salesAmntTotalLastYear
            ? round((100 * $salesAmntTotalThisMonth / $salesAmntTotalLastYear) - 100, 2)
            : 0;

        return $result;
    }

    protected function getSalesAmntTotalInAMonth($storeId, array $filters = [])
    {
        return $this->buidlQueryWithSheetId(Arr::get($filters, 'mq_sheet_id'))
            ->where('mq_accounting.store_id', $storeId)
            ->where('mq_accounting.month', Arr::get($filters, 'month'))
            ->where('mq_accounting.year', Arr::get($filters, 'year'))
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->select(
                DB::raw('SUM(mk.sales_amnt) as sales_amnt'),
            )
            ->first()
            ?->sales_amnt ?? 0;
    }

    /**
     * Get forecast vs actual.
     */
    public function getForecastVsActual(string $storeId, array $filters = []): array
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);

        $expected = $this->useScope([
            'dateRange' => [
                $dateRangeFilter['from_date'],
                $dateRangeFilter['to_date'],
            ],
        ])
            ->buidlQueryWithSheetId(Arr::get($filters, 'mq_sheet_id'))
            ->where('mq_accounting.store_id', $storeId)
            ->join('mq_kpi as mk', 'mk.id', '=', 'mq_accounting.mq_kpi_id')
            ->join('mq_cost as mc', 'mc.id', '=', 'mq_accounting.mq_cost_id')
            ->select(
                DB::raw('SUM(CASE WHEN mk.sales_amnt IS NULL THEN 0 ELSE mk.sales_amnt END) as sales_amnt'),
                DB::raw('SUM(CASE WHEN mc.profit IS NULL THEN 0 ELSE mc.profit END) as profit'),
            )
            ->first();
        $actual = $this->mqAccountingService->getSalesAmountAndProfit($storeId, $filters);
        $actual->sales_amnt ??= 0;
        $actual->profit ??= 0;
        $salesAmntRate = $expected->sales_amnt
            ? ($actual->sales_amnt - $expected->sales_amnt) * 100 / $expected->sales_amnt
            : 0;
        $profitRate = $expected->profit
            ? ($actual->profit - $expected->profit) * 100 / $expected->profit
            : 0;

        return [
            'sales_amnt_rate' => round($salesAmntRate, 2),
            'profit_rate' => round($profitRate, 2),
            'actual' => $actual,
            'expected' => $expected,
        ];
    }

    /**
     * Build the query with mq_sheet_id.
     */
    protected function buidlQueryWithSheetId(?string $mqSheetId): Builder
    {
        $query = $this->queryBuilder();

        if ($mqSheetId) {
            $query->join('mq_sheets as ms', 'ms.id', '=', 'mq_accounting.mq_sheet_id')
                ->where('mq_accounting.mq_sheet_id', $mqSheetId);
        } else {
            $query->join('mq_sheets as ms', 'ms.id', '=', 'mq_accounting.mq_sheet_id')
                ->where('ms.is_default', 1);
        }

        return $query;
    }

    /**
     * Get comparative analysis.
     */
    public function getComparativeAnalysis(string $storeId, array $filters = [])
    {
        $salesAmntAndProfit = $this->mqAccountingService->getSalesAmountAndProfit($storeId, $filters);
        $salesAmntRate = 0;
        $profitRate = 0;
        $salesAmnt = [
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'sales_amnt' => $salesAmntAndProfit->sales_amnt,
        ];
        $profit = [
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'profit' => $salesAmntAndProfit->profit,
        ];
        $comparedSalesAmnt = [];
        $comparedProfit = [];

        if (Arr::has($filters, ['compared_from_date', 'compared_to_date'])) {
            $filters['from_date'] = Arr::get($filters, 'compared_from_date');
            $filters['to_date'] = Arr::get($filters, 'compared_to_date');

            if (! Arr::get($filters, 'to_date') || Arr::get($filters, 'to_date').'-01' > now()->format('Y-m-d')) {
                $filters['to_date'] = now()->format('Y-m-d');
            }

            $comparedSalesAmntAndProfit = $this->mqAccountingService->getSalesAmountAndProfit($storeId, $filters);

            $salesAmntRate = $comparedSalesAmntAndProfit->sales_amnt
                ? ($salesAmntAndProfit->sales_amnt - $comparedSalesAmntAndProfit->sales_amnt) * 100 / $comparedSalesAmntAndProfit->sales_amnt
                : 0;
            $profitRate = $comparedSalesAmntAndProfit->profit
                ? ($salesAmntAndProfit->profit - $comparedSalesAmntAndProfit->profit) * 100 / $comparedSalesAmntAndProfit->profit
                : 0;

            $comparedSalesAmnt = [
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'sales_amnt' => $comparedSalesAmntAndProfit->sales_amnt,
            ];
            $comparedProfit = [
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'profit' => $comparedSalesAmntAndProfit->profit,
            ];
        }

        return [
            'sales_amnt_rate' => round($salesAmntRate, 2),
            'profit_rate' => round($profitRate, 2),
            'sales_amnt' => [$salesAmnt, $comparedSalesAmnt],
            'profit' => [$profit, $comparedProfit],
        ];
    }

    /**
     * Handle creating default mq_accounting.
     */
    public function makeDefaultData(string $storeId, MqSheet $mqSheet, array $defaultData = []): void
    {
        $dateRange = $this->getDateTimeRange(now()->firstOfYear(), now()->endOfYear());

        foreach ($dateRange as $yearMonth) {
            $setting = Arr::first(Arr::where($defaultData, function ($item) use ($yearMonth) {
                return Carbon::create($item['date'])->format('Y-m') == $yearMonth;
            }));

            $this->createDefaultData($yearMonth, $storeId, $mqSheet, $setting);
        }

        $anotherData = Arr::where($defaultData, function ($item) {
            return Carbon::create($item['date'])->year != now()->year;
        });

        foreach ($anotherData as $data) {
            $this->createDefaultData(Carbon::create($data)->format('Y-m'), $storeId, $mqSheet, $data);
        }
    }

    /**
     * Handles the creation of a new mq_accounting along with its relationships.
     */
    protected function createDefaultData($yearMonth, $storeId, $mqSheet, $setting): ?MqAccounting
    {
        return $this->handleSafely(function () use ($yearMonth, $storeId, $mqSheet, $setting) {
            [$year, $month] = explode('-', $yearMonth);
            $dataMqCost = [];
            $dataMqAccounting = [];

            if (
                ! empty($setting)
                && $yearMonth == Carbon::create(Arr::get($setting, 'date'))->format('Y-m')
            ) {
                $dataMqCost = [
                    'management_agency_fee' => Arr::get($setting, 'estimated_management_agency_expenses'),
                    'cost_price_rate' => Arr::get($setting, 'estimated_cost_rate'),
                    'management_agency_fee' => Arr::get($setting, 'estimated_shipping_fee'),
                    'commision_rate' => Arr::get($setting, 'estimated_commission_rate'),
                    'reserve1' => Arr::get($setting, 'estimated_store_opening_fee'),
                    'reserve2' => Arr::get($setting, 'estimated_csv_usage_fee'),
                ];
                $dataMqAccounting = [
                    'csv_usage_fee' => Arr::get($setting, 'estimated_csv_usage_fee'),
                    'store_opening_fee' => Arr::get($setting, 'estimated_store_opening_fee'),
                ];
            }

            $mqKpi = MqKpi::create([]);
            $mqAccessNum = MqAccessNum::create([]);
            $mqAdSalesAmnt = MqAdSalesAmnt::create([]);
            $mqUserTrends = MqUserTrend::create([]);
            $mqCost = MqCost::create($dataMqCost);
            $mqAccounting = $this->model([
                'store_id' => $storeId,
                'year' => $year,
                'month' => $month,
                'mq_kpi_id' => $mqKpi->id,
                'mq_access_num_id' => $mqAccessNum->id,
                'mq_ad_sales_amnt_id' => $mqAdSalesAmnt->id,
                'mq_user_trends_id' => $mqUserTrends->id,
                'mq_cost_id' => $mqCost->id,
                'mq_sheet_id' => $mqSheet->id,
                'created_at' => now(),
                'updated_at' => now(),
            ] + $dataMqAccounting);

            $mqAccounting->save();

            return $mqAccounting;
        }, 'Create default mq accounting');
    }
}
