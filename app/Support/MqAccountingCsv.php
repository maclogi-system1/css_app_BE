<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Arr;

class MqAccountingCsv
{
    public function __construct(
        protected array $rows = []
    ) {}

    public function getDataMqKpi($column)
    {
        return [
            'sales_amnt' => $this->removeStrangeCharacters(Arr::get($this->rows, "2.{$column}")),
            'sales_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "3.{$column}")),
            'access_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "4.{$column}")),
            'conversion_rate' => $this->removeStrangeCharacters(Arr::get($this->rows, "5.{$column}")),
            'sales_amnt_per_user' => $this->removeStrangeCharacters(Arr::get($this->rows, "6.{$column}")),
        ];
    }

    public function getDataMqAccessNum($column)
    {
        return [
            'access_flow_sum' => $this->removeStrangeCharacters(Arr::get($this->rows, "7.{$column}")),
            'search_flow_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "8.{$column}")),
            'ranking_flow_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "9.{$column}")),
            'instagram_flow_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "10.{$column}")),
            'google_flow_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "11.{$column}")),
            'cpc_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "12.{$column}")),
            'display_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "13.{$column}")),
        ];
    }

    public function getDataMqAdSalesAmnt($column)
    {
        return [
            'sales_amnt_via_ad' => $this->removeStrangeCharacters(Arr::get($this->rows, "14.{$column}")),
            'sales_amnt_seasonal' => $this->removeStrangeCharacters(Arr::get($this->rows, "15.{$column}")),
            'sales_amnt_event' => $this->removeStrangeCharacters(Arr::get($this->rows, "16.{$column}")),
            'tda_access_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "17.{$column}")),
            'tda_v_sales_amnt' => $this->removeStrangeCharacters(Arr::get($this->rows, "18.{$column}")),
            'tda_v_roas' => $this->removeStrangeCharacters(Arr::get($this->rows, "19.{$column}")),
        ];
    }

    public function getDataMqUserTrends($column)
    {
        return [
            'new_sales_amnt' => $this->removeStrangeCharacters(Arr::get($this->rows, "20.{$column}")),
            'new_sales_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "21.{$column}")),
            'new_price_per_user' => $this->removeStrangeCharacters(Arr::get($this->rows, "22.{$column}")),
            're_sales_amnt' => $this->removeStrangeCharacters(Arr::get($this->rows, "23.{$column}")),
            're_sales_num' => $this->removeStrangeCharacters(Arr::get($this->rows, "24.{$column}")),
            're_price_per_user' => $this->removeStrangeCharacters(Arr::get($this->rows, "25.{$column}")),
        ];
    }

    public function getDataMqCost($column)
    {
        return [
            'coupon_points_cost' => $this->removeStrangeCharacters(Arr::get($this->rows, "26.{$column}")),
            'coupon_points_cost_rate' => $this->removeStrangeCharacters(Arr::get($this->rows, "27.{$column}")),
            'ad_cost' => $this->removeStrangeCharacters(Arr::get($this->rows, "28.{$column}")),
            'ad_cpc_cost' => $this->removeStrangeCharacters(Arr::get($this->rows, "29.{$column}")),
            'ad_season_cost' => $this->removeStrangeCharacters(Arr::get($this->rows, "30.{$column}")),
            'ad_event_cost' => $this->removeStrangeCharacters(Arr::get($this->rows, "31.{$column}")),
            'ad_tda_cost' => $this->removeStrangeCharacters(Arr::get($this->rows, "32.{$column}")),
            'ad_cost_rate' => $this->removeStrangeCharacters(Arr::get($this->rows, "33.{$column}")),
            'cost_price' => $this->removeStrangeCharacters(Arr::get($this->rows, "34.{$column}")),
            'cost_price_rate' => $this->removeStrangeCharacters(Arr::get($this->rows, "35.{$column}")),
            'postage' => $this->removeStrangeCharacters(Arr::get($this->rows, "36.{$column}")),
            'postage_rate' => $this->removeStrangeCharacters(Arr::get($this->rows, "37.{$column}")),
            'commision' => $this->removeStrangeCharacters(Arr::get($this->rows, "38.{$column}")),
            'commision_rate' => $this->removeStrangeCharacters(Arr::get($this->rows, "39.{$column}")),
            'variable_cost_sum' => $this->removeStrangeCharacters(Arr::get($this->rows, "40.{$column}")),
            'gross_profit' => $this->removeStrangeCharacters(Arr::get($this->rows, "41.{$column}")),
            'gross_profit_rate' => $this->removeStrangeCharacters(Arr::get($this->rows, "42.{$column}")),
            'management_agency_fee' => $this->removeStrangeCharacters(Arr::get($this->rows, "43.{$column}")),
            'reserve1' => $this->removeStrangeCharacters(Arr::get($this->rows, "44.{$column}")),
            'reserve2' => $this->removeStrangeCharacters(Arr::get($this->rows, "45.{$column}")),
            'management_agency_fee_rate' => $this->removeStrangeCharacters(Arr::get($this->rows, "46.{$column}")),
            'cost_sum' => $this->removeStrangeCharacters(Arr::get($this->rows, "47.{$column}")),
            'profit' => $this->removeStrangeCharacters(Arr::get($this->rows, "48.{$column}")),
            'sum_profit' => $this->removeStrangeCharacters(Arr::get($this->rows, "49.{$column}")),
        ];
    }

    /**
     * Remove strange characters in csv file content to save to database.
     */
    public function removeStrangeCharacters($value)
    {
        return ! is_null($value) ? str_replace([',', '%', ' '], ['', '', ''], $value) : null;
    }

