<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrCreate([
            'name' => '株式会社マクロジ',
            'company_id' => 'maclogi',
        ], [
            'password' => bcrypt(123456),
        ]);

        $company->users()->create([
            'first_name' => 'Supreme',
            'last_name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('kcUy0f$'),
            'email_verified_at' => now(),
            'remember_token' => str()->random(60),
            'is_admin' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (app()->environment(['local', 'development', 'testing'])) {
            User::factory()->count(50)->for($company)->create();
        }
    }
}
