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

class MqAccountingRepository extends Repository implements MqAccountingRepositoryContract
{
    public const SHOWABLE_ROWS = [
       'sales_amnt',
       'sales_num',
       'access_num',
       'conversion_rate',
       'sales_amnt_per_user',
       'coupon_points_cost',
       'ad_cost',
       'ad_cpc_cost',
       'ad_season_cost',
       'ad_event_cost',
       'ad_tda_cost',
       'ad_cost_rate',
       'cost_price_rate',
       'postage',
       'postage_rate',
       'commision',
       'commision_rate',
       'gross_profit',
       'gross_profit_rate',
       'management_agency_fee',
       'reserve1',
       'reserve2',
       'management_agency_fee_rate',
       'profit',
       'sum_profit',
    ];

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
     * Get a list of items that can be shown.
     */
    public function getShowableRows(): array
    {
        return static::SHOWABLE_ROWS;
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
                        $query->where('year', '>=', $this->checkAndGetYearForFilter($fromDate->year))
                            ->where('month', '>=', $fromDate->month);
                    })
                    ->where(function ($query) use ($toDate) {
                        $query->where('year', '<=', $this->checkAndGetYearForFilter($toDate->year))
                            ->where('month', '<=', $toDate->month);
                    });
            }
        } else {
            if ($year = Arr::get($filter, 'year')) {
                $query->where('year', $this->checkAndGetYearForFilter($year));
            } else {
                $query->where('year', '>=', now()->subYear(2)->year)
                    ->where('year', '<=', now()->addYear()->year);
            }

            if ($month = Arr::get($filter, 'month')) {
                $query->where('month', $month);
            }
        }


        return $query->get();
    }

    /**
     * Check for filter by year, filter only year not less than current by 2 years
     * and year not more than present by 1 year.
     */
    private function checkAndGetYearForFilter($year): int
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
        $mqAccounting = null;
        $fromDate = Carbon::create($filter['from_date']);
        $toDate = Carbon::create($filter['to_date']);
        $dateRange = $this->getDateTimeRange($fromDate, $toDate);
        $options = Arr::get($filter, 'options', []);

        if ($storeId) {
            $mqAccounting = $this->getListByStore($storeId, $filter);
        }

        return function () use ($mqAccounting, $options, $dateRange) {
            $file = fopen('php://output', 'w');

            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['項目', '詳細項目'], fn ($month, $year) => $year.'年', $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', ''], fn ($month) => $month.'月', $dateRange)));

            // Add lines for mq_kpi.
            if (in_array('sales_amnt', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['売上の公式', '売上'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->sales_amnt;
                }, $dateRange)));
            }
            if (in_array('sales_num', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->sales_num;
                }, $dateRange)));
            }
            if (in_array('access_num', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', 'アクセス'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->access_num;
                }, $dateRange)));
            }
            if (in_array('conversion_rate', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '転換率（％）'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->conversion_rate;
                }, $dateRange)));
            }
            if (in_array('sales_amnt_per_user', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->sales_amnt_per_user;
                }, $dateRange)));
            }

            // Add lines for mq_access_num.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['アクセス内訳', '広告以外アクセス'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->access_flow_sum;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗サーチ流入'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->search_flow_num;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗ランキング流入'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->ranking_flow_num;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗Instagram流入'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->instagram_flow_num;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗Google流入'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->google_flow_num;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '運用広告アクセス'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->cpc_num;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', 'ディスプレイアクセス'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->display_num;
            }, $dateRange)));

            // Add lines for mq_ad_sales_amnt.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['広告経由売上内訳', '広告経由売上※TDA除く'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_via_ad;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗シーズナル広告売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_seasonal;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗イベント広告売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_event;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['TDA', 'アクセス'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_access_num;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', 'V売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_v_sales_amnt;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', 'VROAS'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_v_roas;
            }, $dateRange)));

            // Add lines for mq_user_trends.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['新規', '売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->new_sales_amnt;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->new_sales_num;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->new_price_per_user;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['リピート', '売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->re_sales_amnt;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->re_sales_num;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->re_price_per_user;
            }, $dateRange)));

            // Add lines for mq_cost.
            if (in_array('coupon_points_cost', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(
                    ['変動費', '販促費(クーポン・ポイント・アフィリエイト）'],
                    function ($month, $year) use ($mqAccounting) {
                        return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->coupon_points_cost;
                    },
                    $dateRange
                )));
            }
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '販促費率'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->coupon_points_cost_rate;
            }, $dateRange)));
            if (in_array('ad_cost', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '広告費合計'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_cost;
                }, $dateRange)));
            }
            if (in_array('ad_cpc_cost', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗運用型広告'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_cpc_cost;
                }, $dateRange)));
            }
            if (in_array('ad_season_cost', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗シーズナル広告'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_season_cost;
                }, $dateRange)));
            }
            if (in_array('ad_event_cost', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗イベント広告'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_event_cost;
                }, $dateRange)));
            }
            if (in_array('ad_tda_cost', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '┗TDA広告'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_tda_cost;
                }, $dateRange)));
            }
            if (in_array('ad_cost_rate', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '広告費率'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_cost_rate;
                }, $dateRange)));
            }
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '原価'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->cost_price;
            }, $dateRange)));
            if (in_array('cost_price_rate', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '原価率'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->cost_price_rate;
                }, $dateRange)));
            }
            if (in_array('postage', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '送料'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->postage;
                }, $dateRange)));
            }
            if (in_array('postage_rate', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '送料費率'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->postage_rate;
                }, $dateRange)));
            }
            if (in_array('commision', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '手数料'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->commision;
                }, $dateRange)));
            }
            if (in_array('commision_rate', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '手数料率'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->commision_rate;
                }, $dateRange)));
            }
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['合計', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->variable_cost_sum;
            }, $dateRange)));
            if (in_array('gross_profit', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['粗利益', '粗利益額'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->gross_profit;
                }, $dateRange)));
            }
            if (in_array('gross_profit_rate', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '粗利益率'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->gross_profit_rate;
                }, $dateRange)));
            }
            if (in_array('management_agency_fee', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['固定費', '運営代行費'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->management_agency_fee;
                }, $dateRange)));
            }
            if (in_array('reserve1', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '予備'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->reserve1;
                }, $dateRange)));
            }
            if (in_array('reserve2', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '予備'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->reserve2;
                }, $dateRange)));
            }
            if (in_array('management_agency_fee_rate', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['', '比率'], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->management_agency_fee_rate;
                }, $dateRange)));
            }
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['合計', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->cost_sum;
            }, $dateRange)));
            if (in_array('profit', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['損益', ''], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->profit;
                }, $dateRange)));
            }
            if (in_array('sum_profit', $options)) {
                fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['損益累計', ''], function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->sum_profit;
                }, $dateRange)));
            }

            // Add lines for mq_accounting.
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['2年間LTV', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->ltv_2y_amnt;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['限界CPA', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->lim_cpa;
            }, $dateRange)));
            fputcsv($file, convert_fields_to_sjis($this->makeRowCsvFile(['広告経由CPO', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->cpo_via_ad;
            }, $dateRange)));

            fclose($file);
        };
    }

    private function getDateTimeRange($fromDate, $toDate)
    {
        $period = new CarbonPeriod($fromDate, '1 month', $toDate);
        $result = [];

        foreach ($period as $dateTime) {
            $result[] = $dateTime->format('Y-m');
        }

        return $result;
    }

    /**
     * Make row for csv file.
     *
     * @param  array  $row
     * @param  Closure  $callback (fn ($month, $year) => ([...]))
     * @return array
     */
    private function makeRowCsvFile(array $row, Closure $callback, array $dateRange = []): array
    {
        $additionalColumns = [];

        if (empty($dateRange)) {
            for ($month = 1; $month <= 12; $month++) {
                $additionalColumns[] = $callback($month, now()->year);
            }
        } else {
            foreach ($dateRange as $yearMonth) {
                [$year, $month] = explode('-', $yearMonth);
                $additionalColumns[] = $callback($month, $year);
            }
        }


        return array_merge($row, $additionalColumns);
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
                'ltv_2y_amnt' => $this->removeStrangeCharacters($rows[50][$column]),
                'lim_cpa' => $this->removeStrangeCharacters($rows[51][$column]),
                'cpo_via_ad' => $this->removeStrangeCharacters($rows[52][$column]),
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
            $cost = MqCost::updateOrCreate([
                'id' => $mqAccounting?->mq_cost_id
            ], $rows['mq_cost']);

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

            $mqAccounting->forceFill(Arr::only($rows, ['ltv_2y_amnt', 'lim_cpa', 'cpo_via_ad']))->save();

            return $mqAccounting;
        }, 'Update or create mq accounting');
    }

    /**
     * Read and parse data for update.
     */
    public function getDataForUpdate(array $data): array
    {
        $rows = Arr::only($data, ['year', 'month', 'ltv_2y_amnt', 'lim_cpa', 'cpo_via_ad']);
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
}
