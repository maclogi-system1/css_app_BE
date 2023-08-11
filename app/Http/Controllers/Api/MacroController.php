<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                'list_tables' => $result,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('Something went wrong'),
                'detail' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
