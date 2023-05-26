<?php

namespace App\Repositories\Eloquents;

use App\Mail\VerifyEmailRegistered;
use App\Models\Bookmark;
use App\Models\User;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use App\Repositories\Repository;
use App\Services\ChatworkService;
use App\Services\UploadFileService;
use LogicException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserRepository extends Repository implements UserRepositoryContract
{
    public function __construct(
        private UploadFileService $uploadFileService
    ) {}

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
    public function getList(array $filters = [], array $columns = ['*']): LengthAwarePaginator|Collection
    {
        if (Arr::has($filters, 'with')) {
            $this->useWith($filters['with']);
        }

        if ($role = Arr::pull($filters, 'search.role')) {
            $this->useHas(['roles' => function (Builder $query) use ($role) {
                $query->orSearches([
                    'display_name' => $role,
                    'name' => $role,
                ]);
            }]);
        }

        if ($role = Arr::pull($filters, 'filter.role')) {
            $this->useHas(['roles' => function (Builder $query) use ($role) {
                $query->where('display_name', $role)
                    ->orWhere('name', $role);
            }]);
        }

        if ($company = Arr::pull($filters, 'search.company')) {
            $this->useHas(['company' => function (Builder $query) use ($company) {
                $query->orSearches([
                    'company_id' => $company,
                    'name' => $company,
                ]);
            }]);
        }

        if ($company = Arr::pull($filters, 'filter.company')) {
            $this->useHas(['company' => function (Builder $query) use ($company) {
                $query->where('company_id', $company)
                    ->orWhere('name', $company);
            }]);
        }

        return parent::getList($filters, $columns);
    }

    /**
     * Find a specified user with roles or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?User
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
            $password = str()->random(8);
            $data['password'] = bcrypt($password);
            $user->fill($data)->save();

            $user->syncRoles(Arr::get($data, 'roles', []));

            if (Arr::has($data, 'teams')) {
                $user->teams()->sync(Arr::get($data, 'teams'));
            }

            if (isset($data['chatwork_id'])) {
                $this->linkUserToChatwork($user, $data['chatwork_id']);
            }

            $this->sendEmailVerificationNotification($user, $password);

            return $user;
        }, 'Create user');
    }

    /**
     * Handle link a specified user to chatwork by chatwork_id.
     */
    public function linkUserToChatwork(User $user, $chatworkId): bool
    {
        $service = new ChatworkService();
        $memberInfo = $service->findMemberByChatworkId($chatworkId);
        $chatwork = $user->chatwork()->where('chatwork_id', $chatworkId)->first();

        if (empty($memberInfo) || $chatwork) {
            return false;
        }

        $user->chatwork()->create([
            'account_id' => $memberInfo->account_id,
            'role' => $memberInfo->role,
            'name' => $memberInfo->name,
            'chatwork_id' => $chatworkId,
            'organization_id' => $memberInfo->organization_id,
            'organization_name' => $memberInfo->organization_name,
            'department' => $memberInfo->department,
            'avatar_image_url' => $memberInfo->avatar_image_url,
        ]);

        return true;
    }

    /**
     * Handle sending email for verification.
     */
    public function sendEmailVerificationNotification(User $user, $password): void
    {
        $expires = now()->addDay()->timestamp;
        $token = sha1($user->email.$expires);
        $signature = $user->getSignatureVerifyEmail($token, $expires);
        $url = route('verification.verify', [
            'id' => $user->id,
            'hash' => $token,
            'expires' => $expires,
            'signature' => $signature,
        ]);

        Mail::to($user)->send(new VerifyEmailRegistered($user, $password, $url));
    }

    /**
     * Handle update the specified user.
     */
    public function update(array $data, User $user): ?User
    {
        return $this->handleSafely(function () use ($data, $user) {
            $user->fill($data);
            $currentPath = $user->profile_photo_path;

            if (Arr::has($data, 'profile_photo_path')) {
                if ($currentPath && Storage::exists($currentPath)) {
                    Storage::delete($currentPath);
                }
            }

            $user->save();

            if (Arr::has($data, 'roles')) {
                $user->syncRoles(Arr::get($data, 'roles', []));
            }

            if (isset($data['chatwork_id'])) {
                $this->linkUserToChatwork($user, $data['chatwork_id']);
            } else {
                $user->chatwork()->delete();
            }

            if (Arr::has($data, 'teams')) {
                $user->teams()->sync($data['teams']);
            }

            return $user->refresh();
        }, 'Update user');
    }

    /**
     * Handle delete the specified user.
     */
    public function delete(User $user, ?User $auth = null): ?User
    {
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
        $photoPath = $this->uploadProfilePhoto($data['profile_photo_path'], $auth);
        $auth->forceFill([
            'profile_photo_path' => $photoPath,
        ])->save();

        if ($currentPath && Storage::exists($currentPath)) {
            Storage::delete($currentPath);
        }

        return $photoPath;
    }

    /**
     * Handle upload profile photo.
     */
    public function uploadProfilePhoto(UploadedFile $file, array|User $user): string
    {
        $user = to_array($user);

        $fileName = str(Arr::get($user, 'name', $file->getClientOriginalName()))
            ->snake()
            ->append('_'.time().'.'.$file->extension());

        return $this->uploadFileService->uploadImage($file, $fileName, 'images/profile_photo');
    }
}
