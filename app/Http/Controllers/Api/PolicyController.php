<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RunSimulationRequest;
use App\Http\Requests\StorePolicySimulationRequest;
use App\Http\Requests\UpdatePolicySimulationRequest;
use App\Http\Resources\PolicyResource;
use App\Models\Policy;
use App\Repositories\Contracts\JobGroupRepository;
use App\Repositories\Contracts\PolicyRepository;
use App\Support\PolicyCsv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PolicyController extends Controller
{
    public function __construct(
        protected PolicyRepository $policyRepository,
        protected JobGroupRepository $jobGroupRepository,
        protected PolicyCsv $policyCsv,
    ) {
    }

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
     * Display the specified policy.
     */
    public function show(Policy $policy)
    {
        return new PolicyResource($policy);
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
    public function destroy(Policy $policy): JsonResource|JsonResponse
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
        $jobGroups = [];

        foreach ($request->post() as $index => $data) {
            if (Arr::get($data, 'control_actions') == Policy::REMOVE_ACTION) {
                $result = $this->policyRepository->delete($this->policyRepository->find(Arr::get($data, 'policy_id')));

                continue;
            }

            $validated = $this->policyRepository->handleValidation($data + ['store_id' => $storeId], $index);

            if (isset($validated['error'])) {
                $errors[] = $validated['error'];
                $status = $status != Response::HTTP_BAD_REQUEST
                    ? Response::HTTP_UNPROCESSABLE_ENTITY
                    : Response::HTTP_BAD_REQUEST;
                $numberFailures++;

                continue;
            }

            if (Arr::get($validated, 'policy.control_actions') == Policy::CREATE_ACTION) {
                $result = $this->policyRepository->create($validated, $storeId);

                if (is_null($result)) {
                    $errors[] = [
                        'index' => $index,
                        'row' => $index + 1,
                        'messages' => [
                            'record' => "Something went wrong! Can't create policy."
                        ],
                    ];
                    $status = Response::HTTP_BAD_REQUEST;
                    $numberFailures++;
                } else {
                    $this->jobGroupRepository->handleStartEndTime(
                        Arr::get($result, 'job_group_id'),
                        $data,
                        $jobGroups
                    );
                }
            }

            if (Arr::get($validated, 'policy.control_actions') == Policy::EDIT_ACTION) {
                $result = $this->policyRepository->update($validated, Policy::find(Arr::get($validated, 'policy.policy_id')));

                if (is_null($result)) {
                    $errors[] = [
                        'index' => $index,
                        'row' => $index + 1,
                        'messages' => [
                            'record' => "Something went wrong! Can't create policy."
                        ],
                    ];
                    $status = Response::HTTP_BAD_REQUEST;
                    $numberFailures++;
                }
            }
        }

        $this->jobGroupRepository->updateTime($jobGroups);

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Stores a newly created simulation policy in storage.
     */
    public function storeSimulation(StorePolicySimulationRequest $request, string $storeId): JsonResource|JsonResponse
    {
        $policySimulation = $this->policyRepository->createSimulation($request->validated(), $storeId);

        return $policySimulation
            ? (new PolicyResource($policySimulation))->response($request)->setStatusCode(Response::HTTP_CREATED)
            : response()->json([
                'message' => __('Created failure.'),
            ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update the specified simulation in storage.
     */
    public function updateSimulation(
        UpdatePolicySimulationRequest $request,
        Policy $policySimulation
    ): JsonResource|JsonResponse {
        $policySimulation = $this->policyRepository->updateSimulation($request->validated(), $policySimulation);

        return $policySimulation ? new PolicyResource($policySimulation) : response()->json([
            'message' => __('Updated failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /*
    * Delete multiple policies at the same time.
    */
    public function deleteMultiple(Request $request): JsonResponse
    {
        $result = $this->policyRepository->deleteMultiple($request->query('policy_ids', []));

        return ! $result
            ? response()->json([
                'message' => __('Delete failed. Please check your policy ids!'),
                'policy_ids' => $request->input('policy_ids', []),
            ], Response::HTTP_BAD_REQUEST)
            : response()->json([
                'message' => __('The policy have been deleted successfully.'),
            ]);
    }

    /**
     * Download a template csv file.
     */
    public function downloadTemplateCsv(Request $request, string $storeId): StreamedResponse
    {
        return response()->stream(callback: $this->policyCsv->streamCsvFile($storeId, $request->query()), headers: [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=policy_template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    /**
     * Run policy simulation.
     */
    public function runSimulation(RunSimulationRequest $request): JsonResponse
    {
        $this->policyRepository->runSimulation($request->validated());

        return response()->json([
            'message' => 'Policy simulation is running...',
        ]);
    }

    /**
     * Get list of work breakdown structure.
     */
    public function workBreakdownStructure(Request $request, string $storeId): JsonResponse
    {
        $result = $this->policyRepository->workBreakdownStructure($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Display the specified simulation.
     */
    public function showSimulation(Policy $policySimulation)
    {
        return new PolicyResource($policySimulation);
    }
}
