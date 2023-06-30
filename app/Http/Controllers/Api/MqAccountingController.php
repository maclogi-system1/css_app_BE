<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadMqAccountingCsvRequest;
use App\Imports\MqAccountingImport;
use App\Repositories\Contracts\MqAccountingRepository;
use App\Repositories\Contracts\MqChartRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
    public function updateByStore(Request $request, $storeId)
    {
        $numberFailures = 0;

        foreach ($request->all() as $data) {
            $rows = $this->mqAccountingRepository->getDataForUpdate($data);
            $result = $this->mqAccountingRepository->updateOrCreate($rows, $storeId);

            if (is_null($result)) {
                $numberFailures++;
            }
        }

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
        ]);
    }

    /**
     * Download a template csv file.
     */
    public function downloadTemplateCsv(Request $request)
    {
        $filter = [
            'options' => $this->mqAccountingRepository->getShowableRows(),
        ] + $request->only(['from_date', 'to_date']);

        return response()->stream($this->mqAccountingRepository->streamCsvFile($filter), 200, [
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
    public function downloadMqAccountingCsv(Request $request, $storeId)
    {
        $filter = $request->only(['from_date', 'to_date', 'options'])
            + ['options' => $this->mqAccountingRepository->getShowableRows()];

        return response()->stream($this->mqAccountingRepository->streamCsvFile($filter, $storeId), 200, [
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
    public function downloadMqAccountingCsvSelection(Request $request, $storeId)
    {
        return response()->stream($this->mqAccountingRepository->streamCsvFile($request->query(), $storeId), 200, [
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
    public function uploadMqAccountingCsv(UploadMqAccountingCsvRequest $request, $storeId)
    {
        $rows = Excel::toArray(new MqAccountingImport(), $request->file('mq_accounting'))[0];
        $dataReaded = $this->mqAccountingRepository->readAndParseCsvFileContents($rows);
        $numberFailures = 0;

        foreach ($dataReaded as $data) {
            $result = $this->mqAccountingRepository->updateOrCreate($data, $storeId);

            if (is_null($result)) {
                $numberFailures++;
            }
        }

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
        ]);
    }

    /**
     * Get the chart information of the month-to-month change of financial indicators.
     */
    public function financialIndicatorsMonthly(Request $request, $storeId)
    {
        $chartMonthly = $this->mqChartRepository->financialIndicatorsMonthly($storeId, $request->query());

        return response()->json([
            'changes_financial_indicators_monthly' => $chartMonthly,
        ]);
    }

    /**
     * Get the cumulative change in revenue and profit.
     */
    public function cumulativeChangeInRevenueAndProfit(Request $request, $storeId)
    {
        $chartMonthly = $this->mqChartRepository->cumulativeChangeInRevenueAndProfit($storeId, $request->query());

        return response()->json($chartMonthly);
    }

    /**
     * Get total sale amount, cost and profit by store id.
     */
    public function getTotalParamByStore(Request $request, $storeId)
    {
        $expectedTotalParam = $this->mqAccountingRepository->getTotalParamByStore($storeId, $request->query()); 

        return response()->json([
            'total_param' => $expectedTotalParam,
        ]);
    }
}
