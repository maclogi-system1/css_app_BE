<?php

namespace App\WebServices\AI;

use App\WebServices\Service;

class PolicyR2Service extends Service
{
    public function getListRecommendByStore($storeId)
    {
        return collect([
            [
                'policy_r2_id' => fake()->uuid(),
                'store_id' => $storeId,
                'store_class' => '',
                'start_date' => '2022-09-19 20:00:00',
                'end_date' => '2022-09-26 20:00:00',
                'notification_date' => '2022-09-16 20:00:00',
                'policy_class' => 'クーポン',
                'kpi_index' => '客単価',
                'policy_name' => '▼松竹梅クーポン▼梅：4,000円以上ご購入で200円OFFクーポン',
                'item_keyword' => '【最大1,500円OFFクーポン有】',
                'rule_1_id' => fake()->uuid(),
                'rule_2_id' => fake()->uuid(),
                'rule_3_id' => fake()->uuid(),
                'service' => '定額',
                'policy_value' => '200',
                'created_at' => '2022-09-01 09:18:27',
                'updated_at' => '2022-09-01 09:18:27',
            ],
        ]);
    }
}
