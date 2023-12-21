<?php

namespace App\Constants;

class MacroConstant
{
    public const TYPE_INTERNAL = 'CSS';
    public const TYPE_EXTERNAL = 'OSS';
    public const TYPE_AI_KPI = 'AI_KPI';
    public const TABLE_NAME = 'table_name';
    public const TABLE_TYPE = 'table_type';
    public const TABLE_COLUMNS = 'columns';
    public const RELATIVE_TABLES = 'relative_tables';
    public const RELATIVE_TABLE_FOREIGN_KEY = 'foreign_key';
    public const RELATIVE_TABLE_FOREIGN_KEY_TYPE = 'foreign_key_type';
    public const RELATIVE_TABLE_TYPE_INBOUND = 'relative_table_inbound';
    public const RELATIVE_TABLE_TYPE_OUTBOUND = 'relative_table_outbound';
    public const COLUMN_TYPE = 'column_type';
    public const COLUMN_TYPE_NUMBER = 'number';
    public const COLUMN_TYPE_STRING = 'string';
    public const COLUMN_TYPE_DATETIME = 'datetime';
    public const REMOVE_COLUMNS = 'remove_columns';

    public const MACRO_TYPE_AI_SALES_FORECAST = 0;
    public const MACRO_TYPE_AI_POLICY_RECOMMENDATION = 1;
    public const MACRO_TYPE_POLICY_REGISTRATION = 2;
    public const MACRO_TYPE_TASK_ISSUE = 3;
    public const MACRO_TYPE_ALERT_DISPLAY = 4;
    public const MACRO_TYPE_EXPORT_CSV = 5;
    public const MACRO_TYPE_GRAPH_DISPLAY = 6;
    public const MACRO_TYPES = [
        self::MACRO_TYPE_AI_SALES_FORECAST => 'AI売上予測',
        self::MACRO_TYPE_AI_POLICY_RECOMMENDATION => 'AI施策レコメンド',
        self::MACRO_TYPE_POLICY_REGISTRATION => '施策登録',
        self::MACRO_TYPE_TASK_ISSUE => 'タスク発行',
        self::MACRO_TYPE_ALERT_DISPLAY => 'アラート表示',
        self::MACRO_TYPE_EXPORT_CSV => 'csv出力',
        self::MACRO_TYPE_GRAPH_DISPLAY => 'グラフ表示',
    ];
    public const MACRO_SCHEDULABLE_TYPES = [
        self::MACRO_TYPE_AI_SALES_FORECAST,
        self::MACRO_TYPE_AI_POLICY_RECOMMENDATION,
        self::MACRO_TYPE_POLICY_REGISTRATION,
        self::MACRO_TYPE_TASK_ISSUE,
        self::MACRO_TYPE_ALERT_DISPLAY,
    ];

