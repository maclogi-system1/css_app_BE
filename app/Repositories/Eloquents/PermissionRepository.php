<?php

namespace App\Repositories\Eloquents;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepository as PermissionRepositoryContract;
use App\Repositories\Repository;

class PermissionRepository extends Repository implements PermissionRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return Permission::class;
    }

    public function update(array $data, Permission $permission): ?Permission
    {
        $permission->forceFill($data);
        $permission->save();

        return $permission->refresh();
    }
}
