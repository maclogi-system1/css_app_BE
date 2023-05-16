<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Company::exists()) {
            Company::firstOrcreate([
                'company_id' => 'maclogi',
                'name' => '株式会社マクロジ',
            ], [
                'password' => bcrypt(123456),
            ]);
        }
    }
}
