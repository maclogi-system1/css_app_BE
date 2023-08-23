<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MqKpiRepository;
use App\Repositories\Contracts\ReportSearchRepository;
use App\Repositories\Contracts\UserAccessRepository;
use App\Repositories\Contracts\UserTrendRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KpiController extends Controller
{
    public function __construct(
        protected MqKpiRepository $mqKpiRepository,
        protected UserTrendRepository $userTrendRepository,
        protected UserAccessRepository $userAccessRepository,
        protected ReportSearchRepository $reportSearchRepository
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

    /**
     * Get total user access.
     */
    public function totalUserAccess(Request $request, string $storeId): JsonResponse
    {
        $result = $this->userAccessRepository->getTotalUserAccess($storeId, $request->query());

        return response()->json($result);
    }

    /**
     * Get chart data user access from AI.
     */
    public function chartUserAccess(Request $request, string $storeId): JsonResponse
    {
        $result = $this->userAccessRepository->getDataChartUserAccess($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get chart data user access with ads and none ads from AI.
     */
    public function chartUserAccessAds(Request $request, string $storeId): JsonResponse
    {
        $result = $this->userAccessRepository->getDataChartUserAccessAds($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get chart data access source from AI.
     */
    public function chartAccessSource(Request $request, string $storeId): JsonResponse
    {
        $result = $this->userAccessRepository->getDataChartAccessSource($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get table data access source from AI.
     */
    public function tableAccessSource(Request $request, string $storeId): JsonResponse
    {
        $result = $this->userAccessRepository->getDataTableAccessSource($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get trending keywords data report search for chart from AI.
     */
    public function chartReportSearch(Request $request, string $storeId): JsonResponse
    {
        $result = $this->reportSearchRepository->getDataChartReportSearch($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get ranking keywords data report search for table from AI.
     */
    public function tableReportSearch(Request $request, string $storeId): JsonResponse
    {
        $result = $this->reportSearchRepository->getDataTableReportSearch($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get detail data report search keywords by product from AI.
     */
    public function detailReportSearchByProduct(Request $request, string $storeId): JsonResponse
    {
        $result = $this->reportSearchRepository->getDataReportSearchByProduct($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }
}
