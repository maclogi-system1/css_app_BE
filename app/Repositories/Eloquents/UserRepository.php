<?php

namespace App\Repositories\Eloquents;

use App\Mail\VerifyEmailRegistered;
use App\Models\Bookmark;
use App\Models\Company;
use App\Models\LinkedUserInfo;
use App\Models\User;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\ChatworkService;
use App\WebServices\OSS\UserService;
use App\WebServices\UploadFileService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use LogicException;

class UserRepository extends Repository implements UserRepositoryContract
{
    public function __construct(
        private UploadFileService $uploadFileService,
        private UserService $userService,
        private \App\Repositories\Contracts\LinkedUserInfoRepository $linkedUserInfoRepository,
    ) {
    }

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
        $filters = $this->prepareFilterData($filters);

        $this->enableUseWith(['chatwork', 'company', 'teams', 'roles', 'permissions'], $filters);

        if ($role = Arr::pull($filters, 'search.role')) {
            $this->useHas([
                'roles' => function (Builder $query) use ($role) {
                    $query->orSearches([
                        'display_name' => $role,
                        'name' => $role,
                    ]);
                },
            ]);
        }

        if ($role = Arr::pull($filters, 'filter.role')) {
            if (is_array($role)) {
                $this->useHas([
                    'roles' => function (Builder $query) use ($role) {
                        $query->whereIn('display_name', $role)
                            ->orWhereIn('name', $role);
                    },
                ]);
            } else {
                $this->useHas([
                    'roles' => function (Builder $query) use ($role) {
                        $query->where('display_name', $role)
                            ->orWhere('name', $role);
                    },
                ]);
            }
        }

        if ($company = Arr::pull($filters, 'search.company')) {
            $this->useHas([
                'company' => function (Builder $query) use ($company) {
                    $query->orSearches([
                        'company_id' => $company,
                        'name' => $company,
                    ]);
                },
            ]);
        }

        if ($company = Arr::pull($filters, 'filter.company')) {
            $this->useHas([
                'company' => function (Builder $query) use ($company) {
                    $query->where('id', $company)
                        ->orWhere('name', $company);
                },
            ]);
        }

