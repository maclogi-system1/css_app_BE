<?php

namespace Database\Seeders;

use App\Models\Policy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PolicySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment(['local', 'development'])) {
            $notificationDate = now()->day(16);

            Policy::create([
                'store_id' => 'store_1',
                'name' => '松竹梅クーポン',
                'category' => Policy::MEDIUM_TERM_CATEGORY,
                'kpi' => Policy::INCREASE_IN_AVERAGE_SPEND_PER_CUSTOMER_KPI,
                'template' => Policy::COUPON_TEMPLATE,
                'status' => Policy::CONFIRMED_STATUS,
                'start_date' => $notificationDate->addDay(3)->format('Y-m-d H:i:s'),
                'end_date' => $notificationDate->addWeek()->format('Y-m-d H:i:s'),
            ]);
            Policy::create([
                'store_id' => 'store_1',
                'name' => 'ポイント5倍',
                'category' => Policy::LONG_TERM_CATEGORY,
                'kpi' => Policy::INCREASE_IN_AVERAGE_SPEND_PER_CUSTOMER_KPI,
                'template' => Policy::POINT_TEMPLATE,
                'status' => Policy::CONFIRMED_STATUS,
                'point_rate' => 5,
                'point_application_period' => now()->day(25)->format('Y-m-d H:i:s'),
                'start_date' => $notificationDate->addDay(3)->format('Y-m-d H:i:s'),
                'end_date' => $notificationDate->addWeek()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
