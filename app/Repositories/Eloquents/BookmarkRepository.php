<?php

namespace App\Repositories\Eloquents;

use App\Models\Bookmark;
use App\Models\User;
use App\Repositories\Contracts\BookmarkRepository as BookmarkRepositoryContract;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;

class BookmarkRepository extends Repository implements BookmarkRepositoryContract
{
    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return Bookmark::class;
    }

    /**
     * Get a list of the bookmark of current user.
     */
    public function getBookmarked(User $user, ?string $modelBaseName = null)
    {
        $modelName = is_null($modelBaseName)
            ? $modelBaseName
            : str($modelBaseName)->title()->prepend('App\\Models\\')->toString();

        $bookmarks = $user->bookmarks($modelName)->get();

        return $bookmarks;
    }

    public function bookmark(User $user, array $modelInfo)
    {
        return $this->handleSafely(function () use ($user, $modelInfo) {
            $model = $this->getInstanceModel(
                Arr::get($modelInfo, 'id'),
                Arr::get($modelInfo, 'type')
            );

            if (! $user->hasBookmarked($model)) {
                $bookmark = $this->model();
                $bookmark->user_id = $user->id;
                $bookmark->bookmarkable_id = $model->getKey();
                $bookmark->bookmarkable_type = $model->getMorphClass();

                $bookmark->save();
            }

            return true;
        }, 'Bookmark');
    }

    public function unBookmark(User $user, array $modelInfo)
    {
        return $this->handleSafely(function () use ($user, $modelInfo) {
            $model = $this->getInstanceModel(
                Arr::get($modelInfo, 'id'),
                Arr::get($modelInfo, 'type')
            );
            $user->bookmarks($model->getMorphClass())
                ->where('bookmarkable_id', $model->getKey())
                ->delete();

            return true;
        }, 'Unbookmark');
    }

    private function getInstanceModel(?int $id, ?string $type): Model
    {
        $modelName = str($type)->title()->prepend('App\\Models\\')->toString();

        return class_exists($modelName) ? $modelName::find($id) : throw new ModelNotFoundException();
    }
}
