<?php

namespace App\Http\Controllers\Api;

use App\Constants\MacroConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\MacroConfigurationRequest;
use App\Services\MacroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MacroController extends Controller
{
    public function __construct(
        protected MacroService $macroService,
    ) {
    }

    /**
     * Get list table by store Id.
     */
    public function getListTableByStoreId(string $storeId): JsonResponse
    {
        try {
            $result = $this->macroService->getListTableByStoreId($storeId);

            return response()->json([
                'message' => __('Get list table done'),
                'result' => $result,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('Something went wrong'),
                'detail' => $e->getMessage(),
                'result' => false,
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Find specified macro configuration.
     */
    public function findMacroConfiguration(int $macroConfigurationId): JsonResponse
    {
        try {
            $result = $this->macroService->findMacroConfiguration($macroConfigurationId);

            return response()->json([
                'message' => __('Get macro config done'),
                'result' => $result,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('Something went wrong'),
                'detail' => $e->getMessage(),
                'result' => false,
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Store macro configuration.
     */
    public function storeMacroConfiguration(MacroConfigurationRequest $request): JsonResponse
    {
        try {
            $conditions = $request->get('conditions', null);
            $timeConditions = $request->get('time_conditions', null);
            $macroType = $request->get('macro_type', MacroConstant::MACRO_TYPE_AI);
            $result = $this->macroService->storeMacroConfiguration($conditions, $timeConditions, $macroType);

            return response()->json([
                'message' => __('Configuration have been stored'),
                'result' => $result,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('Something went wrong'),
                'detail' => $e->getMessage(),
                'result' => false,
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update macro configuration.
     */
    public function updateMacroConfiguration(MacroConfigurationRequest $request): JsonResponse
    {
        try {
            $macroConfigurationId = $request->get('id', null);
            if ($macroConfigurationId) {
                $conditions = $request->get('conditions', null);
                $timeConditions = $request->get('time_conditions', null);
                $macroType = $request->get('macro_type', null);
                $result = $this->macroService->updateMacroConfiguration($macroConfigurationId, $conditions, $timeConditions, $macroType);

                return response()->json([
                    'message' => __('Configuration have been updated'),
                    'result' => $result,
                ], Response::HTTP_OK);
            }else{
                return response()->json([
                    'message' => __('Id does not exist'),
                    'result' => false,
                ], Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('Something went wrong'),
                'detail' => $e->getMessage(),
                'result' => false,
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Delete specified macro configuration.
     */
    public function deleteMacroConfiguration(int $macroConfigurationId): JsonResponse
    {
        try {
            $result = $this->macroService->deleteMacroConfiguration($macroConfigurationId);

            return response()->json([
                'message' => __('Configuration have been deleted'),
                'result' => $result,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('Something went wrong'),
                'detail' => $e->getMessage(),
                'result' => false,
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
