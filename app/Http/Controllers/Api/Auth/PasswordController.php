<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Traits\PasswordValidationRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    use PasswordValidationRules;

    /**
     * Send password reset link to requested user's email.
     */
    public function sendPasswordResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'message' => __($status),
        ], $status === Password::RESET_LINK_SENT ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get password reset token.
     */
    public function getPasswordResetToken(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = Password::getUser($credentials);

        if (is_null($user)) {
            return response()->json([
                'message' => __(Password::INVALID_USER),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'token' => Password::createToken($user),
        ]);
    }

    /**
     * Handle reset password.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => $this->passwordRules(),
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->setRememberToken(str()->random(60));

                $user->save();
            }
        );

        return response()->json([
            'message' => __($status),
        ], $status === Password::PASSWORD_RESET ? Response::HTTP_ACCEPTED : Response::HTTP_BAD_REQUEST);
    }

    /**
     * Handle change current password.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password:sanctum'],
            'password' => $this->passwordRules() + ['different:current_password'],
        ]);

        $user = $request->user('sanctum');

        $user->forceFill([
            'password' => bcrypt($request->input('password')),
        ])->saveQuietly();

        return response()->json([
            'message' => 'Your password has been change.'
        ], Response::HTTP_OK);
    }
}
