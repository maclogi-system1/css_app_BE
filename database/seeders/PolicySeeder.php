<?php

namespace Database\Seeders;

use App\Models\Policy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->environment(['local', 'development'])) {
            Policy::create([
                'store_id' => 'store_1',
                'name' => '松竹梅クーポン',
                'category' => Policy::MEDIUM_TERM_CATEGORY,
                'kpi' => Policy::INCREASE_IN_AVERAGE_SPEND_PER_CUSTOMER_KPI,
            ]);
            Policy::create([
                'store_id' => 'store_1',
                'name' => 'ポイント5倍',
                'category' => Policy::LONG_TERM_CATEGORY,
                'kpi' => Policy::INCREASE_IN_AVERAGE_SPEND_PER_CUSTOMER_KPI,
            ]);
        }
    }
}
