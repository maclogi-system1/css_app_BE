<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function verifyCompany(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required'],
        ]);

        $company = Company::where('company_id', $data['company_id'])->first();

        if (!$company || $request->user()->company_id == $data['company_id']) {
            $request->user()->currentAccessToken()?->delete();

            throw ValidationException::withMessages([
                'company_id' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'company' => new CompanyResource($company),
        ]);
    }

    /**
     * Handle login.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
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
