<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    public function loginCompany(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required'],
            'password' => ['required'],
        ]);

        $company = Company::where('company_id', $data['company_id'])->first();

        if (! $company || ! Hash::check($data['password'], $company->password)) {
            throw ValidationException::withMessages([
                'company_id' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'company' => new CompanyResource($company),
            'access_token' => $company->createToken($company->company_id, ['*'], now()->addMinute(5))->plainTextToken,
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
            'company_access_token' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->first();
        $accessToken = PersonalAccessToken::findToken($data['company_access_token']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (is_null($accessToken) || ! $this->verifyCompanyAccessToken($accessToken, $user->company->company_id)) {
            throw ValidationException::withMessages([
                'company_access_token' => ['The provided credentials are incorrect.'],
            ]);
        }

        $accessToken->delete();

        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $user->createToken('maclogi_css_user', ['*'], now()->addDay())->plainTextToken,
        ]);
    }

    /**
     * Verify access token of company.
     */
    private function verifyCompanyAccessToken(PersonalAccessToken $accessToken, $companyId): bool
    {
        if (
            $accessToken->name != $companyId
            || $accessToken->tokenable_type != Company::class
            || (! $accessToken->expires_at && $accessToken->created_at->lte(now()->subMinutes(5)))
            || ($accessToken->expires_at && $accessToken->expires_at->isPast())
        ) {
            $accessToken->delete();

            return false;
        }

        return true;
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
