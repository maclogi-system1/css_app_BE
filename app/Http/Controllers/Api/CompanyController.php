<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Repositories\Contracts\CompanyRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyRepository $companyRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->authorize('view_company');

        $companies = CompanyResource::collection($this->companyRepository->getList($request->query()));
        $companies->wrap('companies');

        return $companies;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request): CompanyResource|JsonResponse
    {
        $company = $this->companyRepository->create($request->validated());

        return $company ? new CompanyResource($company) : response()->json([
            'message' => __('Created failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company): CompanyResource|JsonResponse
    {
        $this->authorize('view_company');

        return new CompanyResource($company);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): CompanyResource|JsonResponse
    {
        $company = $this->companyRepository->update($request->validated(), $company);

        return $company ? new CompanyResource($company) : response()->json([
            'message' => __('Updated failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company): CompanyResource|JsonResponse
    {
        $this->authorize('delete_company');

        $company = $this->companyRepository->delete($company);

        return $company ? new CompanyResource($company) : response()->json([
            'message' => __('Deleted failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }
}