    /**
     * Descriptions of the tables.
     */
    public const DESCRIPTION_TABLES = [
        'mq_accounting' => [
            self::TABLE_NAME => 'mq_accounting',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'mq_kpi_id',
                'mq_access_num_id',
                'mq_ad_sales_amnt_id',
                'mq_user_trends_id',
                'mq_cost_id',
                'created_at',
                'updated_at',
                'store_id',
                'mq_sheet_id',
            ],
        ],
        'mq_access_num' => [
            self::TABLE_NAME => 'mq_access_num',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
            ],
        ],
        'mq_ad_sales_amnt' => [
            self::TABLE_NAME => 'mq_ad_sales_amnt',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
            ],
        ],
        'mq_user_trends' => [
            self::TABLE_NAME => 'mq_user_trends',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
            ],
        ],
        'mq_cost' => [
            self::TABLE_NAME => 'mq_cost',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
            ],
        ],
        'mq_kpi' => [
            self::TABLE_NAME => 'mq_kpi',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
            ],
        ],
        'policies' => [
            self::TABLE_NAME => 'policies',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'store_id',
                'job_group_id',
                'single_job_id',
                'created_at',
                'updated_at',
                'processing_status',
                'simulation_promotional_expenses',
            ],
        ],
        'policy_attachments' => [
            self::TABLE_NAME => 'policy_attachments',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'policy_id',
                'created_at',
                'updated_at',
            ],
        ],
        'policy_rules' => [
            self::TABLE_NAME => 'policy_rules',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'policy_id',
                'created_at',
                'updated_at',
            ],
        ],
        'projects' => [
            self::TABLE_NAME => 'projects',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'client_id',
                'created_by',
                'deleted_by',
                'created_at',
                'updated_at',
                'deleted_at',
                'status_id',
                'parent_id',
                'mall_type_id',
                'service_type_id',
            ],
        ],
        'job_groups' => [
            self::TABLE_NAME => 'job_groups',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'project_id',
                'milestone_id',
                'status_id',
                'created_by',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
        ],
        'single_jobs' => [
            self::TABLE_NAME => 'single_jobs',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'job_group_id',
                'item_urls',
                'created_at',
                'updated_at',
                'uuid',
                'template_id',
            ],
        ],
        'single_job_templates' => [
            self::TABLE_NAME => 'single_job_templates',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'created_at',
                'updated_at',
            ],
        ],
        'users' => [
            self::TABLE_NAME => 'users',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'email_verified_at',
                'email_verified_token',
                'email_verified_datetime_token',
                'password',
                'set_password',
                'activation_code',
                'created_by',
                'deleted_by',
                'remember_token',
                'image_path',
                'created_at',
                'updated_at',
                'deleted_at',
                'owner_id',
                'owner_type',
            ],
        ],
        'tasks' => [
            self::TABLE_NAME => 'tasks',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'status_id',
                'created_by',
                'deleted_by',
                'created_at',
                'updated_at',
                'deleted_at',
                'estimate_time_type',
                'parent_id',
                'issue_type_id',
                'milestone_id',
                'task_category_id',
                'job_group_id',
                'project_id',
                'single_job_id',
                'auto_type_id',
            ],
        ],
        'task_categories' => [
            self::TABLE_NAME => 'task_categories',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'issue_type_id',
            ],
        ],
        'task_assignees' => [
            self::TABLE_NAME => 'task_assignees',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'task_id',
                'user_id',
            ],
        ],
        'issue_types' => [
            self::TABLE_NAME => 'issue_types',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
            ],
        ],
        'alerts' => [
            self::TABLE_NAME => 'alerts',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'project_id',
                'job_group_id',
                'alert_type_id',
                'created_at',
                'updated_at',
                'deleted_by',
                'deleted_at',
                'job_id',
                'gold_id',
            ],
        ],
        'alert_types' => [
            self::TABLE_NAME => 'alert_types',
            self::TABLE_TYPE => self::TYPE_EXTERNAL,
            self::REMOVE_COLUMNS => [
                'id',
                'created_at',
                'updated_at',
            ],
        ],
        'access_keywords' => [
            self::TABLE_NAME => 'access_keywords',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'access_others' => [
            self::TABLE_NAME => 'access_others',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'others_id',
            ],
        ],
        'access_rakuten' => [
            self::TABLE_NAME => 'access_rakuten',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'rakuten_id',
            ],
        ],
        'access_reference' => [
            self::TABLE_NAME => 'access_reference',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'access_source' => [
            self::TABLE_NAME => 'access_source',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'rakuten_id',
                'others_id',
                'created_at',
            ],
        ],
        'ad_purchase_history' => [
            self::TABLE_NAME => 'ad_purchase_history',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'affiliate_achivement' => [
            self::TABLE_NAME => 'affiliate_achivement',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'coupon_advice_ad' => [
            self::TABLE_NAME => 'coupon_advice_ad',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'daily_rranking' => [
            self::TABLE_NAME => 'daily_rranking',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'item_listprice' => [
            self::TABLE_NAME => 'item_listprice',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'items_analytics' => [
            self::TABLE_NAME => 'items_analytics',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'items_data' => [
            self::TABLE_NAME => 'items_data',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'items_data_all' => [
            self::TABLE_NAME => 'items_data_all',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'items_data_all_id',
            ],
        ],
        'items_data_pc' => [
            self::TABLE_NAME => 'items_data_pc',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'items_data_pc_id',
            ],
        ],
        'items_data_sd_web' => [
            self::TABLE_NAME => 'items_data_sd_web',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'items_data_sd_app_id',
            ],
        ],
        'items_data_sd_app' => [
            self::TABLE_NAME => 'items_data_sd_app',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'items_data_sd_web_id',
            ],
        ],
        'items_sales' => [
            self::TABLE_NAME => 'items_sales',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'keysearch_ranking' => [
            self::TABLE_NAME => 'keysearch_ranking',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'purchase_count' => [
            self::TABLE_NAME => 'purchase_count',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'prchs_cnt_num_id',
                'prchs_cnt_sales_amnt_id',
                'created_at',
            ],
        ],
        'purchase_count_2y' => [
            self::TABLE_NAME => 'purchase_count_2y',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'prchs_cnt_2y_num_id',
                'prchs_cnt_2y_sales_amnt_id',
                'created_at',
            ],
        ],
        'purchase_count_2y_num' => [
            self::TABLE_NAME => 'purchase_count_2y_num',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'prchs_cnt_2y_num_id',
            ],
        ],
        'purchase_count_2y_sales_amnt' => [
            self::TABLE_NAME => 'purchase_count_2y_sales_amnt',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'prchs_cnt_2y_sales_amnt_id',
            ],
        ],
        'purchase_count_num' => [
            self::TABLE_NAME => 'purchase_count_num',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'prchs_cnt_num_id',
            ],
        ],
        'purchase_count_sales_amnt' => [
            self::TABLE_NAME => 'purchase_count_sales_amnt',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'prchs_cnt_sales_amnt_id',
            ],
        ],
        'rgroup_ad' => [
            self::TABLE_NAME => 'rgroup_ad',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'sales_amnt_id',
                'created_at',
            ],
        ],
        'rgroup_ad_sales_amnt' => [
            self::TABLE_NAME => 'rgroup_ad_sales_amnt',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'sales_amnt_id',
            ],
        ],
        'rgroup_ad_by_ad' => [
            self::TABLE_NAME => 'rgroup_ad_by_ad',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'group_ad_by_ad_id',
                'store_id',
                'sales_amnt_id',
                'created_at',
            ],
        ],
        'rgroup_ad_by_ad_sales_amnt' => [
            self::TABLE_NAME => 'rgroup_ad_by_ad_sales_amnt',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'sales_amnt_id',
            ],
        ],
        'rpp_actual_amnt' => [
            self::TABLE_NAME => 'rpp_actual_amnt',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'rpp_actual_amnt_id',
            ],
        ],
        'rpp_ad' => [
            self::TABLE_NAME => 'rpp_ad',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'rpp_actual_amnt_id',
                'rpp_sales_amnt_id',
                'rpp_sales_num_id',
                'rpp_cvr_rate_id',
                'rpp_roas_id',
                'rpp_price_per_order_id',
                'created_at',
            ],
        ],
        'rpp_cvr_rate' => [
            self::TABLE_NAME => 'rpp_cvr_rate',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'rpp_cvr_rate_id',
            ],
        ],
        'rpp_price_per_order' => [
            self::TABLE_NAME => 'rpp_price_per_order',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'rpp_price_per_order_id',
            ],
        ],
        'rpp_roas' => [
            self::TABLE_NAME => 'rpp_roas',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'rpp_roas_id',
            ],
        ],
        'rpp_sales_amnt' => [
            self::TABLE_NAME => 'rpp_sales_amnt',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'rpp_sales_amnt_id',
            ],
        ],
        'rpp_sales_num' => [
            self::TABLE_NAME => 'rpp_sales_num',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'rpp_sales_num_id',
            ],
        ],
        'shop_analytics_daily' => [
            self::TABLE_NAME => 'shop_analytics_daily',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'sales_amnt_id',
                'sales_num_id',
                'access_num_id',
                'conversion_rate_id',
                'sales_amnt_per_user_id',
                'created_at',
            ],
        ],
        'shop_analytics_daily_access_num' => [
            self::TABLE_NAME => 'shop_analytics_daily_access_num',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'access_num_id',
            ],
        ],
        'shop_analytics_daily_conversion_rate' => [
            self::TABLE_NAME => 'shop_analytics_daily_conversion_rate',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'conversion_rate_id',
            ],
        ],
        'shop_analytics_daily_sales_amnt' => [
            self::TABLE_NAME => 'shop_analytics_daily_sales_amnt',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'sales_amnt_id',
            ],
        ],
        'shop_analytics_daily_sales_amnt_per_user' => [
            self::TABLE_NAME => 'shop_analytics_daily_sales_amnt_per_user',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'sales_amnt_per_user_id',
            ],
        ],
        'shop_analytics_daily_sales_num' => [
            self::TABLE_NAME => 'shop_analytics_daily_sales_num',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'sales_amnt_id',
            ],
        ],
        'shop_analytics_monthly' => [
            self::TABLE_NAME => 'shop_analytics_monthly',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
                'sales_amnt_id',
                'sales_num_id',
                'access_num_id',
                'conversion_rate_id',
                'sales_amnt_per_user_id',
            ],
        ],
        'shop_analytics_monthly_access_num' => [
            self::TABLE_NAME => 'shop_analytics_monthly_access_num',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'access_num_id',
            ],
        ],
        'shop_analytics_monthly_conversion_rate' => [
            self::TABLE_NAME => 'shop_analytics_monthly_conversion_rate',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'conversion_rate_id',
            ],
        ],
        'shop_analytics_monthly_sales_amnt' => [
            self::TABLE_NAME => 'shop_analytics_monthly_sales_amnt',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'sales_amnt_id',
            ],
        ],
        'shop_analytics_monthly_sales_amnt_per_user' => [
            self::TABLE_NAME => 'shop_analytics_monthly_sales_amnt_per_user',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'sales_amnt_per_user_id',
            ],
        ],
        'shop_analytics_monthly_sales_num' => [
            self::TABLE_NAME => 'shop_analytics_monthly_sales_num',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'sales_num_id',
            ],
        ],
        'tda_ad' => [
            self::TABLE_NAME => 'tda_ad',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
        'user_trends' => [
            self::TABLE_NAME => 'user_trends',
            self::TABLE_TYPE => self::TYPE_AI_KPI,
            self::REMOVE_COLUMNS => [
                'store_id',
                'created_at',
            ],
        ],
    ];

    /**
     * List of tables and relations.
     */
    public const LIST_RELATIVE_TABLE = [
        'mq_accounting' => [
            self::TABLE_NAME => 'mq_accounting',
            self::RELATIVE_TABLES => [
                'mq_access_num' => [
                    self::TABLE_NAME => 'mq_access_num',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'mq_access_num_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'mq_ad_sales_amnt' => [
                    self::TABLE_NAME => 'mq_ad_sales_amnt',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'mq_ad_sales_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'mq_user_trends' => [
                    self::TABLE_NAME => 'mq_user_trends',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'mq_user_trends_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'mq_cost' => [
                    self::TABLE_NAME => 'mq_cost',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'mq_cost_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'mq_kpi' => [
                    self::TABLE_NAME => 'mq_kpi',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'mq_kpi_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'policies' => [
            self::TABLE_NAME => 'policies',
            self::RELATIVE_TABLES => [
                // 'policy_attachments' => [
                //     self::TABLE_NAME => 'policy_attachments',
                //     self::RELATIVE_TABLE_FOREIGN_KEY => 'policy_id',
                //     self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_INBOUND,
                // ],
                'policy_rules' => [
                    self::TABLE_NAME => 'policy_rules',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'policy_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_INBOUND,
                ],
            ],
        ],
        'job_groups' => [
            self::TABLE_NAME => 'job_groups',
            self::RELATIVE_TABLES => [
                'projects' => [
                    self::TABLE_NAME => 'projects',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'project_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'single_jobs' => [
                    self::TABLE_NAME => 'single_jobs',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'job_group_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_INBOUND,
                    self::RELATIVE_TABLES => [
                        [
                            self::TABLE_NAME => 'single_job_templates',
                            self::RELATIVE_TABLE_FOREIGN_KEY => 'template_id',
                            self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                        ],
                    ],
                ],
                'users' => [
                    self::TABLE_NAME => 'users',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'created_by',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'tasks' => [
            self::TABLE_NAME => 'tasks',
            self::RELATIVE_TABLES => [
                'task_categories' => [
                    self::TABLE_NAME => 'task_categories',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'task_category_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'task_assignees' => [
                    self::TABLE_NAME => 'task_assignees',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'task_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_INBOUND,
                    self::RELATIVE_TABLES => [
                        'users' => [
                            self::TABLE_NAME => 'users',
                            self::RELATIVE_TABLE_FOREIGN_KEY => 'user_id',
                            self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                        ],
                    ],
                ],
                'issue_types' => [
                    self::TABLE_NAME => 'issue_types',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'issue_type_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'job_groups' => [
                    self::TABLE_NAME => 'job_groups',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'job_group_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'single_jobs' => [
                    self::TABLE_NAME => 'single_jobs',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'single_job_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'projects' => [
                    self::TABLE_NAME => 'projects',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'project_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'alerts' => [
            self::TABLE_NAME => 'alerts',
            self::RELATIVE_TABLES => [
                'job_groups' => [
                    self::TABLE_NAME => 'job_groups',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'job_group_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'projects' => [
                    self::TABLE_NAME => 'projects',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'project_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'alert_types' => [
                    self::TABLE_NAME => 'alert_types',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'alert_type_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'access_keywords' => [
            self::TABLE_NAME => 'access_keywords',
            self::RELATIVE_TABLES => [],
        ],
        'access_reference' => [
            self::TABLE_NAME => 'access_reference',
            self::RELATIVE_TABLES => [],
        ],
        'access_source' => [
            self::TABLE_NAME => 'access_source',
            self::RELATIVE_TABLES => [
                'access_others' => [
                    self::TABLE_NAME => 'access_others',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'others_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'access_rakuten' => [
                    self::TABLE_NAME => 'access_rakuten',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'rakuten_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'ad_purchase_history' => [
            self::TABLE_NAME => 'ad_purchase_history',
            self::RELATIVE_TABLES => [],
        ],
        'affiliate_achivement' => [
            self::TABLE_NAME => 'affiliate_achivement',
            self::RELATIVE_TABLES => [],
        ],
        'coupon_advice_ad' => [
            self::TABLE_NAME => 'coupon_advice_ad',
            self::RELATIVE_TABLES => [],
        ],
        'daily_rranking' => [
            self::TABLE_NAME => 'daily_rranking',
            self::RELATIVE_TABLES => [],
        ],
        'item_listprice' => [
            self::TABLE_NAME => 'item_listprice',
            self::RELATIVE_TABLES => [],
        ],
        'items_analytics' => [
            self::TABLE_NAME => 'items_analytics',
            self::RELATIVE_TABLES => [],
        ],
        'items_data' => [
            self::TABLE_NAME => 'items_data',
            self::RELATIVE_TABLES => [
                'items_data_all' => [
                    self::TABLE_NAME => 'items_data_all',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'items_data_all_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'items_data_pc' => [
                    self::TABLE_NAME => 'items_data_pc',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'items_data_pc_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'items_data_sd_web' => [
                    self::TABLE_NAME => 'items_data_sd_web',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'items_data_sd_web_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'items_data_sd_app' => [
                    self::TABLE_NAME => 'items_data_sd_app',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'items_data_sd_app_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'items_sales' => [
            self::TABLE_NAME => 'items_sales',
            self::RELATIVE_TABLES => [],
        ],
        'keysearch_ranking' => [
            self::TABLE_NAME => 'keysearch_ranking',
            self::RELATIVE_TABLES => [],
        ],
        'purchase_count' => [
            self::TABLE_NAME => 'purchase_count',
            self::RELATIVE_TABLES => [
                'purchase_count_num' => [
                    self::TABLE_NAME => 'purchase_count_num',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'prchs_cnt_num_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'purchase_count_sales_amnt' => [
                    self::TABLE_NAME => 'purchase_count_sales_amnt',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'prchs_cnt_sales_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'purchase_count_2y' => [
            self::TABLE_NAME => 'purchase_count',
            self::RELATIVE_TABLES => [
                'purchase_count_2y_num' => [
                    self::TABLE_NAME => 'purchase_count_2y_num',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'prchs_cnt_2y_num_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'purchase_count_2y_sales_amnt' => [
                    self::TABLE_NAME => 'purchase_count_2y_sales_amnt',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'prchs_cnt_2y_sales_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'rgroup_ad' => [
            self::TABLE_NAME => 'rgroup_ad',
            self::RELATIVE_TABLES => [
                'rgroup_ad_sales_amnt' => [
                    self::TABLE_NAME => 'rgroup_ad_sales_amnt',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'sales_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'rgroup_ad_by_ad' => [
            self::TABLE_NAME => 'rgroup_ad_by_ad',
            self::RELATIVE_TABLES => [
                'rgroup_ad_by_ad_sales_amnt' => [
                    self::TABLE_NAME => 'rgroup_ad_by_ad_sales_amnt',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'sales_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'rpp_ad' => [
            self::TABLE_NAME => 'rpp_ad',
            self::RELATIVE_TABLES => [
                'rpp_actual_amnt' => [
                    self::TABLE_NAME => 'rpp_actual_amnt',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'rpp_actual_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'rpp_sales_amnt' => [
                    self::TABLE_NAME => 'rpp_actual_amnt',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'rpp_sales_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'rpp_sales_num' => [
                    self::TABLE_NAME => 'rpp_sales_num',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'rpp_sales_num_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'rpp_cvr_rate' => [
                    self::TABLE_NAME => 'rpp_cvr_rate',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'rpp_cvr_rate_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'rpp_roas' => [
                    self::TABLE_NAME => 'rpp_roas',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'rpp_roas_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'rpp_price_per_order' => [
                    self::TABLE_NAME => 'rpp_price_per_order',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'rpp_price_per_order_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'shop_analytics_daily' => [
            self::TABLE_NAME => 'shop_analytics_daily',
            self::RELATIVE_TABLES => [
                'shop_analytics_daily_sales_amnt' => [
                    self::TABLE_NAME => 'shop_analytics_daily_sales_amnt',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'sales_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'shop_analytics_daily_sales_num' => [
                    self::TABLE_NAME => 'shop_analytics_daily_sales_num',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'sales_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'shop_analytics_daily_access_num' => [
                    self::TABLE_NAME => 'shop_analytics_daily_access_num',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'access_num_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'shop_analytics_daily_conversion_rate' => [
                    self::TABLE_NAME => 'shop_analytics_daily_conversion_rate',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'conversion_rate_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'shop_analytics_daily_sales_amnt_per_user' => [
                    self::TABLE_NAME => 'shop_analytics_daily_sales_amnt_per_user',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'sales_amnt_per_user_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'shop_analytics_monthly' => [
            self::TABLE_NAME => 'shop_analytics_monthly',
            self::RELATIVE_TABLES => [
                'shop_analytics_monthly_access_num' => [
                    self::TABLE_NAME => 'shop_analytics_monthly_access_num',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'access_num_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'shop_analytics_monthly_conversion_rate' => [
                    self::TABLE_NAME => 'shop_analytics_monthly_conversion_rate',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'conversion_rate_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'shop_analytics_monthly_sales_amnt' => [
                    self::TABLE_NAME => 'shop_analytics_monthly_sales_amnt',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'sales_amnt_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'shop_analytics_monthly_sales_amnt_per_user' => [
                    self::TABLE_NAME => 'shop_analytics_monthly_sales_amnt_per_user',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'sales_amnt_per_user_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
                'shop_analytics_monthly_sales_num' => [
                    self::TABLE_NAME => 'shop_analytics_monthly_sales_num',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'sales_num_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_OUTBOUND,
                ],
            ],
        ],
        'tda_ad' => [
            self::TABLE_NAME => 'tda_ad',
            self::RELATIVE_TABLES => [],
        ],
        'user_trends' => [
            self::TABLE_NAME => 'user_trends',
            self::RELATIVE_TABLES => [],
        ],
    ];

    public const MACRO_OPERATORS = [
        '=', '!=', '<', '>', '<=', '>=', 'like', 'not_like',
    ];

    public const MACRO_OPERATOR_LABELS = [
        '=：等しい ', '≠：等しくない', '≺：より小さい', '≻：より大きい', '≦：以下', '≧：以上',
        '次のいずれかを含む', '次のいずれも含まない',
    ];

    public const MACRO_OPERATORS_OF_TYPES = [
        'string' => ['like', 'not_like'],
        'number' => ['=', '!=', '<', '>', '<=', '>='],
        'date' => ['=', '!=', '<', '>', '<=', '>='],
    ];

    public const MACRO_TIME_CONDITION_DESIGNATION = 'designation';
    public const MACRO_TIME_CONDITION_SCHEDULE = 'schedule';
    public const MACRO_TIME_CONDITIONS = [
        self::MACRO_TIME_CONDITION_DESIGNATION => '曰付在指定',
        self::MACRO_TIME_CONDITION_SCHEDULE => 'Schedule',
    ];

    public const MACRO_STATUS_NOT_READY = 0;
    public const MACRO_STATUS_READY = 1;
    public const MACRO_STATUS_FINISH = 2;
    public const MACRO_STATES = [
        self::MACRO_STATUS_NOT_READY => 'Not ready',
        self::MACRO_STATUS_READY => 'Ready',
        self::MACRO_STATUS_FINISH => 'Finish',
    ];

    public const MACRO_GRAPH_TYPE_LINE_CHART = 'line_chart';
    public const MACRO_GRAPH_TYPE_BAR_CHART = 'bar_chart';
    public const MACRO_GRAPH_TYPE_PIE_CHART = 'pie_chart';
    public const MACRO_GRAPH_TYPE_SCATTER_PLOT_CHART = 'scatter_plot_chart';
    public const MACRO_GRAPH_TYPE_STACKED_BAR_CHART = 'stacked_bar_chart';
    public const MACRO_GRAPH_TYPES = [
        self::MACRO_GRAPH_TYPE_LINE_CHART => '折れ線グラフ',
        self::MACRO_GRAPH_TYPE_BAR_CHART => '棒グラフ',
        self::MACRO_GRAPH_TYPE_PIE_CHART => '円グラフ',
        self::MACRO_GRAPH_TYPE_SCATTER_PLOT_CHART => '散布図',
        self::MACRO_GRAPH_TYPE_STACKED_BAR_CHART => '積み上げ棒グラフ',
    ];

    public const MACRO_POSITION_DISPLAY_1 = 'macrograph_1';
    public const MACRO_POSITION_DISPLAY_2 = 'macrograph_2';
    public const MACRO_POSITION_DISPLAY_3 = 'macrograph_3';
    public const MACRO_POSITION_DISPLAY_4 = 'macrograph_4';
    public const MACRO_POSITION_DISPLAY = [
        self::MACRO_POSITION_DISPLAY_1 => 'マクログラフ1',
        self::MACRO_POSITION_DISPLAY_2 => 'マクログラフ2',
        self::MACRO_POSITION_DISPLAY_3 => 'マクログラフ3',
        self::MACRO_POSITION_DISPLAY_4 => 'マクログラフ4',
    ];

    public const ACCOUNTING_ACTUAL_COLUMN = '.actual';
    public const ACCOUNTING_DIFF_COLUMN = '.diff';
}
