<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
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

        $this->updateOldRoles();

        $roleMaster = Role::updateOrCreate([
            'name' => Role::SYSTEM_ADMIN_ROLE,
            'guard_name' => $guardName,
        ], ['display_name' => Role::ROLE_SEED[Role::SYSTEM_ADMIN_ROLE]]);

        $roleCompanyAdmin = Role::updateOrCreate([
            'name' => Role::COMPANY_ADMINISTRATOR_ROLE,
            'guard_name' => $guardName,
        ], ['display_name' => ROLE::ROLE_SEED[Role::COMPANY_ADMINISTRATOR_ROLE]]);

        $roleGeneralUser = Role::updateOrCreate([
            'name' => Role::GENERAL_USER_ROLE,
            'guard_name' => $guardName,
        ], ['display_name' => ROLE::ROLE_SEED[Role::GENERAL_USER_ROLE]]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Permission::truncate();

        $permissions = $this->insertPermissions($guardName);

        $roleMaster->syncPermissions($permissions->filter(function ($permission) {
            return in_array($permission, [
                'create_all_user_info', 'view_all_user_info', 'edit_all_user_info', 'delete_all_user_info',
                'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
                'view_all_shops', 'create_all_shops', 'edit_all_shops', 'delete_all_shops',
                'edit_all_companies',
                'mq_accountings', 'kpi_dashboard', 'policy_management', 'ai_policy_simulation',
                'create_all_macros', 'view_all_macros', 'edit_all_macros', 'delete_all_macros',
            ]);
        })->values());

        $roleCompanyAdmin->syncPermissions($permissions->filter(function ($permission) {
            return in_array($permission, [
                'create_company_user_info', 'view_company_user_info', 'edit_company_user_info', 'delete_company_user_info',
                'view_all_company_shops', 'create_all_company_shops', 'edit_all_company_shops', 'delete_all_company_shops',
                'mq_accountings', 'kpi_dashboard', 'policy_management', 'ai_policy_simulation',
                'create_all_macros',
            ]);
        })->values());

        $roleGeneralUser->syncPermissions($permissions->filter(function ($permission) {
            return in_array($permission, [
                'view_my_user_info', 'edit_my_user_info',
                'view_shops', 'view_company_contract_shops', 'edit_shops',
                'mq_accountings', 'kpi_dashboard', 'policy_management', 'ai_policy_simulation',
                'view_macros', 'edit_macros', 'delete_macros',
            ]);
        })->values());

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function insertPermissions($guardName): Collection
    {
        $permissions = collect();
        foreach (Permission::PERMISSION_SEED as $name => $displayName) {
            Permission::firstOrCreate([
                'display_name' => $displayName,
                'name' => $name,
                'guard_name' => $guardName,
            ]);

            $permissions->add($name);
        }

        return $permissions;
    }

    private function updateOldRoles(): void
    {
        Role::where('name', 'Master')->update(['name' => Role::SYSTEM_ADMIN_ROLE]);
        Role::where('name', 'Company Administrator')->update(['name' => Role::COMPANY_ADMINISTRATOR_ROLE]);
        Role::where('name', 'General User')->update(['name' => Role::GENERAL_USER_ROLE]);

        Role::whereIn('name', ['Corporate Leader', 'Business'])->delete();
    }
}
