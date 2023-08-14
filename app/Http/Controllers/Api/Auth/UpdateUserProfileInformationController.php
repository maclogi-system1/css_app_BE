<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateUserProfileInformationController extends Controller
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * Validate and update the given user's profile information.
     */
    public function update(UpdateUserProfileRequest $request): UserResource
    {
        return new UserResource($this->userRepository->updateProfile($request->validated(), $request->user()));
    }

    /**
     * Handle update profile photo.
     */
    public function uploadProfilePhoto(Request $request): JsonResponse
    {
        $data = $request->validate([
            'profile_photo_path' => [
                'required',
                'image',
                'max:'.config('filesystems.profile_photo_max', 2 * pow(2, 10)), // default 2MB
            ],
        ]);

        return response()->json([
            'profile_photo_path' => $this->userRepository->updateProfilePhoto($data, $request->user()),
        ]);
    }
}
