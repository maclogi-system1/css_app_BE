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
use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MqAccountingRepository extends Repository implements MqAccountingRepositoryContract
{
    protected array $showableRows = [
        'sales_amnt',
        'sales_num',
        'access_num',
        'conversion_rate',
        'sales_amnt_per_user',
        'access_flow_sum',
        'search_flow_num',
        'ranking_flow_num',
        'instagram_flow_num',
        'google_flow_num',
        'cpc_num',
        'display_num',
        'sales_amnt_via_ad',
        'sales_amnt_seasonal',
        'sales_amnt_event',
        'tda_access_num',
        'tda_v_sales_amnt',
        'tda_v_roas',
        'new_sales_amnt',
        'new_sales_num',
        'new_price_per_user',
        're_sales_amnt',
        're_sales_num',
        're_price_per_user',
        'coupon_points_cost',
        'coupon_points_cost_rate',
        'ad_cost',
        'ad_cpc_cost',
        'ad_season_cost',
        'ad_event_cost',
        'ad_tda_cost',
        'ad_cost_rate',
        'cost_price',
        'cost_price_rate',
        'postage',
        'postage_rate',
        'commision',
        'commision_rate',
        'variable_cost_sum',
        'gross_profit',
        'gross_profit_rate',
        'management_agency_fee',
        'reserve1',
        'reserve2',
        'csv_usage_fee',
        'store_opening_fee',
        'management_agency_fee_rate',
        'fixed_cost',
        'profit',
        'sum_profit',
        'ltv_2y_amnt',
        'lim_cpa',
        'cpo_via_ad',
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
        return $this->showableRows;
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
     * Get date range for filter.
     */
    public function getDateRangeFilter(array $filter): array
    {
        $fromDate = Carbon::create(Arr::get($filter, 'from_date', now()->subYears(2)->month(1)->format('Y-m')));
        $fromDate->year($this->checkAndGetYearForFilter($fromDate->year));
        $toDate = Carbon::create(Arr::get($filter, 'to_date', now()->addYear()->month(12)->format('Y-m')));
        $toDate->year($this->checkAndGetYearForFilter($toDate->year));

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    }

    /**
     * Check for filter by year, filter only year not less than current by 2 years
     * and year not more than present by 1 year.
     */
    protected function checkAndGetYearForFilter($year): int
    {
        $year = $year < now()->subYear(2)->year ? now()->subYear(2)->year : $year;
        $year = $year > now()->addYear()->year ? now()->addYear()->year : $year;

        return $year;
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
        $fromDate = Carbon::create(Arr::get($filter, 'from_date', now()->subYears(2)->month(1)->format('Y-m')));
        $toDate = Carbon::create(Arr::get($filter, 'to_date', now()->addYear()->month(12)->format('Y-m')));
        $dateRange = $this->getDateTimeRange($fromDate, $toDate);
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

    protected function getDateTimeRange($fromDate, $toDate)
    {
        $period = new CarbonPeriod($fromDate, '1 month', $toDate);
        $result = [];

        foreach ($period as $dateTime) {
            $result[] = $dateTime->format('Y-m');
        }

        return $result;
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
                'year' => str_replace('年', '', $rows[0][$column]),
                'month' => str_replace('月', '', $rows[1][$column]),
                'store_opening_fee' => $this->removeStrangeCharacters($rows[46][$column]),
                'csv_usage_fee' => $this->removeStrangeCharacters($rows[47][$column]),
                'ltv_2y_amnt' => $this->removeStrangeCharacters($rows[52][$column]),
                'lim_cpa' => $this->removeStrangeCharacters($rows[53][$column]),
                'cpo_via_ad' => $this->removeStrangeCharacters($rows[54][$column]),
            ];
            if (! empty($mqKpi)) {
                $tmpData['mq_kpi'] = $mqKpi;
            }

            if (! empty($mqAccessNum)) {
                $tmpData['mq_access_num'] = $mqAccessNum;
            }

            if (! empty($mqAdSalesAmnt)) {
                $tmpData['mq_ad_sales_amnt'] = $mqAdSalesAmnt;
            }

            if (! empty($mqUserTrends)) {
                $tmpData['mq_user_trends'] = $mqUserTrends;
            }

            if (! empty($mqCost)) {
                $tmpData['mq_cost'] = $mqCost;
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
