<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Traits\PasswordValidationRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    use PasswordValidationRules;

    /**
     * Handle reset password.
     */
    public function reset(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle update password.
     */
    public function update(Request $request)
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

        $message = __($status);

        return <<<HTML
        <html>
        <head><title>Password reset</title></head>
        <body style="font-size: 16px;font-family: Consolas, monaco, monospace;">
        <div style="width: 100vw;height: 100vh;display: flex;justify-content: center;align-items: center">
            <p style="font-size: 2.5rem;color: #d1d5db">$message</p>
        </div>
        </body>
        </html>
        HTML;
    }
}
