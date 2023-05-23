<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Team;
use Illuminate\Database\Seeder;

class CompanyTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Team::whereNull('company_id')->update([
            'company_id' => Company::first()->id,
        ]);
    }
}
