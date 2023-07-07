<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    ) {}

    /**
     * Get a listing of the "mq_accounting" by store id (corresponds to shop_url in OSS).
     */
    public function getListByStore(Request $request, $storeId): JsonResponse
    {
        $actualMqAccounting = $this->mqAccountingRepository->getListFromAIByStore($storeId, $request->query());
        $expectedMqAccounting = $this->mqAccountingRepository->getListByStore($storeId, $request->query());

        return response()->json([
            'actual_mq_accounting' => $actualMqAccounting,
            'expected_mq_accounting' => $expectedMqAccounting,
        ]);
    }

    /**
     * Update metrics of mq_accounting by store id (corresponds to shop_url in OSS).
     */
    public function updateByStore(Request $request, $storeId): JsonResponse
    {
        $numberFailures = 0;
        $errors = [];

        foreach ($request->post() as $data) {
            $validated = $this->mqAccountingRepository->handleValidationUpdate($data, $storeId);

            if (isset($validated['error'])) {
                $errors[] = $validated['error'];
                $numberFailures++;

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
                    'messages' => 'Error',
                ];
            }
        }

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'errors' => $errors,
        ], count($errors) || $numberFailures ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }

    /**
     * Download a template csv file.
     */
    public function downloadTemplateCsv(Request $request): StreamedResponse
    {
        $filter = [
            'options' => $this->mqAccountingRepository->getShowableRows(),
        ] + $request->only(['from_date', 'to_date']);

        return response()->stream($this->mqAccountingRepository->streamCsvFile($filter), Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=mq_accounting.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    /**
     * Download a csv file containing the data of mq_accounting by store_id and by time period
     */
    public function downloadMqAccountingCsv(Request $request, $storeId): StreamedResponse
    {
        $filter = $request->only(['from_date', 'to_date', 'options'])
            + ['options' => $this->mqAccountingRepository->getShowableRows()];

        return response()->stream($this->mqAccountingRepository->streamCsvFile($filter, $storeId), Response::HTTP_OK, [
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
        $filter = $request->only(['from_date', 'to_date', 'options']);
        $filter['options'][] = 'reserve1';
        $filter['options'][] = 'reserve2';

        return response()->stream($this->mqAccountingRepository->streamCsvFile($filter, $storeId), Response::HTTP_OK, [
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
        $rows = Excel::toArray(new MqAccountingImport(), $request->file('mq_accounting'))[0];
        $dataReaded = $this->mqAccountingRepository->readAndParseCsvFileContents($rows);
        $numberFailures = 0;
        $errors = [];

        foreach ($dataReaded as $data) {
            $validated = $this->mqAccountingRepository->handleValidationUpdate($data, $storeId);

            if (isset($validated['error'])) {
                $errors[] = $validated['error'];
                $numberFailures++;

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
                    'messages' => 'Error',
                ];
                $numberFailures++;
            }
        }

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'errors' => $errors,
        ], count($errors) || $numberFailures ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
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
    public function cumulativeChangeInRevenueAndProfit(Request $request, $storeId): JsonResponse
    {
        $chartMonthly = $this->mqChartRepository->cumulativeChangeInRevenueAndProfit($storeId, $request->query());

        return response()->json($chartMonthly);
    }

    /**
     * Get total sale amount, cost and profit by store id.
     */
    public function getTotalParamByStore(Request $request, $storeId): JsonResponse
    {
        $expectedTotalParam = $this->mqAccountingRepository->getTotalParamByStore($storeId, $request->query());

        return response()->json([
            'total_param' => $expectedTotalParam,
        ]);
    }

    /**
     * Get forecast vs actual.
     */
    public function getForecastVsActual(Request $request, $storeId): JsonResponse
    {
        $result = $this->mqAccountingRepository->getForecastVsActual($storeId, $request->query());

        return response()->json($result);
    }

    /**
     * Get comparative analysis.
     */
    public function getComparativeAnalysis(Request $request, $storeId): JsonResponse
    {
        $year = Arr::get($request->query(), 'year', now()->year);
        $filter = [
            'from_date' => "{$year}-01",
            'to_date' => "{$year}-12",
        ];
        $result = $this->mqAccountingRepository->getForecastVsActual($storeId, $filter);

        return response()->json($result);
    }
}
