<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $userRepository
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->authorize('view_user');

        $users = UserResource::collection($this->userRepository->getList($request->query()));
        $users->wrap('users');

        return $users;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): UserResource|JsonResponse
    {
        $user = $this->userRepository->create($request->validated());

        return $user ? new UserResource($user): response()->json([
            'message' => __('Created failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource|JsonResponse
    {
        $this->authorize('view_user');

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource|JsonResponse
    {
        $user = $this->userRepository->update($request->validated(), $user);

        return $user ? new UserResource($user) : response()->json([
            'message' => __('Updated failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): UserResource|JsonResponse
    {
        $this->authorize('delete_user');

        if ($request->user()->id == $user->id) {
            return response()->json([
                'message' => __('You can not delete yourself.'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->delete($user);

        return $user ? new UserResource($user) : response()->json([
            'message' => __('Deleted failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove multiple users at the same time.
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        $this->authorize('delete_user');

        return ! $this->userRepository->deleteMultiple($request->query('user_ids', []))
            ? response()->json([
                'message' => __('Delete failed. Please check your user ids!'),
                'user_ids' => $request->input('user_ids', []),
            ], Response::HTTP_BAD_REQUEST)
            : response()->json([
                'message' => __('The users have been deleted successfully.'),
            ]);
    }
}
