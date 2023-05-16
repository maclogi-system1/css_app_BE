<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ToggleBookmarkRequest;
use App\Http\Resources\BookmarkResource;
use App\Repositories\Contracts\BookmarkRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookmarkController extends Controller
{
    public function __construct(
        private BookmarkRepository $bookmarkRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $bookmarks = BookmarkResource::collection(
            $this->bookmarkRepository->getBookmarked(
                $request->user('sanctum'),
                $request->query('type')
            )
        );
        $bookmarks->wrap('bookmarks');

        return $bookmarks;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function bookmark(ToggleBookmarkRequest $request): JsonResponse
    {
        return is_null($this->bookmarkRepository->bookmark($request->user(), $request->validated()))
            ? response()->json([
                'message' => 'Bookmarking failed. Maybe your type is wrong, please check again.',
            ])
            : response()->json([
                'message' => 'Successfully bookmarked.',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function unbookmark(ToggleBookmarkRequest $request): JsonResponse
    {
        return is_null($this->bookmarkRepository->unbookmark($request->user(), $request->validated()))
            ? response()->json([
                'message' => 'Unbookmarking failed. Maybe your type is wrong, please check again.',
            ])
            : response()->json([
                'message' => 'Successfully unbookmarked.',
            ]);
    }
}
