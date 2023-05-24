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
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    /**
     * Display a listing of the user.
     */
    public function index(Request $request): JsonResource|JsonResponse
    {
        $this->authorize('view_user');

        $users = UserResource::collection($this->userRepository->getList($request->query()));
        $users->wrap('users');

        return $users;
    }

    /**
     * Get a listing of the user by keyword.
     */
    public function search(Request $request): JsonResource|JsonResponse
    {
        $this->authorize('view_user');

        $users = UserResource::collection($this->userRepository->search(
            ['name', 'email'],
            $request->query(),
            ['id', 'name', 'email']
        ));
        $users->wrap('users');

        return $users;
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): JsonResource|JsonResponse
    {
        $user = $this->userRepository->create($request->validated());

        return $user ? new UserResource($user): response()->json([
            'message' => __('Created failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResource|JsonResponse
    {
        $this->authorize('view_user');

        return new UserResource($user);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResource|JsonResponse
    {
        $user = $this->userRepository->update($request->validated(), $user);

        return $user ? new UserResource($user) : response()->json([
            'message' => __('Updated failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): JsonResource|JsonResponse
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
