<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Team::exists()) {
            Team::firstOrcreate([
                'company_id' => Company::first()->id,
                'name' => 'チームA',
            ], [
                'created_by' => User::first()->id,
            ]);
        }
    }
}
