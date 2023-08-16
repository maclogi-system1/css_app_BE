<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MqKpiRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    public function __construct(
        protected MqKpiRepository $mqKpiRepository
    ) {
    }

    /**
     * Get KPI summary (KPI target achievement rate, KPI performance summary).
     */
    public function summary(Request $request, string $storeId): JsonResponse
    {
        $kpiSummary = $this->mqKpiRepository->getSummary($storeId, $request->query());

        return response()->json($kpiSummary);
    }
}
