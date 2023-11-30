<?php

return [
    'api_url' => [
        'production' => [
            'store_pred_36m' => 'https://j5ze4yajxh.execute-api.ap-northeast-1.amazonaws.com/deploy/css-ai-systems-inference-store_pred_36m',
            'predict_2_months_url' => 'https://imk5fupme7.execute-api.ap-northeast-1.amazonaws.com/deploy/css-ai-systems-predict_2months',
            'module_suggest_policy_url' => 'https://ru3o501u2m.execute-api.ap-northeast-1.amazonaws.com/deploy/css-ai-systems-module-suggest_policy',
        ],
        'staging' => [
            'store_pred_36m' => 'https://mb1jm5yh76.execute-api.ap-northeast-1.amazonaws.com/default/test',
            'predict_2_months_url' => 'https://g5p4jn0a41.execute-api.ap-northeast-1.amazonaws.com/default/predict_2months-dev001',
            'module_suggest_policy_url' => 'https://cy6klmqqj4.execute-api.ap-northeast-1.amazonaws.com/default/ec-policy-prediction-module-suggest_policy-dev001',
        ],
    ],
];
