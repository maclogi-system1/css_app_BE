<?php

namespace Database\Seeders;

use App\Models\MqAccessNum;
use App\Models\MqAccounting;
use App\Models\MqAdSalesAmnt;
use App\Models\MqCost;
use App\Models\MqKpi;
use App\Models\MqUserTrend;
use Illuminate\Database\Seeder;

class MqAccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment('local', 'development')) {
            $kpi = MqKpi::create(['sales_amnt' => 10085484, 'sales_num' => 3034, 'access_num' => 45575, 'conversion_rate' => 0.000666, 'sales_amnt_per_user' => 3324]);
            MqKpi::create(['sales_amnt' => 7803984, 'sales_num' => 2503, 'access_num' => 38493, 'conversion_rate' => 0.00065, 'sales_amnt_per_user' => 3118]);

            $accessNum = MqAccessNum::create([
                'access_flow_sum' => 34422, 'search_flow_num' => 0, 'ranking_flow_num' => 0,
                'instagram_flow_num' => 0, 'google_flow_num' => 0, 'cpc_num' => 9250, 'display_num' => 1903,
            ]);
            MqAccessNum::create([
                'access_flow_sum' => 32908, 'search_flow_num' => 0, 'ranking_flow_num' => 0,
                'instagram_flow_num' => 0, 'google_flow_num' => 0, 'cpc_num' => 5585, 'display_num' => 0,
            ]);

            $adSalesAmnt = MqAdSalesAmnt::create([
                'sales_amnt_via_ad' => 745601, 'sales_amnt_seasonal' => 871918, 'sales_amnt_event' => 0,
                'tda_access_num' => 0, 'tda_v_sales_amnt' => 0, 'tda_v_roas' => null,
            ]);
            MqAdSalesAmnt::create([
                'sales_amnt_via_ad' => 300649, 'sales_amnt_seasonal' => 0, 'sales_amnt_event' => 0,
                'tda_access_num' => 119, 'tda_v_sales_amnt' => 187546, 'tda_v_roas' => 911.13,
            ]);

            $userTrends = MqUserTrend::create([
                'new_sales_amnt' => 5030067, 'new_sales_num' => 1805, 'new_price_per_user' => 2787,
                're_sales_amnt' => 5055417, 're_sales_num' => 1229, 're_price_per_user' => 4113,
            ]);
            MqUserTrend::create([
                'new_sales_amnt' => 4023226, 'new_sales_num' => 1544, 'new_price_per_user' => 2606,
                're_sales_amnt' => 3780758, 're_sales_num' => 959, 're_price_per_user' => 3942,
            ]);


            $cost = MqCost::create([
                'coupon_points_cost' => 40243, 'coupon_points_cost_rate' => 0.004, 'ad_cost' => 420000,
                'ad_cpc_cost' =>  320000, 'ad_season_cost' => 100000, 'ad_event_cost' => 0, 'ad_tda_cost' => 0,
                'ad_cost_rate' => 0.0416, 'cost_price' => 2521371, 'cost_price_rate' => 0.25, 'postage' => 1517000,
                'postage_rate' => 0.1504, 'commision' => 1008548, 'commision_rate' => 0.1,
                'variable_cost_sum' => 5507162, 'gross_profit' => 4578322, 'gross_profit_rate' => 0.00454,
                'management_agency_fee' => 500000, 'management_agency_fee_rate' =>  4.96, 'cost_sum' => 500000,
                'profit' => 4078322, 'sum_profit' => 4078322,
            ]);
            MqCost::create([
                'coupon_points_cost' => 42929, 'coupon_points_cost_rate' => 0.0055, 'ad_cost' => 289895,
                'ad_cpc_cost' =>  269311, 'ad_season_cost' => 0, 'ad_event_cost' => 0, 'ad_tda_cost' => 20584,
                'ad_cost_rate' => 0.0371, 'cost_price' => 1950996, 'cost_price_rate' => 0.25, 'postage' => 1251500,
                'postage_rate' => 0.1604, 'commision' => 780398, 'commision_rate' => 0.1,
                'variable_cost_sum' => 4315718, 'gross_profit' => 3488266, 'gross_profit_rate' => 0.00447,
                'management_agency_fee' => 500000, 'management_agency_fee_rate' =>  6.41, 'cost_sum' => 500000,
                'profit' => 2988266, 'sum_profit' => 2988266,
            ]);

            MqAccounting::create([
                'store_id' => 'store_1', 'year' => '2023', 'month' => '3', 'mq_kpi_id' => $kpi->id,
                'mq_access_num_id' => $accessNum->id, 'mq_ad_sales_amnt_id' => $adSalesAmnt->id,
                'mq_user_trends_id' => $userTrends->id, 'mq_cost_id' => $cost->id, 'ltv_2y_amnt' => 0, 'lim_cpa' => 0, 'cpo_via_ad' => 859,
            ]);
        }
    }
}
