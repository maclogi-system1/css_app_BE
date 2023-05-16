<?php

namespace App\Repositories\Eloquents;

use App\Models\Bookmark;
use App\Models\User;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use App\Repositories\Repository;
use LogicException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class UserRepository extends Repository implements UserRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return User::class;
    }

    /**
     * Get the list of the resource with pagination and handle filter.
     */
    public function getList(array $filters = [], array $columns = ['*'])
    {
        if (Arr::has($filters, 'with')) {
            $this->useWith($filters['with']);
        }

        if ($role = Arr::pull($filters, 'search.role')) {
            $this->useHas(['roles' => function (Builder $query) use ($role) {
                $query->where('display_name', 'like', "%{$role}%")
                    ->orWhere('name', 'like', "%{$role}%");
            }]);
        }

        if ($company = Arr::pull($filters, 'search.company')) {
            $this->useHas(['company' => function (Builder $query) use ($company) {
                $query->where('email', 'like', "%{$company}%")
                    ->orWhere('name', 'like', "%{$company}%");
            }]);
        }

        return parent::getList($filters, $columns);
    }

    /**
     * Set filter for builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getWithFilter(Builder $builder, array $filters = []): Builder
    {
        if ($fullName = Arr::pull($filters, 'search.name')) {
            $builder->where(function ($query) use ($fullName) {
                $query->where('first_name', 'like', "%{$fullName}%")
                    ->orWhere('last_name', 'like', "%{$fullName}%");
            });
        }

        return parent::getWithFilter($builder, $filters);
    }

    /**
     * Find a specified user with roles or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): User|null
    {
        if (Arr::has($filters, 'with')) {
            $this->useWith($filters['with']);
        }

        $this->useWith(['company']);

        return $this->queryBuilder()->where('id', $id)->first($columns);
    }

    /**
     * Handle create a new user and assign role for that.
     */
    public function create(array $data): ?User
    {
        return $this->handleSafely(function () use ($data) {
            $user = $this->model();
            $data['password'] = bcrypt($data['password']);
            $user->fill($data)->save();

            $user->syncRoles(Arr::get($data, 'roles', []));

            return $user;
        }, 'Create user');
    }

    /**
     * Handle update the specified user.
     */
    public function update(array $data, $id): ?User
    {
        $user = $id instanceof User ? $id : $this->model()->find($id);

        return $this->handleSafely(function () use ($data, $user) {
            $user->fill($data);
            $user->save();

            $user->syncRoles(Arr::get($data, 'roles', []));

            return $user->refresh();
        }, 'Update user');
    }

    /**
     * Handle delete the specified user.
     */
    public function delete($id, ?User $auth = null): ?User
    {
        $user = $id instanceof User ? $id : $this->model()->find($id);

        return $this->handleSafely(function () use ($user, $auth) {
            if ($this->auth(user: $auth)->id == $user->id) {
                throw new LogicException('Can not delete current user.');
            }

            $user->delete();

            return $user->refresh();
        }, 'Delete user');
    }

    /**
     * Handle delete multiple users at the same time.
     */
    public function deleteMultiple(array $userIds, ?User $auth = null): ?bool
    {
        if (empty($userIds) || in_array($this->auth(user: $auth)->id, $userIds)) {
            return null;
        }

        return $this->handleSafely(function () use ($auth, $userIds) {
            $result = $this->model()->whereIn('id', $userIds)->delete();

            if ($result) {
                $this->removeUserBookmarks($this->auth(user: $auth), $userIds);
            }

            return $result;
        }, 'Delete multiple user');
    }

    /**
     * Handle remove bookmarks of the user.
     */
    public function removeUserBookmarks(User $user, array $userIds): void
    {
        $bookmarkedList = $user->bookmarks(User::class)->get()->pluck('bookmarkable_id')->toArray();
        $unbookmarks = array_intersect($bookmarkedList, $userIds);

        Bookmark::whereIn('bookmarkable_id', $unbookmarks)->delete();
    }

    /**
     * Handle update profile photo.
     */
    public function updateProfilePhoto(array $data, ?User $auth = null): string
    {
        $currentPath = $auth->profile_photo_path;
        $fileName = str($auth->full_name)
            ->snake()
            ->append('_'.time().'.'.$data['photo']->extension());
        $photoPath = $data['photo']->storeAs('images/profile_photo', $fileName, 'public');
        $auth->forceFill([
            'profile_photo_path' => $photoPath,
        ])->save();

        if ($currentPath && Storage::exists($currentPath)) {
            Storage::disk('public')->delete($currentPath);
        }

        return $photoPath;
    }
}
