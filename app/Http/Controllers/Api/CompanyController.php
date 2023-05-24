<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Repositories\Contracts\CompanyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyRepository $companyRepository
    ) {}

    /**
     * Display a listing of the company.
     */
    public function index(Request $request): JsonResource|JsonResponse
    {
        $this->authorize('view_company');

        $companies = CompanyResource::collection($this->companyRepository->getList($request->query()));
        $companies->wrap('companies');

        return $companies;
    }

    /**
     * Get a listing of the permission by keyword.
     */
    public function search(Request $request): JsonResource|JsonResponse
    {
        $companies = CompanyResource::collection($this->companyRepository->search(
            ['name', 'company_id'],
            $request->query(),
            ['id', 'name', 'company_id']
        ));
        $companies->wrap('companies');

        return $companies;
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(StoreCompanyRequest $request): JsonResource|JsonResponse
    {
        $company = $this->companyRepository->create($request->validated(), $request->user());

        return $company ? new CompanyResource($company) : response()->json([
            'message' => __('Created failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): JsonResource|JsonResponse
    {
        $this->authorize('view_company');

        return new CompanyResource($company);
    }

    /**
     * Update the specified company in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResource|JsonResponse
    {
        $company = $this->companyRepository->update($request->validated(), $company, $request->user());

        return $company ? new CompanyResource($company) : response()->json([
            'message' => __('Updated failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy(Company $company): JsonResource|JsonResponse
    {
        $this->authorize('delete_company');

        $company = $this->companyRepository->delete($company);

        return $company ? new CompanyResource($company) : response()->json([
            'message' => __('Deleted failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Restore deleted company.
     */
    public function restore(Request $request, $id): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json([
                'message' => __('Access deny.'),
            ], Response::HTTP_FORBIDDEN);
        }

        $this->companyRepository->restore($id);

        return response()->json([
            'message' => __('Restore successfully.'),
        ]);
    }

    /**
     * Force delete the specified company.
     */
    public function forceDelete(Request $request, $id): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json([
                'message' => __('Access deny.'),
            ], Response::HTTP_FORBIDDEN);
        }

        $company = Company::withTrashed()->where('id', $id)->first();

        $this->companyRepository->forceDelete($company);

        return response()->json([
            'message' => __('Delete successfully.'),
        ]);
    }
}
