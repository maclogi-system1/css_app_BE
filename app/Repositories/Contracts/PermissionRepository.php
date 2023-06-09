<?php

namespace App\Repositories\Contracts;

use App\Models\Permission;

interface PermissionRepository extends Repository
{
    /**
     * Handle update permission.
     */
    public function update(array $data, Permission $permission): ?Permission;
}
