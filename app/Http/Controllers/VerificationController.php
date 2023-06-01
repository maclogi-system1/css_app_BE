<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Handle verify email.
     */
    public function verify(Request $request, string $id, string $hash)
    {
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        $user = User::where('id', $id)->whereNull('email_verified_at')->firstOrFail();

        $signatureVerify = $user->getSignatureVerifyEmail($hash, $expires);

        if (! hash_equals($signature, $signatureVerify)) {
            abort(404);
        }

        $user->email_verified_at = now();
        $user->save();

        return <<<HTML
        <html>
        <head><title>Email verification</title></head>
        <body style="font-size: 16px">
        <div style="width: 100vw;height: 100vh;display: flex;justify-content: center;align-items: center">
            <p style="font-size: 3rem">Successful Verification.</p>
        </div>
        </body>
        </html>
        HTML;
    }
}
