<?php

namespace App\Http\Controllers\Api;

use App\Constants\MacroConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\MacroConfigurationRequest;
use App\Http\Resources\MacroConfigurationResource;
use App\Models\MacroConfiguration;
use App\Repositories\Contracts\MacroConfigurationRepository;
use App\Repositories\Contracts\PolicyRepository;
use App\Repositories\Contracts\TaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class MacroController extends Controller
{
    public function __construct(
        protected MacroConfigurationRepository $macroConfigurationRepository,
        protected PolicyRepository $policyRepository,
        protected TaskRepository $taskRepository,
    ) {
    }

    public function index(Request $request): JsonResource
    {
        $macroConfigurations = MacroConfigurationResource::collection(
            $this->macroConfigurationRepository->getList($request->query())
        );
        $macroConfigurations->wrap('macro_configurations');

        return $macroConfigurations;
    }

    /**
     * Get list table by store Id.
     */
    public function getListTable(): JsonResponse
    {
        $tables = $this->macroConfigurationRepository->getListTable();

        return response()->json([
            'tables' => $tables,
        ], Response::HTTP_OK);
    }

    /**
     * Find specified macro configuration.
     */
    public function show(MacroConfiguration $macroConfiguration): JsonResource
    {
        return new MacroConfigurationResource($macroConfiguration);
    }

    /**
     * Store macro configuration.
     */
    public function store(MacroConfigurationRequest $request): JsonResource|JsonResponse
    {
        $data = $request->validated() + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ];
        $errors = $this->validationMultipleData($data);

        if (! empty($errors)) {
            return response()->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $macroConfiguration = $this->macroConfigurationRepository->create($data);

        if ($macroConfiguration) {
            $queryResult = $this->macroConfigurationRepository->getQueryResults($macroConfiguration);
            $jsonResponse = new MacroConfigurationResource($macroConfiguration);
            $jsonResponse->additional([
                'query_results' => $queryResult->toArray(),
            ]);
        }

        return $macroConfiguration
            ? $jsonResponse
            : response()->json([
                'message' => __('Created failure.'),
            ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update macro configuration.
     */
    public function update(
        MacroConfigurationRequest $request,
        MacroConfiguration $macroConfiguration
    ): JsonResource|JsonResponse {
        $data = $request->validated() + [
            'updated_by' => $request->user()->id,
        ];
        $errors = $this->validationMultipleData($data + ['macro_type' => $macroConfiguration->macro_type]);

        if (! empty($errors)) {
            return response()->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $macroConfiguration = $this->macroConfigurationRepository->update($data, $macroConfiguration);

        return $macroConfiguration
            ? new MacroConfigurationResource($macroConfiguration)
            : response()->json([
                'message' => __('Created failure.'),
            ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Handle multi-data validation by type.
     */
    private function validationMultipleData(array $data): array
    {
        $errors = [];
        $numberFailures = 0;

        $macroType = Arr::get($data, 'macro_type');

        if ($macroType == MacroConstant::MACRO_TYPE_POLICY_REGISTRATION) {
            $policies = Arr::get($data, 'policies', []);

            foreach ($policies as $index => $policy) {
                $validated = $this->policyRepository->handleValidation(
                    $policy + ['store_id' => Arr::get($data, 'store_ids')],
                    $index
                );

                if (isset($validated['error'])) {
                    $errors[] = $validated['error'];
                    $numberFailures++;
                }
            }
        } elseif ($macroType == MacroConstant::MACRO_TYPE_TASK_ISSUE) {
            $tasks = Arr::get($data, 'tasks', []);

            foreach ($tasks as $index => $task) {
                $validated = $this->taskRepository->handleValidation($task, $index);

                if (isset($validated['error'])) {
                    $errors[] = $validated['error'];
                    $numberFailures++;
                }
            }
        }

        if ($numberFailures) {
            return [
                'message' => 'There are a few failures.',
                'number_of_failures' => $numberFailures,
                'errors' => $errors,
            ];
        }

        return [];
    }

    /**
     * Delete specified macro configuration.
     */
    public function destroy(MacroConfiguration $macroConfiguration): JsonResource|JsonResponse
    {
        $macroConfiguration = $this->macroConfigurationRepository->delete($macroConfiguration);

        return $macroConfiguration ? new MacroConfigurationResource($macroConfiguration) : response()->json([
            'message' => __('Deleted failure.'),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Get options for select box.
     */
    public function getOptions(Request $request): JsonResponse
    {
        $options = $this->macroConfigurationRepository->getOptions();

        return response()->json($options);
    }

    /**
     * Get the query results obtained from the conditions of the macro configuration.
     */
    public function getQueryResults(MacroConfiguration $macroConfiguration): JsonResponse
    {
        $result = $this->macroConfigurationRepository->getQueryResults($macroConfiguration);

        return response()->json([
            'result' => $result,
        ]);
    }

    public function run(MacroConfiguration $macroConfiguration): JsonResponse
    {
        $result = $this->macroConfigurationRepository->executeMacro($macroConfiguration);

        return response()->json([
            'message' => $result
                ? 'The macro is ready for scheduled execution.'
                : 'The macro executes immediately and is not scheduled.',
        ]);
    }

    /**
     * Get detail data report search keywords by product from AI.
     */
    public function getKeywords(Request $request): JsonResponse
    {
        $keyword = $request->query('keyword', '');
        $keyword = str_replace(['\'', '"'], '', $keyword);
        $result = $this->macroConfigurationRepository->getKeywords($keyword);

        return response()->json($result);
    }

    /**
     * Get the query results obtained from the json conditions.
     */
    public function getQueryConditionsResults(Request $request)
    {
        $conditions = json_decode($request->getContent(), true);
        $result = $this->macroConfigurationRepository->getQueryConditionsResults($conditions);

        return response()->json($result);
    }
}
