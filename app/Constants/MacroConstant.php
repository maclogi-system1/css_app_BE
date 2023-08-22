<?php

namespace App\Constants;

class MacroConstant
{
    public const TYPE_INTERNAL = 'CSS';
    public const TYPE_EXTERNAL = 'OSS';
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

    public const MACRO_TYPE_AI = 0;
    public const MACRO_TYPE_CSV = 1;
    public const MACRO_TYPE_DB = 2;
    public const MACRO_ARRAY = [
        self::MACRO_TYPE_AI,
        self::MACRO_TYPE_CSV,
        self::MACRO_TYPE_DB,
    ];

    public const DESCRIPTION_TABLES = [
        'mq_accounting' => [
            self::TABLE_NAME => 'mq_accounting',
            self::TABLE_TYPE => self::TYPE_INTERNAL,
            self::REMOVE_COLUMNS => [
                'mq_kpi_id',
                'mq_access_num_id',
                'mq_ad_sales_amnt_id',
                'mq_user_trends_id',
                'mq_cost_id',
                'created_at',
                'updated_at',
                'store_id',
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
    ];

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
                'policy_attachments' => [
                    self::TABLE_NAME => 'policy_attachments',
                    self::RELATIVE_TABLE_FOREIGN_KEY => 'policy_id',
                    self::RELATIVE_TABLE_FOREIGN_KEY_TYPE => self::RELATIVE_TABLE_TYPE_INBOUND,
                ],
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
    ];
}
