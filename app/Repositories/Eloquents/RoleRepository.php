<?php

namespace App\Repositories\Eloquents;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepository as RoleRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class RoleRepository extends Repository implements RoleRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return Role::class;
    }

    /**
     * Get the list of the resource with pagination and handle filter.
     */
    public function getList(array $filters = [], array $columns = ['*']): LengthAwarePaginator|Collection
    {
        if (Arr::has($filters, 'with')) {
            $this->useWith($filters['with']);
        }

        return parent::getList($filters, $columns);
    }

    /**
     * Find a specified role with users or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): Role|null
    {
        if (Arr::has($filters, 'with')) {
            $this->useWith($filters['with']);
        }

        return $this->queryBuilder()->where('id', $id)->first($columns);
    }

    /**
     * Handle create a new role and add users to that.
     */
    public function create(array $data): Role|null
    {
        return $this->handleSafely(function () use ($data) {
            $data['guard_name'] ??= 'web';
            $role = $this->model()->create($data);
            $role->save();

            $permissions = Arr::get($data, 'permissions', []);
            $users = Arr::get($data, 'users', []);

            $role->givePermissionTo($permissions);
            $role->users()->sync($users);

            return $role;
        }, 'Create role');
    }

    /**
     * Handle update the specified role.
     */
    public function update(array $data, Role $role): ?Role
    {
        return $this->handleSafely(function () use ($data, $role) {
            $role->fill($data);

            $permissions = Arr::get($data, 'permissions');
            $users = Arr::get($data, 'users');

            if (! empty($permissions)) {
                $role->syncPermissions($permissions);
                $role->setAttribute('updated_at', now());
            }

            if (! empty($users)) {
                $role->users()->sync($users);
                $role->setAttribute('updated_at', now());
            }

            $role->save();

            return $role->refresh();
        }, 'Update role');
    }

    /**
     * Handle delete the specified role.
     */
    public function delete(Role $role): ?Role
    {
        $role->delete();

        return $role;
    }
}
