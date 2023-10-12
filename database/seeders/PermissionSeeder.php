<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
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

        $roleMaster = Role::firstOrCreate([
            'name' => 'Master',
            'display_name' => 'マスター',
            'guard_name' => $guardName,
        ]);

        Role::firstOrCreate([
            'name' => 'Company Administrator',
            'display_name' => '企業管理者',
            'guard_name' => $guardName,
        ]);

        Role::firstOrCreate([
            'name' => 'Corporate Leader',
            'display_name' => '企業リーダー',
            'guard_name' => $guardName,
        ]);

        Role::firstOrCreate([
            'name' => 'Business',
            'display_name' => '企業一般',
            'guard_name' => $guardName,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('permission_role')->truncate();
        DB::table('permissions')->truncate();

        $roleMaster->syncPermissions($this->insertPermissions($guardName));

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function insertPermissions($guardName): array
    {
        $permissions = [];
        foreach (Permission::PERMISSION_SEED as $name => $displayName) {
            Permission::firstOrCreate([
                'display_name' => $displayName,
                'name' => $name,
                'guard_name' => $guardName,
            ]);

            $permissions[] = $name;
        }

        return $permissions;
    }
}
