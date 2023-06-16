<?php

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoleRepository extends Repository
{
    /**
     * Get the list of the resource with pagination and handle filter.
     */
    public function getList(array $filters = [], array $columns = ['*']): LengthAwarePaginator|Collection;

    /**
     * Find a specified role with users or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?Role;

    /**
     * Handle create a new role and add users to that.
     */
    public function create(array $data): ?Role;

    /**
     * Handle update the specified role.
     */
    public function update(array $data, Role $role): ?Role;

    /**
     * Handle delete the specified role.
     */
    public function delete(Role $role): ?Role;

    /**
     * Handle delete multiple roles at the same time.
     */
    public function deleteMultiple(array $roleIds, ?Role $auth = null): ?bool;
}
