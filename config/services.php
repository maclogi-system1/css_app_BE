<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'maclogi_oss' => [
        'url' => env('MACLOGI_OSS_URL', 'http://localhost'),
        'key' => env('MACLOGI_OSS_KEY'),
        'api_uri' => [
            'shops' => [
                'list' => '/shops',
                'detail' => '/shops/{storeId}',
            ],
            'alerts' => [
                'list' => '/alerts',
            ],
            'tasks' => [
                'list' => '/tasks',
            ],
            'job_groups' => [
                'list' => '/job-groups',
                'detail' => '/job-groups/{id}',
                'validate_create' => '/job-groups/validation',
                'validate_update' => '/job-groups/validation-update',
                'create' => '/job-groups',
                'update' => '/job-groups/{jobGroupCode}',
                'update_time' => '/job-groups/update-time',
            ],
            'single_jobs' => [
                'list' => '/single-jobs',
                'detail' => '/single-jobs/{id}',
                'delete' => '/single-jobs/{id}',
                'options' => '/single-jobs/options',
                'schedule' => '/single-jobs/schedule',
            ],
            'users' => [
                'shop_users' => '/users/oss',
            ],
            'schema' => [
                'get_columns' => '/schema/column',
                'get_list_table' => '/schema/list-table',
                'check_exist_with_store' => '/schema/check-exist-with-store',
                'query_condition_result' => '/schema/query-condition-result',
            ]
        ],
    ],

];
