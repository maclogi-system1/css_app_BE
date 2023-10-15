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
    ) {
    }

    /**
     * Display a listing of the user.
     */
    public function index(Request $request): JsonResource|JsonResponse
    {
        $users = UserResource::collection($this->userRepository->getList($request->query()));
        $users->wrap('users');

        return $users;
    }

    /**
     * Get a listing of the user by keyword.
     */
    public function search(Request $request): JsonResource|JsonResponse
    {
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
        $data = $request->validated();

        if ($request->hasFile('profile_photo_path')) {
            $data['profile_photo_path'] = $this->userRepository->uploadProfilePhoto(
                $request->file('profile_photo_path'),
                $data
            );
        }

        $user = $this->userRepository->create($data);

        return $user
            ? (new UserResource($user))->response($request)->setStatusCode(Response::HTTP_CREATED)
            : response()->json([
                'message' => __('Created failure.'),
            ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResource|JsonResponse
    {
        return new UserResource($user);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResource|JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('profile_photo_path')) {
            $data['profile_photo_path'] = $this->userRepository->uploadProfilePhoto(
                $request->file('profile_photo_path'),
                $data
            );
        }

        $user = $this->userRepository->update($data, $user);

        return $user ? new UserResource($user) : response()->json([
            'message' => __('Updated failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): JsonResource|JsonResponse
    {
        if ($request->user()->id == $user->id) {
            return response()->json([
                'message' => __('You cannot delete yourself.'),
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
        return ! $this->userRepository->deleteMultiple($request->query('user_ids', []))
            ? response()->json([
                'message' => __('Delete failed. Please check your user ids!'),
                'user_ids' => $request->input('user_ids', []),
            ], Response::HTTP_BAD_REQUEST)
            : response()->json([
                'message' => __('The users have been deleted successfully.'),
            ]);
    }

    /**
     * Get a list of users as select box options.
     */
    public function getOptions(): JsonResponse
    {
        $users = $this->userRepository->getList(['per_page' => -1])->map(fn ($user) => [
            'value' => $user->id,
            'label' => $user->name,
        ]);

        return response()->json([
            'users' => $users,
        ]);
    }
}
