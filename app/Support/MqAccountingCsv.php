<?php

namespace App\Support;

use Illuminate\Support\Arr;

class MqAccountingCsv
{
    public function __construct(
        protected array $rows
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
}
