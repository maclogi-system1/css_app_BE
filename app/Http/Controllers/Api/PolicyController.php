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
     * Remove the specified policy from storage.
     */
    public function destroy(Policy $policy) : JsonResource|JsonResponse
    {
        $policy = $this->policyRepository->delete($policy);

        return $policy ? new PolicyResource($policy) : response()->json([
            'message' => __('Deleted failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Stores many newly created policies in storage.
     */
    public function storeMultiple(Request $request, string $storeId): JsonResponse
    {
        $numberFailures = 0;
        $errors = [];
        $status = Response::HTTP_OK;

        foreach ($request->post() as $index => $data) {
            $validated = $this->policyRepository->handleValidation($data, $index);

            if (isset($validated['error'])) {
                $errors[] = $validated['error'];
                $status = $status != Response::HTTP_BAD_REQUEST
                    ? Response::HTTP_UNPROCESSABLE_ENTITY
                    : Response::HTTP_BAD_REQUEST;
                $numberFailures++;
                continue;
            }

            $result = $this->policyRepository->create($data, $storeId);

            if (is_null($result)) {
                $errors[] = [
                    'messages' => "Something went wrong! Can't create policy.",
                ];
                $status = Response::HTTP_BAD_REQUEST;
                $numberFailures++;
            }
        }

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'errors' => $errors,
        ], $status);
    }
}
