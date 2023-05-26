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

        return __('Successful Verification.');
    }
}
