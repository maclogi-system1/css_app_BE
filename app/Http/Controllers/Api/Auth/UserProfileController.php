<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = User::with(['chatwork', 'company', 'teams', 'roles'])
            ->where('id', $request->user()->id)
            ->first();

        return new UserResource($user);
    }
}
