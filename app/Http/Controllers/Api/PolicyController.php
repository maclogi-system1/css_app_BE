<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PolicyResource;
use App\Models\Policy;
use App\Repositories\Contracts\PolicyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class PolicyController extends Controller
{
    public function __construct(
        protected PolicyRepository $policyRepository
    ) {}

    /**
     * Get a list of policies by store id in this site.
     */
    public function getListByStore(Request $request, string $storeId): JsonResource|JsonResponse
    {
        $policyCollection = PolicyResource::collection($this->policyRepository->getListByStore(
            $storeId,
            $request->query(),
        ));
        $policyCollection->wrap('policies');

        return $policyCollection;
    }

    /**
     * Get a list of recommendations by store from AI.
     */
    public function getAiRecommendationByStore(Request $request, string $storeId): JsonResource|JsonResponse
    {
        $result = PolicyResource::collection($this->policyRepository->getAiRecommendation($storeId, $request->query()));
        $result->wrap('policies');

        return $result;
    }

    /**
     * Get a list of options for select.
     */
    public function getOptions(): JsonResponse
    {
        $options = $this->policyRepository->getOptions();

        return response()->json($options);
    }

    /**
     * Destroy a recommendations by uuid
     */
    public function destroy(Policy $policy) : JsonResource|JsonResponse
    {
        $policy = $this->policyRepository->delete($policy);

        return $policy ? new PolicyResource($policy) : response()->json([
            'message' => __('Deleted failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }
}