        return parent::getList($filters, $columns);
    }

    /**
     * Find a specified user with roles or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?User
    {
        $filters = $this->prepareFilterData($filters);
        $this->enableUseWith(['chatwork', 'teams', 'roles', 'permissions'], $filters);

        $this->useWith(['company', 'roles', 'roles.permissions']);

        return $this->queryBuilder()->where('id', $id)->first($columns);
    }

    /**
     * Handle create a new user and assign role for that.
     */
    public function create(array $data): ?User
    {
        return $this->handleSafely(function () use ($data) {
            $password = str()->random(8);
            $data['password'] = bcrypt($password);

            $deletedUser = $this->model()
                ->onlyTrashed()
                ->where('email', $data['email'])
                ->first();

            if (! is_null($deletedUser)) {
                $deletedUser->forceFill(Arr::only($data, $this->model()->getFillable()) + [
                        'deleted_at' => null,
                        'email_verified_at' => null,
                    ])->saveQuietly();
                $user = $deletedUser;
            } else {
                $user = $this->model();
                $user->fill($data)->save();
            }

            $user->syncRoles(Arr::get($data, 'roles', []));

            if (Arr::has($data, 'teams')) {
                $this->syncTeams($user->refresh(), $data['teams']);
            }

            if (isset($data['chatwork_account_id'])) {
                $this->linkUserToChatwork($user, $data['chatwork_account_id']);
            }

            $paramAPI = [
                'name' => $data['name'],
                'email' => $data['email'],
                'image_path' => $data['profile_photo_path'] ?? null,
                'is_css' => 1,
            ];

            $result = $this->userService->create($paramAPI);
            if (! $result->get('success')) {
                throw new Exception('Create user at task-management failed.');
            }

            $this->linkedUserInfoRepository->create([
                'user_id' => $user->id,
                'service_id' => 2,
                'linked_service_user_id' => $result->get('data')->get('data'),
            ]);

            $this->sendEmailVerificationNotification($user, $password);

            return $user->withAllRels();
        }, 'Create user');
    }

    /**
     * Handle sync teams to a specified user.
     */
    public function syncTeams(User $user, array $teams): void
    {
        // Add only teams belonging to the current user's company.
        $companysTeam = $user->company->teams->pluck('id')->intersect($teams)->all();
        $user->teams()->sync($companysTeam);
    }

    /**
     * Handle link a specified user to chatwork by chatwork_account_id.
     */
    public function linkUserToChatwork(User $user, $chatworkAccountId): bool
    {
        $service = new ChatworkService();
        $memberInfo = $service->findMemberByAccountId($chatworkAccountId);
        $chatwork = $user->chatwork()->where('account_id', $chatworkAccountId)->first();

        if (empty($memberInfo) || $chatwork) {
            return false;
        }

        $user->chatwork()->create([
            'account_id' => $memberInfo->account_id,
            'role' => $memberInfo->role,
            'name' => $memberInfo->name,
            'chatwork_id' => $memberInfo->chatwork_id,
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
    public function sendEmailVerificationNotification(User $user, ?string $password = null): void
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
            $currentPath = $user->profile_photo_path;
            $user->fill($data);

            if (Arr::has($data, 'profile_photo_path')) {
                if ($currentPath && Storage::exists($currentPath)) {
                    Storage::delete($currentPath);
                }
            }

            $user->save();

            if (Arr::has($data, 'roles')) {
                $user->syncRoles(Arr::get($data, 'roles', []));
            }

            if (isset($data['chatwork_account_id'])) {
                $this->linkUserToChatwork($user, $data['chatwork_account_id']);
            } else {
                $user->chatwork()->delete();
            }

            if (Arr::has($data, 'teams')) {
                $this->syncTeams($user->refresh(), $data['teams']);
            }

            return $user->withAllRels();
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

        $fileName = str(Arr::get($user, 'email', $file->getClientOriginalName()))
            ->snake()
            ->append('_'.time().'.'.$file->extension());

        return $this->uploadFileService->uploadImage($file, $fileName, User::PROFILE_PATH);
    }

    /**
     * Handle update profile photo.
     */
    public function updateProfile(array $data, ?User $user = null): User
    {
        if ($user->email != $data['email']) {
            $this->updateVerifiedUser($user, Arr::only($data, ['name', 'email']));
        } else {
            $user->forceFill(Arr::only($data, ['name', 'email']))->saveQuietly();
        }

        if (Arr::has($data, 'chatwork_account_id')) {
            $this->linkUserToChatwork($user, $data['chatwork_account_id']);
        }

        if (Arr::has($data, 'team_id')) {
            $user->teams()->sync([$data['team_id']]);
        }

        return $user;
    }

    /**
     * Update the given verified user's profile information.
     */
    private function updateVerifiedUser(User $user, array $input)
    {
        $user->forceFill($input + [
                'email_verified_at' => null,
            ])->saveQuietly();

        $this->sendEmailVerificationNotification($user);

        $user->tokens()->delete();
    }

    /**
     * Get the user's company.
     */
    public function getUsersCompany(User $user): Company
    {
        $usersTeam = $user->teams->first();
        $company = $user->company->withAllRels();
        $company->teams->map(function ($team) use ($usersTeam) {
            if ($usersTeam?->id == $team->id) {
                $team->is_user_s_team = 1;
            } else {
                $team->is_user_s_team = 0;
            }

            return $team;
        });

        return $company;
    }

    /**
     * Get a list of the user by linked service user ids.
     */
    public function getListByLinkedUserIds(array|Collection $linkedUserIds): Collection
    {
        /** @var \App\Models\LinkedUserInfo */
        $linkedServiceUser = app(LinkedUserInfo::class);
        $users = $linkedServiceUser->with(['cssUser'])
            ->whereIn('linked_service_user_id', $linkedUserIds)
            ->get()
            ->pluck('cssUser')->filter();

        return $users;
    }

    /**
     * Get a list of css_user_ids by oss_user_ids.
     */
    public function getCssUserIdsByOssUserIds(array|Collection $linkedUserIds): array
    {
        return $this->getListByLinkedUserIds($linkedUserIds)->pluck('id')->toArray();
    }

    public function getUsersByIds(array $userIds): Collection
    {
        return $this->model()->newQuery()->whereIn('id', $userIds)->get();
    }

    public function prepareFilterData(array $filters): array
    {
        $withs = Arr::get($filters, 'with');
        if (! empty($withs) && in_array('role', $withs)) {
            $withs[] = 'roles';
            Arr::set($filters, 'with', array_filter($withs, fn ($with) => $with != 'role'));
        }

        return $filters;
    }
}
