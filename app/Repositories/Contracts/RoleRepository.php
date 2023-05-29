<?php

namespace App\Repositories\Contracts;

use App\Models\Role;

interface RoleRepository extends Repository
{
    /**
     * Handle delete multiple roles at the same time.
     */
    public function deleteMultiple(array $roleIds, ?Role $auth = null): ?bool;
}
