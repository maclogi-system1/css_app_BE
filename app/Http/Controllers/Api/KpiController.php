<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\AccessAnalysisRepository;
use App\Repositories\Contracts\AdsAnalysisRepository;
use App\Repositories\Contracts\MacroConfigurationRepository;
use App\Repositories\Contracts\MqKpiRepository;
use App\Repositories\Contracts\ReportSearchRepository;
use App\Repositories\Contracts\SalesAmntPerUserAnalysisRepository;
use App\Repositories\Contracts\StoreChartRepository;
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
        protected ReportSearchRepository $reportSearchRepository,
        protected AdsAnalysisRepository $adsAnalysisRepository,
        protected MacroConfigurationRepository $macroConfigurationRepository,
        protected AccessAnalysisRepository $accessAnalysisRepository,
        protected StoreChartRepository $storeChartRepository,
        protected SalesAmntPerUserAnalysisRepository $salesAmntPerUserAnalysisRepository
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

    /**
     * Get chart data organic inflows report search keywords from AI.
     */
    public function chartOrganicInflows(Request $request, string $storeId): JsonResponse
    {
        $result = $this->reportSearchRepository->getDataChartOrganicInflows($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get chart data inflows via specific words report search from AI.
     */
    public function chartInflowsViaSpecificWords(Request $request, string $storeId): JsonResponse
    {
        $result = $this->reportSearchRepository->getDataChartInflowsViaSpecificWords($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get advertisement summary.
     */
    public function adsAnalysisSummary(Request $request, string $storeId): JsonResponse
    {
        $adsAnalysisSummary = $this->adsAnalysisRepository->getAdsAnalysisSummary($storeId, $request->query());

        return response()->json(
            $adsAnalysisSummary->get('data'),
            $adsAnalysisSummary->get('status', Response::HTTP_OK)
        );
    }

    /**
     * Get data conversion of advertising effect from AI.
     */
    public function detailAdsConversion(Request $request, string $storeId): JsonResponse
    {
        $result = $this->adsAnalysisRepository->getListAdsConversion($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get data conversion of advertising effect from AI.
     */
    public function getListProductByRoas(Request $request, string $storeId): JsonResponse
    {
        $result = $this->adsAnalysisRepository->getListProductByRoas($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get detail data chart sales and access impact from AI.
     */
    public function chartSalesAndAccess(Request $request, string $storeId): JsonResponse
    {
        $result = $this->adsAnalysisRepository->getDataChartSalesAndAccess($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /*
     * Get detail data chart sales and access impact from AI.
     */
    public function chartMacroGraph(Request $request, string $storeId): JsonResponse
    {
        $result = $this->macroConfigurationRepository->getDataChartMacroGraph($storeId);

        return response()->json($result);
    }

    /**
     * Get detail data table for access analysis screen from AI.
     */
    public function tableAccessAnalysis(Request $request, string $storeId): JsonResponse
    {
        $result = $this->accessAnalysisRepository->getDataTableAccessAnalysis($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get chart data new user access for access analysis screen from AI.
     */
    public function chartNewUserAccess(Request $request, string $storeId): JsonResponse
    {
        $result = $this->accessAnalysisRepository->getDataChartNewUserAccess($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get chart data exist user access for access analysis screen from AI.
     */
    public function chartExistUserAccess(Request $request, string $storeId): JsonResponse
    {
        $result = $this->accessAnalysisRepository->getDataChartExistUserAccess($storeId, $request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get data chart comparison conversion rate from AI.
     */
    public function chartComparisonConversionRate(Request $request): JsonResponse
    {
        $result = $this->storeChartRepository->getDataChartComparisonConversionRate($request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get data table conversion rate analysis from AI.
     */
    public function tableConversionRateAnalysis(Request $request): JsonResponse
    {
        $result = $this->storeChartRepository->getDataTableConversionRateAnalysis($request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get data relation between number of PV and conversion rate from AI.
     */
    public function chartRelationPVAndConversionRate(Request $request): JsonResponse
    {
        $result = $this->storeChartRepository->getDataChartRelationPVAndConversionRate($request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    /**
     * Get data chart summary sale amount per user from AI.
     */
    public function chartSummarySaleAmountPerUser(Request $request, string $storeId): JsonResponse
    {
        $result = $this->salesAmntPerUserAnalysisRepository->getChartSummarySalesAmntPerUser($storeId, $request->query());

        return response()->json($result);
    }

    /**
     * Get data table compare sales amount per user with last year data.
     */
    public function tableSaleAmountPerUserComparison(Request $request, string $storeId): JsonResponse
    {
        $result = $this->salesAmntPerUserAnalysisRepository->getTableSalesAmntPerUserComparison($storeId, $request->query());

        return response()->json($result);
    }

    /**
     * Get data chart PV sale amount per user from AI.
     */
    public function chartPVSaleAmountPerUser(Request $request, string $storeId): JsonResponse
    {
        $result = $this->salesAmntPerUserAnalysisRepository->getChartPVSalesAmntPerUser($storeId, $request->query());

        return response()->json($result);
    }
}
