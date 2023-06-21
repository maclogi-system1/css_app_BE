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
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MqAccountingRepository extends Repository implements MqAccountingRepositoryContract
{
    public function __construct(
        private MqAccountingService $mqAccountingService
    ) {}

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MqAccounting::class;
    }

    /**
     * Get mq_accounting details by storeId.
     */
    public function getListByStore(string $storeId, array $filter = []): ?Collection
    {
        $this->useWith(['mqKpi', 'mqAccessNum', 'mqAdSalesAmnt', 'mqUserTrends', 'mqCost']);
        $query = $this->queryBuilder()->where('store_id', $storeId);
        $fromDate = Carbon::create(Arr::get($filter, 'from_date'));
        $toDate = Carbon::create(Arr::get($filter, 'to_date'));

        if (Arr::has($filter, ['from_date', 'to_date'])) {
            $fromDate = Carbon::create($filter['from_date']);
            $toDate = Carbon::create($filter['to_date']);

            if ($fromDate < $toDate) {
                $query->where(function ($query) use ($fromDate) {
                        $query->where('year', '>=', $fromDate->year)
                            ->where('month', '>=', $fromDate->month);
                    })
                    ->where(function ($query) use ($toDate) {
                        $query->where('year', '<=', $toDate->year)
                            ->where('month', '<=', $toDate->month);
                    });
            }
        } else {
            if ($year = Arr::get($filter, 'year')) {
                $query->where('year', $year);
            }

            if ($month = Arr::get($filter, 'month')) {
                $query->where('month', $month);
            }
        }


        return $query->get();
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
        $mqAccounting = null;
        $year = Arr::get($filter, 'year', now()->year);

        if ($storeId) {
            $mqAccounting = $this->getListByStore($storeId, ['year' => $year]);
        }

        return function () use ($mqAccounting, $year) {
            $file = fopen('php://output', 'w');

            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['項目', '詳細項目'], fn () => $year.'年')));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', ''], fn ($i) => $i.'月')));

            // Add lines for mq_kpi.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['売上の公式', '売上'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqKpi?->sales_amnt;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqKpi?->sales_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', 'アクセス'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqKpi?->access_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '転換率（％）'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqKpi?->conversion_rate;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqKpi?->sales_amnt_per_user;
            })));

            // Add lines for mq_access_num.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['アクセス内訳', '広告以外アクセス'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAccessNum?->access_flow_sum;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗サーチ流入'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAccessNum?->search_flow_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗ランキング流入'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAccessNum?->ranking_flow_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗Instagram流入'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAccessNum?->instagram_flow_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗Google流入'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAccessNum?->google_flow_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '運用広告アクセス'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAccessNum?->cpc_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', 'ディスプレイアクセス'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAccessNum?->display_num;
            })));

            // Add lines for mq_ad_sales_amnt.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['広告経由売上内訳', '広告経由売上※TDA除く'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_via_ad;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗シーズナル広告売上'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_seasonal;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗イベント広告売上'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_event;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['TDA', 'アクセス'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_access_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', 'V売上'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_v_sales_amnt;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', 'VROAS'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_v_roas;
            })));

            // Add lines for mq_user_trends.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['新規', '売上'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqUserTrends?->new_sales_amnt;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqUserTrends?->new_sales_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqUserTrends?->new_price_per_user;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['リピート', '売上'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqUserTrends?->re_sales_amnt;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqUserTrends?->re_sales_num;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqUserTrends?->re_price_per_user;
            })));

            // Add lines for mq_cost.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(
                ['変動費', '販促費(クーポン・ポイント・アフィリエイト）'],
                function ($i) use ($mqAccounting, $year) {
                    return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->coupon_points_cost;
                }
            )));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '販促費率'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->coupon_points_cost_rate;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '広告費合計'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->ad_cost;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗運用型広告'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->ad_cpc_cost;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗シーズナル広告'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->ad_season_cost;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗イベント広告'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->ad_event_cost;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗TDA広告'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->ad_tda_cost;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '広告費率'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->ad_cost_rate;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '原価'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->cost_price;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '原価率'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->cost_price_rate;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '送料'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->postage;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '送料費率'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->postage_rate;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '手数料'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->commision;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '手数料率'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->commision_rate;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['合計', ''], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->variable_cost_sum;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['粗利益', '粗利益額'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->gross_profit;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '粗利益率'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->gross_profit_rate;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['固定費', '運営代行費'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->management_agency_fee;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '予備'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->reserve1;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '予備'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->reserve2;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '比率'], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->management_agency_fee_rate;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['合計', ''], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->cost_sum;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['損益', ''], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->profit;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['損益累計', ''], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->mqCost?->sum_profit;
            })));

            // Add lines for mq_accounting.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['2年間LTV', ''], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->ltv_2y_amnt;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['限界CPA', ''], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->lim_cpa;
            })));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['広告経由CPO', ''], function ($i) use ($mqAccounting, $year) {
                return $mqAccounting->where('month', $i)->where('year', $year)->first()?->cpo_via_ad;
            })));

            fclose($file);
        };
    }

    /**
     * Make row for csv file.
     */
    private function makeRowCsvFile(array $row, Closure $callback): array
    {
        $additionalColumns = [];

        for ($i = 1; $i <= 12; $i++) {
            $additionalColumns[] = $callback($i);
        }

        return array_merge($row, $additionalColumns);
    }

    /**
     * Read and parse csv file contents.
     */
    public function readAndParseCsvFileContents(array $rows)
    {
        $data = [];

        for ($i = 2; $i < count($rows[0]); $i++) {
            $data[] = [
                'year' => str_replace('年', '', $rows[0][$i]),
                'month' => str_replace('月', '', $rows[1][$i]),
                'mq_kpi' => [
                    'sales_amnt' => $this->removeStrangeCharacters($rows[2][$i]),
                    'sales_num' => $this->removeStrangeCharacters($rows[3][$i]),
                    'access_num' => $this->removeStrangeCharacters($rows[4][$i]),
                    'conversion_rate' => $this->removeStrangeCharacters($rows[5][$i]),
                    'sales_amnt_per_user' => $this->removeStrangeCharacters($rows[6][$i]),
                ],
                'mq_access_num' => [
                    'access_flow_sum' => $this->removeStrangeCharacters($rows[7][$i]),
                    'search_flow_num' => $this->removeStrangeCharacters($rows[8][$i]),
                    'ranking_flow_num' => $this->removeStrangeCharacters($rows[9][$i]),
                    'instagram_flow_num' => $this->removeStrangeCharacters($rows[10][$i]),
                    'google_flow_num' => $this->removeStrangeCharacters($rows[11][$i]),
                    'cpc_num' => $this->removeStrangeCharacters($rows[12][$i]),
                    'display_num' => $this->removeStrangeCharacters($rows[13][$i]),
                ],
                'mq_ad_sales_amnt' => [
                    'sales_amnt_via_ad' => $this->removeStrangeCharacters($rows[14][$i]),
                    'sales_amnt_seasonal' => $this->removeStrangeCharacters($rows[15][$i]),
                    'sales_amnt_event' => $this->removeStrangeCharacters($rows[16][$i]),
                    'tda_access_num' => $this->removeStrangeCharacters($rows[17][$i]),
                    'tda_v_sales_amnt' => $this->removeStrangeCharacters($rows[18][$i]),
                    'tda_v_roas' => $this->removeStrangeCharacters($rows[19][$i]),
                ],
                'mq_user_trends' => [
                    'new_sales_amnt' => $this->removeStrangeCharacters($rows[20][$i]),
                    'new_sales_num' => $this->removeStrangeCharacters($rows[21][$i]),
                    'new_price_per_user' => $this->removeStrangeCharacters($rows[22][$i]),
                    're_sales_amnt' => $this->removeStrangeCharacters($rows[23][$i]),
                    're_sales_num' => $this->removeStrangeCharacters($rows[24][$i]),
                    're_price_per_user' => $this->removeStrangeCharacters($rows[25][$i]),
                ],

                'mq_cost' => [
                    'coupon_points_cost' => $this->removeStrangeCharacters($rows[26][$i]),
                    'coupon_points_cost_rate' => $this->removeStrangeCharacters($rows[27][$i]),
                    'ad_cost' => $this->removeStrangeCharacters($rows[28][$i]),
                    'ad_cpc_cost' => $this->removeStrangeCharacters($rows[29][$i]),
                    'ad_season_cost' => $this->removeStrangeCharacters($rows[30][$i]),
                    'ad_event_cost' => $this->removeStrangeCharacters($rows[31][$i]),
                    'ad_tda_cost' => $this->removeStrangeCharacters($rows[32][$i]),
                    'ad_cost_rate' => $this->removeStrangeCharacters($rows[33][$i]),
                    'cost_price' => $this->removeStrangeCharacters($rows[34][$i]),
                    'cost_price_rate' => $this->removeStrangeCharacters($rows[35][$i]),
                    'postage' => $this->removeStrangeCharacters($rows[36][$i]),
                    'postage_rate' => $this->removeStrangeCharacters($rows[37][$i]),
                    'commision' => $this->removeStrangeCharacters($rows[38][$i]),
                    'commision_rate' => $this->removeStrangeCharacters($rows[39][$i]),
                    'variable_cost_sum' => $this->removeStrangeCharacters($rows[40][$i]),
                    'gross_profit' => $this->removeStrangeCharacters($rows[41][$i]),
                    'gross_profit_rate' => $this->removeStrangeCharacters($rows[42][$i]),
                    'management_agency_fee' => $this->removeStrangeCharacters($rows[43][$i]),
                    'reserve1' => $this->removeStrangeCharacters($rows[44][$i]),
                    'reserve2' => $this->removeStrangeCharacters($rows[45][$i]),
                    'management_agency_fee_rate' => $this->removeStrangeCharacters($rows[46][$i]),
                    'cost_sum' => $this->removeStrangeCharacters($rows[47][$i]),
                    'profit' => $this->removeStrangeCharacters($rows[48][$i]),
                    'sum_profit' => $this->removeStrangeCharacters($rows[49][$i]),
                ],
                'ltv_2y_amnt' => $this->removeStrangeCharacters($rows[50][$i]),
                'lim_cpa' => $this->removeStrangeCharacters($rows[51][$i]),
                'cpo_via_ad' => $this->removeStrangeCharacters($rows[52][$i]),
            ];
        }
        return $data;
    }

    /**
     * Remove strange characters in csv file content to save to database.
     */
    private function removeStrangeCharacters($value)
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

            $kpi = $this->updateOrCreateKpi($rows['mq_kpi'], $mqAccounting?->mq_kpi_id);
            $accessNum = $this->updateOrCreateAccessNum($rows['mq_access_num'], $mqAccounting?->mq_access_num_id);
            $adSalesAmnt = $this->updateOrCreateAdSalesAmnt($rows['mq_ad_sales_amnt'], $mqAccounting?->mq_ad_sales_amnt_id);
            $userTrends = $this->updateOrCreateUserTrends($rows['mq_user_trends'], $mqAccounting?->mq_user_trends_id);
            $cost = $this->updateOrCreateCost($rows['mq_cost'], $mqAccounting?->mq_cost_id);

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

            $mqAccounting->forceFill([
                'ltv_2y_amnt' => $rows['ltv_2y_amnt'],
                'lim_cpa' => $rows['lim_cpa'],
                'cpo_via_ad' => $rows['cpo_via_ad'],
            ])->save();

            return $mqAccounting;
        }, 'Update or create mq accounting');
    }

    /**
     * Update an existing mp_kpi or create a new mp_kpi.
     */
    private function updateOrCreateKpi($data, ?string $kpiId = null): MqKpi
    {
        $mqKpi = new MqKpi();

        if ($kpiId) {
            $mqKpi = MqKpi::where('id', $kpiId)->first();
        }

        $mqKpi->forceFill($data)->save();

        return $mqKpi;
    }

    /**
     * Update an existing mp_access_num or create a new mp_access_num.
     */
    private function updateOrCreateAccessNum($data, ?string $accessNumId = null): MqAccessNum
    {
        $accessNum = new MqAccessNum();

        if ($accessNumId) {
            $accessNum = MqAccessNum::where('id', $accessNumId)->first();
        }

        $accessNum->forceFill($data)->save();

        return $accessNum;
    }

    /**
     * Update an existing mp_ad_sales_amnt or create a new mp_ad_sales_amnt.
     */
    private function updateOrCreateAdSalesAmnt($data, ?string $adSalesAmntId = null): MqAdSalesAmnt
    {
        $adSalesAmnt = new MqAdSalesAmnt();

        if ($adSalesAmntId) {
            $adSalesAmnt = MqAdSalesAmnt::where('id', $adSalesAmntId)->first();
        }

        $adSalesAmnt->forceFill($data)->save();

        return $adSalesAmnt;
    }

    /**
     * Update an existing mp_user_trends or create a new mp_user_trends.
     */
    private function updateOrCreateUserTrends($data, ?string $userTrendsId = null): MqUserTrend
    {
        $userTrends = new MqUserTrend();

        if ($userTrendsId) {
            $userTrends = MqUserTrend::where('id', $userTrendsId)->first();
        }

        $userTrends->forceFill($data)->save();

        return $userTrends;
    }

    /**
     * Update an existing mp_cost or create a new mp_cost.
     */
    private function updateOrCreateCost($data, ?string $costId = null): MqCost
    {
        $cost = new MqCost();

        if ($costId) {
            $cost = MqCost::where('id', $costId)->first();
        }

        $cost->forceFill($data)->save();

        return $cost;
    }
}
