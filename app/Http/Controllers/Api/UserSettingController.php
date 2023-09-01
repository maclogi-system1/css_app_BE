<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserSettingRequest;
use App\Repositories\Contracts\UserSettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserSettingController extends Controller
{
    public function __construct(
        private UserSettingRepository $userSettingRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $userSettings = $this->userSettingRepository->getSettings($request->user());

        return response()->json([
            'user_settings' => $userSettings,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserSettingRequest $request): JsonResponse
    {
        $userSettings = $this->userSettingRepository->updateSettings($request->user(), $request->validated());

        return $userSettings ? response()->json([
            'message' => 'Updated successfully.',
        ]) : response()->json([
            'message' => 'Updated failure.',
        ], Response::HTTP_BAD_REQUEST);
    }
}
