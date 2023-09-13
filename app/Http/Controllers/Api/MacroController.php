<?php

namespace App\Http\Controllers\Api;

use App\Constants\MacroConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetQueryConditionsResultsRequest;
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
use Illuminate\Support\Facades\Validator;

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
        $tableLabels = $this->macroConfigurationRepository->getTableLabels();

        return response()->json([
            'tables' => $tables,
            'table_labels' => $tableLabels,
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
    public function store(Request $request): JsonResource|JsonResponse
    {
        $validation = $this->validationMultipleData($request, $request->all());

        if (! empty(Arr::get($validation, 'errors', []))) {
            return response()->json(Arr::get($validation, 'errors'), Response::HTTP_BAD_REQUEST);
        }

        $data = Arr::get($validation, 'validated', []) + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ];

        $macroConfiguration = $this->macroConfigurationRepository->create($data);

        return $macroConfiguration
            ? new MacroConfigurationResource($macroConfiguration)
            : response()->json([
                'message' => __('Created failure.'),
            ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update macro configuration.
     */
    public function update(
        Request $request,
        MacroConfiguration $macroConfiguration
    ): JsonResource|JsonResponse {
        $validation = $this->validationMultipleData($request, $request->all() + [
            'macro_type' => $macroConfiguration->macro_type,
            'macroConfiguration' => $macroConfiguration,
        ]);

        if (! empty(Arr::get($validation, 'errors', []))) {
            return response()->json(Arr::get($validation, 'errors'), Response::HTTP_BAD_REQUEST);
        }

        $data = Arr::get($validation, 'validated', []) + [
            'updated_by' => $request->user()->id,
        ];

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
    private function validationMultipleData(Request $request, array $data): array
    {
        $requestRule = MacroConfigurationRequest::create('');
        $requestRule->initialize(query: $data, request: $data);
        $validator = Validator::make($data, $requestRule->rules());
        $bagErrors = [
            'message' => 'There are a few failures.',
        ];

        if ($validator->fails()) {
            $bagErrors = array_merge($bagErrors, [
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $errors = [];
        $key = 'errors';

        $macroType = Arr::get($data, 'macro_type');

        if ($macroType == MacroConstant::MACRO_TYPE_POLICY_REGISTRATION) {
            $policies = Arr::get($data, 'policies', []);
            $key = 'policies';

            foreach ($policies as $index => $policy) {
                $validated = $this->policyRepository->handleValidation(
                    $policy + ['store_id' => Arr::get($data, 'store_ids')],
                    $index
                );

                if (isset($validated['error'])) {
                    $errors[] = $validated['error'];
                }
            }
        } elseif ($macroType == MacroConstant::MACRO_TYPE_TASK_ISSUE) {
            $tasks = Arr::get($data, 'tasks', []);
            $key = 'tasks';

            foreach ($tasks as $index => $task) {
                $validated = $this->taskRepository->handleValidation($task, $index);

                if (isset($validated['error'])) {
                    $errors[] = $validated['error'];
                }
            }
        }

        if (! empty($errors)) {
            $bagErrors['errors'] = array_merge(Arr::get($bagErrors, 'errors', []), [$key => $errors]);
        }

        return ! Arr::has($bagErrors, 'errors') ? [
            'validated' => $validator->validated(),
        ] : [
            'errors' => $bagErrors,
        ];
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
    public function getQueryConditionsResults(GetQueryConditionsResultsRequest $request)
    {
        $conditions = json_decode($request->getContent(), true);
        $result = $this->macroConfigurationRepository->getQueryConditionsResults($conditions);

        return response()->json($result);
    }
}
