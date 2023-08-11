<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * Handle login.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'company_id' => ['required'],
        ]);

        $user = User::join('companies as c', function ($join) use ($data) {
            $join->on('c.id', '=', 'users.company_id')
                ->where('c.company_id', $data['company_id']);
        })
            ->where('email', $data['email'])
            ->first(['users.*', 'c.company_id as company_company_id', 'c.name as company_name']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (is_null($user->email_verified_at)) {
            $this->userRepository->sendEmailVerificationNotification($user, $data['password']);

            return response()->json([
                'message' => __('Verification email has been sent.'),
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $user->createToken('maclogi_css_user', ['*'], now()->addDay())->plainTextToken,
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => __('You are logged out.'),
        ]);
    }
}
