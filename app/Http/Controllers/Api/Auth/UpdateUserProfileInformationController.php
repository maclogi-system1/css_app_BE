<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UpdateUserProfileInformationController extends Controller
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function update(Request $request): UserResource
    {
        $user = $request->user('sanctum');

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->forceFill($data)->saveQuietly();

        return new UserResource($user);
    }

    public function uploadProfilePhoto(Request $request): JsonResponse
    {
        $data = $request->validate([
            'photo' => [
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