    /**
     * Collect, set rows and return that for csv file.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|null  $mqAccounting
     * @param  array  $dateRange
     * @param  array  $options
     * @return array
     */
    public function makeRowsCsvFile($mqAccounting, array $dateRange, array $options = [])
    {
        $this->rows = [
            convert_fields_to_sjis($this->makeRowCsvFile(['項目', '詳細項目'], fn ($month, $year) => $year.'年', $dateRange)),
            convert_fields_to_sjis($this->makeRowCsvFile(['', ''], fn ($month) => $month.'月', $dateRange)),
        ];

        // Add lines for mq_kpi.
        if (in_array('sales_amnt', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['売上の公式', '売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->sales_amnt;
            }, $dateRange));
        }
        if (in_array('sales_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->sales_num;
            }, $dateRange));
        }
        if (in_array('access_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', 'アクセス'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->access_num;
            }, $dateRange));
        }
        if (in_array('conversion_rate', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '転換率（％）'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->conversion_rate;
            }, $dateRange));
        }
        if (in_array('sales_amnt_per_user', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqKpi?->sales_amnt_per_user;
            }, $dateRange));
        }

        // Add lines for mq_access_num.
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['アクセス内訳', '広告以外アクセス'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->access_flow_sum;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗サーチ流入'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->search_flow_num;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗ランキング流入'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->ranking_flow_num;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗Instagram流入'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->instagram_flow_num;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗Google流入'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->google_flow_num;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '運用広告アクセス'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->cpc_num;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', 'ディスプレイアクセス'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->display_num;
        }, $dateRange));

        // Add lines for mq_ad_sales_amnt.
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['広告経由売上内訳', '広告経由売上※TDA除く'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_via_ad;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗シーズナル広告売上'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_seasonal;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗イベント広告売上'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_event;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['TDA', 'アクセス'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_access_num;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', 'V売上'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_v_sales_amnt;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', 'VROAS'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_v_roas;
        }, $dateRange));

        // Add lines for mq_user_trends.
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['新規', '売上'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->new_sales_amnt;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->new_sales_num;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->new_price_per_user;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['リピート', '売上'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->re_sales_amnt;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->re_sales_num;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->re_price_per_user;
        }, $dateRange));

        // Add lines for mq_cost.
        if (in_array('coupon_points_cost', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(
                ['変動費', '販促費(クーポン・ポイント・アフィリエイト）'],
                function ($month, $year) use ($mqAccounting) {
                    return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->coupon_points_cost;
                },
                $dateRange
            ));
        }
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '販促費率'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->coupon_points_cost_rate;
        }, $dateRange));
        if (in_array('ad_cost', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '広告費合計'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_cost;
            }, $dateRange));
        }
        if (in_array('ad_cpc_cost', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗運用型広告'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_cpc_cost;
            }, $dateRange));
        }
        if (in_array('ad_season_cost', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗シーズナル広告'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_season_cost;
            }, $dateRange));
        }
        if (in_array('ad_event_cost', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗イベント広告'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_event_cost;
            }, $dateRange));
        }
        if (in_array('ad_tda_cost', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗TDA広告'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_tda_cost;
            }, $dateRange));
        }
        if (in_array('ad_cost_rate', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '広告費率'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->ad_cost_rate;
            }, $dateRange));
        }
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '原価'], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->cost_price;
        }, $dateRange));
        if (in_array('cost_price_rate', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '原価率'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->cost_price_rate;
            }, $dateRange));
        }
        if (in_array('postage', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '送料'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->postage;
            }, $dateRange));
        }
        if (in_array('postage_rate', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '送料費率'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->postage_rate;
            }, $dateRange));
        }
        if (in_array('commision', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '手数料'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->commision;
            }, $dateRange));
        }
        if (in_array('commision_rate', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '手数料率'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->commision_rate;
            }, $dateRange));
        }
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['合計', ''], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->variable_cost_sum;
        }, $dateRange));
        if (in_array('gross_profit', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['粗利益', '粗利益額'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->gross_profit;
            }, $dateRange));
        }
        if (in_array('gross_profit_rate', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '粗利益率'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->gross_profit_rate;
            }, $dateRange));
        }
        if (in_array('management_agency_fee', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['固定費', '運営代行費'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->management_agency_fee;
            }, $dateRange));
        }
        if (in_array('reserve1', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '予備'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->reserve1;
            }, $dateRange));
        }
        if (in_array('reserve2', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '予備'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->reserve2;
            }, $dateRange));
        }
        if (in_array('management_agency_fee_rate', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '比率'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->management_agency_fee_rate;
            }, $dateRange));
        }
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['合計', ''], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->cost_sum;
        }, $dateRange));
        if (in_array('profit', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['損益', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->profit;
            }, $dateRange));
        }
        if (in_array('sum_profit', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['損益累計', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->sum_profit;
            }, $dateRange));
        }

        // Add lines for mq_accounting.
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['2年間LTV', ''], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->ltv_2y_amnt;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['限界CPA', ''], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->lim_cpa;
        }, $dateRange));
        $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['広告経由CPO', ''], function ($month, $year) use ($mqAccounting) {
            return $mqAccounting->where('month', $month)->where('year', $year)->first()?->cpo_via_ad;
        }, $dateRange));

        return $this->rows;
    }

    /**
     * Make a row for csv file.
     *
     * @param  array  $row
     * @param  Closure  $callback (fn ($month, $year) => ([...]))
     * @return array
     */
    protected function makeRowCsvFile(array $row, Closure $callback, array $dateRange = []): array
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

    public function getRows()
    {
        return $this->rows;
    }
}
