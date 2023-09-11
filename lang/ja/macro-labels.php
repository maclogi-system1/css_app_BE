<?php

return [
    'mq_access_num' => [
        'access_flow_sum' => '広告以外アクセス',
        'search_flow_num' => 'サーチ流入',
        'ranking_flow_num' => 'ランキング流入',
        'instagram_flow_num' => 'Instagram流入',
        'google_flow_num' => 'Google流入',
        'cpc_num' => '運用広告アクセス',
        'display_num' => 'ディスプレイアクセス',
    ],
    'mq_accounting' => [
        'store_id' => '店舗ID',
        'year' => '年次',
        'month' => '月次',
        'mq_kpi_id' => '売上',
        'mq_access_num_id' => 'アクセスID',
        'mq_ad_sales_amnt_id' => '広告経由売上ID',
        'mq_user_trends_id' => '新規・リピータ顧客情報ID',
        'mq_cost_id' => '費用ID',
        'ltv_2y_amnt' => '2年間LTV',
        'lim_cpa' => '限界CPA',
        'cpo_via_ad' => '広告経由CPO',
        'create_at' => '作成日時',
        'updated_at' => '更新日時',
        'csv_usage_fee' => 'CSV利用料',
        'store_opening_fee' => '出店料',
        'fixed_cost' => '固定費',
    ],
    'mq_ad_sales_amnt' => [
        'sales_amnt_via_ad' => '広告経由売上※TDA除く',
        'sales_amnt_seasonal' => 'シーズナル広告売上',
        'sales_amnt_event' => 'イベント広告売上',
        'tda_access_num' => 'アクセス',
        'tda_v_sales_amnt' => 'V売上',
        'tda_v_roas' => 'VROAS',
    ],
    'mq_cost' => [
        'coupon_points_cost' => '販促費(クーポン・ポイント・アフィリエイト）',
        'coupon_points_cost_rate' => '販促費率',
        'ad_cost' => '広告費合計',
        'ad_cpc_cost' => '運用型広告',
        'ad_season_cost' => 'シーズナル広告',
        'ad_event_cost' => 'イベント広告',
        'ad_tda_cost' => 'TDA広告',
        'ad_cost_rate' => '広告費率',
        'cost_price' => '原価',
        'cost_price_rate' => '原価率',
        'postage' => '送料',
        'postage_rate' => '送料費率',
        'commision' => '手数料',
        'commision_rate' => '手数料率',
        'variable_cost_sum' => '変動費合計',
        'gross_profit' => '粗利益額',
        'gross_profit_rate' => '粗利益率',
        'management_agency_fee' => '運営代行費',
        'reserve1' => '予備１',
        'reserve2' => '予備２',
        'management_agency_fee_rate' => '比率',
        'cost_sum' => '合計',
        'profit' => '損益',
        'sum_profit' => '損益累計',
    ],
    'mq_kpi' => [
        'sales_amnt' => '売上',
        'sales_num' => '売上件数',
        'access_num' => 'アクセス',
        'conversion_rate' => '転換率（％）',
        'sales_amnt_per_user' => '客単価',
    ],
    'mq_user_trends' => [
        'new_sales_amnt' => '売上',
        'new_sales_num' => '売上件数',
        'new_price_per_user' => '客単価',
        're_sales_amnt' => '売上',
        're_sales_num' => '売上件数',
        're_price_per_user' => '客単価',
    ],
    'policies' => [
        'store_id' => '店舗ID',
        'job_group_id' => 'IDジョブグループ',
        'single_job_id' => 'IDジョブ',
        'name' => '施策名',
        'category' => '施策種別',
        'immediate_reflection' => '即時反映',
        'simulation_start_date' => '開始日時',
        'simulation_end_date' => '利用可能販促費',
        'simulation_store_priority' => '店舗優先',
        'simulation_product_priority' => '商品優先',
    ],
    'policy_rules' => [
        'class' => '施策種別',
        'service' => '施策サービス',
        'value' => '施策値',
        'condition_1' => '適用条件1',
        'condition_value_1' => '適用条件値1',
        'condition_2' => '適用条件2',
        'condition_value_2' => '適用条件値2',
        'condition_3' => '適用条件3',
        'condition_value_3' => '適用条件値3',
    ],
    'shop_setting_award_points' => [
        'store_id' => '店舗ID',
        'purchase_date' => '購入日付',
        'order_number' => '注文番号',
        'points_awarded' => 'ポイント付与数',
    ],
    'shop_setting_rankings' => [
        'store_id' => '店舗ID',
        'merchandise_control_number' => '商品管理番号',
        'directory_id' => 'ディレクトリID',
    ],
    'shop_setting_search_rankings' => [
        'store_id' => '店舗ID',
        'merchandise_control_number' => '商品管理番号',
        'keyword_1' => 'キーワード1',
        'keyword_2' => 'キーワード2',
        'keyword_3' => 'キーワード3',
    ]
];