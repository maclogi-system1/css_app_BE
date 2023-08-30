<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MacroConfigurationRequest;
use App\Http\Resources\MacroConfigurationResource;
use App\Models\MacroConfiguration;
use App\Repositories\Contracts\MacroConfigurationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class MacroController extends Controller
{
    public function __construct(
        protected MacroConfigurationRepository $macroConfigurationRepository,
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
        MacroConfigurationRequest $request,
        MacroConfiguration $macroConfiguration
    ): JsonResource|JsonResponse {
        $data = $request->validated() + [
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
}
