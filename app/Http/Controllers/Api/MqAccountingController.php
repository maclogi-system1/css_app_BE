<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetCumlativeChangeInRevenueAndProfitRequest;
use App\Http\Requests\GetMqAnalysisRequest;
use App\Http\Requests\GetMqBreakEvenPointRequest;
use App\Http\Requests\GetMqInferredAndExpectedMqSalesRequest;
use App\Http\Requests\GetMqTotalParamRequest;
use App\Http\Requests\UpdateMqAccountingRequest;
use App\Http\Requests\UploadMqAccountingCsvRequest;
use App\Imports\MqAccountingImport;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\MqChartRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MqAccountingController extends Controller
{
    public function __construct(
        private MqAccountingRepository $mqAccountingRepository,
        private MqChartRepository $mqChartRepository
    ) {
    }

    /**
     * Get a listing of the "mq_accounting" by store id (corresponds to shop_url in OSS).
     */
    public function getListByStore(Request $request, $storeId): JsonResponse
    {
        $result = $this->mqAccountingRepository->getListCompareActualsWithExpectedValues($storeId, $request->query());

        return response()->json($result);
    }

    /**
     * Update metrics of mq_accounting by store id (corresponds to shop_url in OSS).
     */
    public function updateByStore(UpdateMqAccountingRequest $request, $storeId): JsonResponse
    {
        $numberFailures = 0;
        $errors = [];
        $status = Response::HTTP_OK;
        $mqSheetId = $request->input('mq_sheet_id');

        foreach ($request->post() as $data) {
            $validated = $this->mqAccountingRepository->handleValidationUpdate(
                $data + ['mq_sheet_id' => $mqSheetId],
                $storeId
            );

            if (isset($validated['error'])) {
                $errors[] = $validated['error'];
                $numberFailures++;
                $status = Response::HTTP_UNPROCESSABLE_ENTITY;

                continue;
            }

            $result = $this->mqAccountingRepository->updateOrCreate(
                $this->mqAccountingRepository->getDataForUpdate($validated['data']),
                $storeId
            );

            if (is_null($result)) {
                $numberFailures++;
                $errors[] = [
                    'store_id' => $storeId,
                    'year' => Arr::get($data, 'year'),
                    'month' => Arr::get($data, 'month'),
                    'messages' => "Something went wrong! Can't update or insert data.",
                ];
                $status = Response::HTTP_BAD_REQUEST;
            }
        }

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Download a template csv file.
     */
    public function downloadTemplateCsv(Request $request): StreamedResponse
    {
        $filters = [
            'options' => $this->mqAccountingRepository->getShowableRows(),
        ] + $request->only(['from_date', 'to_date']);

        return response()->stream($this->mqAccountingRepository->streamCsvFile($filters), Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=mq_accounting.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    /**
     * Download a csv file containing the data of mq_accounting by store_id and by time period.
     */
    public function downloadMqAccountingCsv(Request $request, $storeId): StreamedResponse
    {
        $filters = $request->only(['from_date', 'to_date', 'options'])
            + ['options' => $this->mqAccountingRepository->getShowableRows()];

        return response()->stream($this->mqAccountingRepository->streamCsvFile($filters, $storeId), Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=mq_accounting.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    /**
     * Download a csv file containing the data of mq_accounting by store_id and by time period with selection fields.
     */
    public function downloadMqAccountingCsvSelection(Request $request, $storeId): StreamedResponse
    {
        $filters = $request->only(['from_date', 'to_date', 'options']);
        $filters['options'][] = 'reserve1';
        $filters['options'][] = 'reserve2';

        return response()->stream($this->mqAccountingRepository->streamCsvFile($filters, $storeId), Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=mq_accounting.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    /**
     * Upload a mq_accounting file for update an existing resource or create a new resource.
     */
    public function uploadMqAccountingCsv(UploadMqAccountingCsvRequest $request, $storeId): JsonResponse
    {
        $mqSheetId = $request->input('mq_sheet_id');
        $rows = Excel::toArray(new MqAccountingImport(), $request->file('mq_accounting'))[0];
        $dataReaded = $this->mqAccountingRepository->readAndParseCsvFileContents($rows);
        $numberFailures = 0;
        $errors = [];
        $status = Response::HTTP_OK;

        foreach ($dataReaded as $data) {
            $validated = $this->mqAccountingRepository->handleValidationUpdate(
                $data + ['mq_sheet_id' => $mqSheetId],
                $storeId
            );

            if (isset($validated['error'])) {
                $errors[] = $validated['error'];
                $numberFailures++;
                $status = Response::HTTP_UNPROCESSABLE_ENTITY;

                continue;
            }

            $result = $this->mqAccountingRepository->updateOrCreate(
                $this->mqAccountingRepository->getDataForUpdate($validated['data']),
                $storeId
            );

            if (is_null($result)) {
                $errors[] = [
                    'store_id' => $storeId,
                    'year' => Arr::get($data, 'year'),
                    'month' => Arr::get($data, 'month'),
                    'messages' => __("Something went wrong! Can't update or insert data."),
                ];
                $numberFailures++;
                $status = Response::HTTP_BAD_REQUEST;
            }
        }

        return response()->json([
            'message' => $numberFailures > 0 ? __('There are a few failures.') : __('Success.'),
            'number_of_failures' => $numberFailures,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Get the chart information of the month-to-month change of financial indicators.
     */
    public function financialIndicatorsMonthly(Request $request, $storeId): JsonResponse
    {
        $chartMonthly = $this->mqChartRepository->financialIndicatorsMonthly($storeId, $request->query());

        return response()->json([
            'changes_financial_indicators_monthly' => $chartMonthly,
        ]);
    }

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function cumulativeChangeInRevenueAndProfit(
        GetCumlativeChangeInRevenueAndProfitRequest $request,
        string $storeId,
    ): JsonResponse {
        $chartMonthly = $this->mqChartRepository->cumulativeChangeInRevenueAndProfit($storeId, $request->query());

        return response()->json($chartMonthly);
    }

    /**
     * Get total sale amount, cost and profit by store id.
     */
    public function getTotalParamByStore(GetMqTotalParamRequest $request, $storeId): JsonResponse
    {
        $totalParam = $this->mqAccountingRepository->getTotalParamByStore($storeId, $request->validated());

        return response()->json([
            'total_param' => $totalParam,
        ]);
    }

    /**
     * Get forecast vs actual.
     */
    public function getForecastVsActual(Request $request, $storeId): JsonResponse
    {
        $filters = $request->query();

        if ($year = Arr::pull($filters, 'year')) {
            $filters = [
                'from_date' => "{$year}-01",
                'to_date' => "{$year}-12",
            ];
        }

        $result = $this->mqAccountingRepository->getForecastVsActual($storeId, $filters);

        return response()->json($result);
    }

    /**
     * Get comparative analysis.
     */
    public function getComparativeAnalysis(GetMqAnalysisRequest $request, $storeId): JsonResponse
    {
        $result = $this->mqAccountingRepository->getComparativeAnalysis($storeId, $request->validated());

        return response()->json($result);
    }

    /**
     * Calculate and get the break-even point.
     */
    public function getBreakEvenPoint(GetMqBreakEvenPointRequest $request, string $storeId): JsonResponse
    {
        $breakEvenPoint = $this->mqChartRepository->getBreakEvenPoint($storeId, $request->validated());

        return response()->json([
            'break_even' => $breakEvenPoint,
        ]);
    }

    public function getInferredAndExpectedMqSales(GetMqInferredAndExpectedMqSalesRequest $request, string $storeId)
    {
        $expectedAndActualSales = $this->mqChartRepository->getInferredAndExpectedMqSales(
            $storeId,
            $request->validated()
        );

        return response()->json($expectedAndActualSales);
    }
}
