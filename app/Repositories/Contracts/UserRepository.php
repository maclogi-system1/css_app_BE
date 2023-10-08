<?php

namespace App\Repositories\Contracts;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface UserRepository extends Repository
{
    /**
     * Get the list of the resource with pagination and handle filter.
     */
    public function getList(array $filters = [], array $columns = ['*']): LengthAwarePaginator|Collection;

    /**
     * Find a specified user with roles or permissions.
     */
    public function find($id, array $columns = ['*'], array $filters = []): ?User;

    /**
     * Handle create a new user and assign role for that.
     */
    public function create(array $data): ?User;

    /**
     * Handle update the specified user.
     */
    public function update(array $data, User $user): ?User;

    /**
     * Handle delete the specified user.
     */
    public function delete(User $user, ?User $auth = null): ?User;

    /**
     * Handle sync teams to a specified user.
     */
    public function syncTeams(User $user, array $teams): void;

    /**
     * Handle link a specified user to chatwork by chatwork_account_id.
     */
    public function linkUserToChatwork(User $user, $chatworkAccountId): bool;

    /**
     * Handle delete multiple users at the same time.
     */
    public function deleteMultiple(array $userIds, ?User $auth = null): ?bool;

    /**
     * Handle remove bookmarks of the user.
     */
    public function removeUserBookmarks(User $user, array $userIds): void;

    /**
     * Handle update profile photo.
     */
    public function updateProfile(array $data, ?User $user = null): User;

    /**
     * Handle update profile photo.
     */
    public function updateProfilePhoto(array $data, ?User $auth = null): string;

    /**
     * Handle sending email for verification.
     */
    public function sendEmailVerificationNotification(User $user, ?string $password = null): void;

    /**
     * Handle upload profile photo.
     */
    public function uploadProfilePhoto(UploadedFile $file, array|User $user): string;

    /**
     * Get the user's company.
     */
    public function getUsersCompany(User $user): Company;

    /**
     * Get a list of the user by linked service user ids.
     */
    public function getListByLinkedUserIds(array $linkedUserIds): Collection;
}
