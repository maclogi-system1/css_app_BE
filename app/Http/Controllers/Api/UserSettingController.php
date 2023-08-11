<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserSettingRequest;
use App\Http\Resources\UserSettingResource;
use App\Repositories\Contracts\UserSettingRepository;
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
    public function index(Request $request)
    {
        return UserSettingResource::collection($this->userSettingRepository->getSettings($request->user()))
            ->mapWithKeys(function ($item) {
                return [$item->key => $item->value];
            });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserSettingRequest $request)
    {
        $userSettings = $this->userSettingRepository->updateSettings($request->user(), $request->validated());

        return $userSettings ? response()->json([
            'message' => 'Updated successfully.',
        ]) : response()->json([
            'message' => 'Updated failure.',
        ], Response::HTTP_BAD_REQUEST);
    }
}
