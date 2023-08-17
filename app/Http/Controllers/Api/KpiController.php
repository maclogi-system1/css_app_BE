<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MqKpiRepository;
use App\Repositories\Contracts\UserTrendRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KpiController extends Controller
{
    public function __construct(
        protected MqKpiRepository $mqKpiRepository,
        protected UserTrendRepository $userTrendRepository
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

    /**
     * Get data user trends from AI.
     */
    public function chartUserTrends(Request $request, string $storeId): JsonResponse
    {
        $result = $this->userTrendRepository->getDataChartUserTrends($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }
}
