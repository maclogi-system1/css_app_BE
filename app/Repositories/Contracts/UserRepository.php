<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface UserRepository extends Repository
{
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
    public function updateProfilePhoto(array $data, ?User $auth = null): string;

    /**
     * Handle sending email for verification.
     */
    public function sendEmailVerificationNotification(User $user, $password): void;

    /**
     * Handle upload profile photo.
     */
    public function uploadProfilePhoto(UploadedFile $file, array|User $user): string;
}
