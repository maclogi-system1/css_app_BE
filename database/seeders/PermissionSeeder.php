<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guardName = config('auth.defaults.guard');

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if (! Role::exists()) {
            $roleMaster = Role::create([
                'name' => 'Master',
                'display_name' => 'マスター',
                'guard_name' => $guardName,
            ]);
            $roleCompanyAdmin = Role::create([
                'name' => 'Company Administrator',
                'display_name' => '企業管理者',
                'guard_name' => $guardName,
            ]);
            $roleCorporateLeader = Role::create([
                'name' => 'Corporate Leader',
                'display_name' => '企業リーダー',
                'guard_name' => $guardName,
            ]);
            $roleBusiness = Role::create([
                'name' => 'Business',
                'display_name' => '企業一般',
                'guard_name' => $guardName,
            ]);

            DB::table('permission_role')->truncate();
            $roleMaster->syncPermissions($this->insertPermissions($guardName));

            $roleMaster->users()->sync(User::all());
        }
    }

    private function insertPermissions($guardName)
    {
        $permissions = [];

        foreach (['user', 'role', 'company'] as $suffix) {
            foreach (Permission::CODES as $prefix) {
                $name = "{$prefix}_{$suffix}";

                Permission::firstOrCreate([
                    'display_name' => $name,
                    'name' => $name,
                    'guard_name' => $guardName,
                ]);

                $permissions[] = $name;
            }
        }

        return $permissions;
    }
}
