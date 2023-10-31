<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Arr;

class MqAccountingCsv
{
    public function __construct(
        protected array $rows = []
    ) {
    }

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
        if (in_array('access_flow_sum', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['アクセス内訳', '広告以外アクセス'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->access_flow_sum;
            }, $dateRange));
        }
        if (in_array('search_flow_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗サーチ流入'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->search_flow_num;
            }, $dateRange));
        }
        if (in_array('ranking_flow_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗ランキング流入'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->ranking_flow_num;
            }, $dateRange));
        }
        if (in_array('instagram_flow_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗Instagram流入'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->instagram_flow_num;
            }, $dateRange));
        }
        if (in_array('google_flow_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗Google流入'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->google_flow_num;
            }, $dateRange));
        }
        if (in_array('cpc_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '運用広告アクセス'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->cpc_num;
            }, $dateRange));
        }
        if (in_array('display_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', 'ディスプレイアクセス'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAccessNum?->display_num;
            }, $dateRange));
        }

        // Add lines for mq_ad_sales_amnt.
        if (in_array('sales_amnt_via_ad', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['広告経由売上内訳', '広告経由売上※TDA除く'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_via_ad;
            }, $dateRange));
        }
        if (in_array('sales_amnt_seasonal', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗シーズナル広告売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_seasonal;
            }, $dateRange));
        }
        if (in_array('sales_amnt_event', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '┗イベント広告売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->sales_amnt_event;
            }, $dateRange));
        }
        if (in_array('tda_access_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['TDA', 'アクセス'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_access_num;
            }, $dateRange));
        }
        if (in_array('tda_v_sales_amnt', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', 'V売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_v_sales_amnt;
            }, $dateRange));
        }
        if (in_array('tda_v_roas', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', 'VROAS'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqAdSalesAmnt?->tda_v_roas;
            }, $dateRange));
        }

        // Add lines for mq_user_trends.
        if (in_array('new_sales_amnt', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['新規', '売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->new_sales_amnt;
            }, $dateRange));
        }
        if (in_array('new_sales_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->new_sales_num;
            }, $dateRange));
        }
        if (in_array('new_price_per_user', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->new_price_per_user;
            }, $dateRange));
        }
        if (in_array('re_sales_amnt', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['リピート', '売上'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->re_sales_amnt;
            }, $dateRange));
        }
        if (in_array('re_sales_num', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '売上件数'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->re_sales_num;
            }, $dateRange));
        }
        if (in_array('re_price_per_user', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '客単価'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqUserTrends?->re_price_per_user;
            }, $dateRange));
        }

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
        if (in_array('coupon_points_cost_rate', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '販促費率'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->coupon_points_cost_rate;
            }, $dateRange));
        }
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
        if (in_array('cost_price', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '原価'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->cost_price;
            }, $dateRange));
        }
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
        if (in_array('variable_cost_sum', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['合計', ''], function ($month, $year) use ($mqAccounting) {
                $item = $mqAccounting->where('month', $month)->where('year', $year)->first();
                $mqCost = $item?->mqCost;

                return is_null($mqCost?->variable_cost_sum)
                    ? ($mqCost?->coupon_points_cost ?? 0)
                        + ($mqCost?->ad_cost ?? 0)
                        + ($mqCost?->cost_price ?? 0)
                        + ($mqCost?->postage ?? 0)
                        + ($mqCost?->commision ?? 0)
                    : ($mqCost?->variable_cost_sum ?? 0);
            }, $dateRange));
        }
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
        if (in_array('store_opening_fee', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '出店料'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->store_opening_fee;
            }, $dateRange));
        }
        if (in_array('csv_usage_fee', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', 'CSV利用料'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->csv_usage_fee;
            }, $dateRange));
        }
        if (in_array('management_agency_fee_rate', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['', '比率'], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->mqCost?->management_agency_fee_rate;
            }, $dateRange));
        }
        if (in_array('fixed_cost', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['合計', ''], function ($month, $year) use ($mqAccounting) {
                $item = $mqAccounting->where('month', $month)->where('year', $year)->first();

                return is_null($item?->fixed_cost)
                    ? ($item?->mqCost?->cost_sum ?? 0)
                        + ($item?->store_opening_fee ?? 0)
                        + ($item?->csv_usage_fee ?? 0)
                    : ($item?->fixed_cost ?? 0);
            }, $dateRange));
        }
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
        if (in_array('ltv_2y_amnt', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['2年間LTV', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->ltv_2y_amnt;
            }, $dateRange));
        }
        if (in_array('lim_cpa', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['限界CPA', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->lim_cpa;
            }, $dateRange));
        }
        if (in_array('cpo_via_ad', $options)) {
            $this->rows[] = convert_fields_to_sjis($this->makeRowCsvFile(['広告経由CPO', ''], function ($month, $year) use ($mqAccounting) {
                return $mqAccounting->where('month', $month)->where('year', $year)->first()?->cpo_via_ad;
            }, $dateRange));
        }

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

    /**
     * Get a list comparing the actual values with the expected values.
     */
    public function compareActualsWithExpectedValues(array $actual, array $expected, bool $isFraction = false): array
    {
        $difference = [];

        foreach ($expected as $index => $item) {
            $difference[] = [
                'year' => Arr::get($item, 'year'),
                'month' => Arr::get($item, 'month'),
                'ltv_2y_amnt' => $this->getTheDifferenceRatio(Arr::get($item, 'ltv_2y_amnt', 0), Arr::get($actual, $index.'.ltv_2y_amnt', 0), $isFraction),
                'lim_cpa' => $this->getTheDifferenceRatio(Arr::get($item, 'lim_cpa', 0), Arr::get($actual, $index.'.lim_cpa', 0), $isFraction),
                'cpo_via_ad' => $this->getTheDifferenceRatio(Arr::get($item, 'cpo_via_ad', 0), Arr::get($actual, $index.'.cpo_via_ad', 0), $isFraction),
                'csv_usage_fee' => $this->getTheDifferenceRatio(Arr::get($item, 'csv_usage_fee', 0), Arr::get($actual, $index.'.csv_usage_fee', 0), $isFraction),
                'store_opening_fee' => $this->getTheDifferenceRatio(Arr::get($item, 'store_opening_fee', 0), Arr::get($actual, $index.'.store_opening_fee', 0), $isFraction),
                'fixed_cost' => $this->getTheDifferenceRatio(Arr::get($item, 'fixed_cost', 0), Arr::get($actual, $index.'.fixed_cost', 0), $isFraction),
                'mq_kpi' => [
                    'sales_amnt' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_kpi.sales_amnt', 0), Arr::get($actual, $index.'.mq_kpi.sales_amnt', 0), $isFraction),
                    'sales_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_kpi.sales_num', 0), Arr::get($actual, $index.'.mq_kpi.sales_num', 0), $isFraction),
                    'access_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_kpi.access_num', 0), Arr::get($actual, $index.'.mq_kpi.access_num', 0), $isFraction),
                    'conversion_rate' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_kpi.conversion_rate', 0), Arr::get($actual, $index.'.mq_kpi.conversion_rate', 0), $isFraction),
                    'sales_amnt_per_user' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_kpi.sales_amnt_per_user', 0), Arr::get($actual, $index.'.mq_kpi.sales_amnt_per_user', 0), $isFraction),
                ],
                'mq_access_num' => [
                    'access_flow_sum' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_access_num.access_flow_sum', 0), Arr::get($actual, $index.'.mq_access_num.access_flow_sum', 0), $isFraction),
                    'search_flow_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_access_num.search_flow_num', 0), Arr::get($actual, $index.'.mq_access_num.search_flow_num', 0), $isFraction),
                    'ranking_flow_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_access_num.ranking_flow_num', 0), Arr::get($actual, $index.'.mq_access_num.ranking_flow_num', 0), $isFraction),
                    'instagram_flow_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_access_num.instagram_flow_num', 0), Arr::get($actual, $index.'.mq_access_num.instagram_flow_num', 0), $isFraction),
                    'google_flow_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_access_num.google_flow_num', 0), Arr::get($actual, $index.'.mq_access_num.google_flow_num', 0), $isFraction),
                    'cpc_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_access_num.cpc_num', 0), Arr::get($actual, $index.'.mq_access_num.cpc_num', 0), $isFraction),
                    'display_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_access_num.display_num', 0), Arr::get($actual, $index.'.mq_access_num.display_num', 0), $isFraction),
                ],
                'mq_ad_sales_amnt' => [
                    'sales_amnt_via_ad' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_ad_sales_amnt.sales_amnt_via_ad', 0), Arr::get($actual, $index.'.mq_ad_sales_amnt.sales_amnt_via_ad', 0), $isFraction),
                    'sales_amnt_seasonal' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_ad_sales_amnt.sales_amnt_seasonal', 0), Arr::get($actual, $index.'.mq_ad_sales_amnt.sales_amnt_seasonal', 0), $isFraction),
                    'sales_amnt_event' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_ad_sales_amnt.sales_amnt_event', 0), Arr::get($actual, $index.'.mq_ad_sales_amnt.sales_amnt_event', 0), $isFraction),
                    'tda_access_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_ad_sales_amnt.tda_access_num', 0), Arr::get($actual, $index.'.mq_ad_sales_amnt.tda_access_num', 0), $isFraction),
                    'tda_v_sales_amnt' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_ad_sales_amnt.tda_v_sales_amnt', 0), Arr::get($actual, $index.'.mq_ad_sales_amnt.tda_v_sales_amnt', 0), $isFraction),
                    'tda_v_roas' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_ad_sales_amnt.tda_v_roas', 0), Arr::get($actual, $index.'.mq_ad_sales_amnt.tda_v_roas', 0, $isFraction)),
                ],
                'mq_user_trends' => [
                    'new_sales_amnt' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_user_trends.new_sales_amnt', 0), Arr::get($actual, $index.'.mq_user_trends.new_sales_amnt', 0), $isFraction),
                    'new_sales_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_user_trends.new_sales_num', 0), Arr::get($actual, $index.'.mq_user_trends.new_sales_num', 0), $isFraction),
                    'new_price_per_user' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_user_trends.new_price_per_user', 0), Arr::get($actual, $index.'.mq_user_trends.new_price_per_user', 0), $isFraction),
                    're_sales_amnt' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_user_trends.re_sales_amnt', 0), Arr::get($actual, $index.'.mq_user_trends.re_sales_amnt', 0), $isFraction),
                    're_sales_num' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_user_trends.re_sales_num', 0), Arr::get($actual, $index.'.mq_user_trends.re_sales_num', 0), $isFraction),
                    're_price_per_user' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_user_trends.re_price_per_user', 0), Arr::get($actual, $index.'.mq_user_trends.re_price_per_user', 0), $isFraction),
                ],
                'mq_cost' => [
                    'coupon_points_cost' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.coupon_points_cost', 0), Arr::get($actual, $index.'.mq_cost.coupon_points_cost', 0), $isFraction),
                    'coupon_points_cost_rate' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.coupon_points_cost_rate', 0), Arr::get($actual, $index.'.mq_cost.coupon_points_cost_rate', 0), $isFraction),
                    'ad_cost' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.ad_cost', 0), Arr::get($actual, $index.'.mq_cost.ad_cost', 0), $isFraction),
                    'ad_cpc_cost' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.ad_cpc_cost', 0), Arr::get($actual, $index.'.mq_cost.ad_cpc_cost', 0), $isFraction),
                    'ad_season_cost' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.ad_season_cost', 0), Arr::get($actual, $index.'.mq_cost.ad_season_cost', 0), $isFraction),
                    'ad_event_cost' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.ad_event_cost', 0), Arr::get($actual, $index.'.mq_cost.ad_event_cost', 0), $isFraction),
                    'ad_tda_cost' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.ad_tda_cost', 0), Arr::get($actual, $index.'.mq_cost.ad_tda_cost', 0), $isFraction),
                    'ad_cost_rate' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.ad_cost_rate', 0), Arr::get($actual, $index.'.mq_cost.ad_cost_rate', 0), $isFraction),
                    'cost_price' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.cost_price', 0), Arr::get($actual, $index.'.mq_cost.cost_price', 0), $isFraction),
                    'cost_price_rate' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.cost_price_rate', 0), Arr::get($actual, $index.'.mq_cost.cost_price_rate', 0), $isFraction),
                    'postage' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.postage', 0), Arr::get($actual, $index.'.mq_cost.postage', 0), $isFraction),
                    'postage_rate' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.postage_rate', 0), Arr::get($actual, $index.'.mq_cost.postage_rate', 0), $isFraction),
                    'commision' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.commision', 0), Arr::get($actual, $index.'.mq_cost.commision', 0), $isFraction),
                    'commision_rate' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.commision_rate', 0), Arr::get($actual, $index.'.mq_cost.commision_rate', 0), $isFraction),
                    'variable_cost_sum' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.variable_cost_sum', 0), Arr::get($actual, $index.'.mq_cost.variable_cost_sum', 0), $isFraction),
                    'gross_profit' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.gross_profit', 0), Arr::get($actual, $index.'.mq_cost.gross_profit', 0), $isFraction),
                    'gross_profit_rate' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.gross_profit_rate', 0), Arr::get($actual, $index.'.mq_cost.gross_profit_rate', 0), $isFraction),
                    'management_agency_fee' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.management_agency_fee', 0), Arr::get($actual, $index.'.mq_cost.management_agency_fee', 0), $isFraction),
                    'reserve1' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.reserve1', 0), Arr::get($actual, $index.'.mq_cost.reserve1', 0), $isFraction),
                    'reserve2' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.reserve2', 0), Arr::get($actual, $index.'.mq_cost.reserve2', 0), $isFraction),
                    'management_agency_fee_rate' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.management_agency_fee_rate', 0), Arr::get($actual, $index.'.mq_cost.management_agency_fee_rate', 0), $isFraction),
                    'cost_sum' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.cost_sum', 0), Arr::get($actual, $index.'.mq_cost.cost_sum', 0), $isFraction),
                    'profit' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.profit', 0), Arr::get($actual, $index.'.mq_cost.profit', 0), $isFraction),
                    'sum_profit' => $this->getTheDifferenceRatio(Arr::get($item, 'mq_cost.sum_profit', 0), Arr::get($actual, $index.'.mq_cost.sum_profit', 0, $isFraction)),
                ],
            ];
        }

        return $difference;
    }

    /**
     * Calculate the difference between expected and actual.
     */
    private function getTheDifferenceRatio($expected, $actual, bool $isFraction = false)
    {
        if ($isFraction) {
            return $actual ? round($expected / $actual, 4) : 0;
        }

        return $actual - $expected;
    }
}
