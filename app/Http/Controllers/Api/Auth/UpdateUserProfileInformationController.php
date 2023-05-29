<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UpdateUserProfileInformationController extends Controller
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    /**
     * Validate and update the given user's profile information.
     */
    public function update(UpdateUserProfileRequest $request): UserResource
    {
        $user = $request->user();

        if ($user->email != $request->post('email')) {
            $this->updateVerifiedUser($user, $request->validated());
        } else {
            $user->forceFill($request->validated())->saveQuietly();
        }

        return new UserResource($user);
    }

    /**
     * Update the given verified user's profile information.
     */
    private function updateVerifiedUser(User $user, array $input)
    {
        $user->forceFill($input + [
            'email_verified_at' => null,
        ])->saveQuietly();

        $this->userRepository->sendEmailVerificationNotification($user);

        $user->tokens()->delete();
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
