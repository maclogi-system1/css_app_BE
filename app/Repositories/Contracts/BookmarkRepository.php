<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface BookmarkRepository extends Repository
{
    /**
     * Get a list of the bookmark of current user.
     */
    public function getBookmarked(User $user, ?string $modelBaseName = null);

    public function bookmark(User $user, array $modelInfo);

    public function unBookmark(User $user, array $modelInfo);
}
