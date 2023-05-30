<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUsersCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Repositories\Contracts\CompanyRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class UsersCompanyController extends Controller
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private UserRepository $userRepository
    ) {}

    /**
     * Show current user's company and company's teams.
     */
    public function show(Request $request): JsonResource
    {
        return new CompanyResource($this->userRepository->getUsersCompany($request->user()));
    }

    /**
     * Update current user's company in storage.
     */
    public function update(UpdateUsersCompanyRequest $request): JsonResource|JsonResponse
    {
        $company = $this->companyRepository->update(
            $request->validated(),
            $request->user()->company,
            $request->user()
        );

        return $company ? new CompanyResource($company) : response()->json([
            'message' => __('Updated failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }
}
