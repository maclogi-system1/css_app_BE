<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadMqAccountingCsvRequest;
use App\Imports\MqAccountingImport;
use App\Repositories\Contracts\MqAccountingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MqAccountingController extends Controller
{
    public function __construct(
        private MqAccountingRepository $mqAccountingRepository
    ) {}

    public function getListByStore(Request $request, $storeId): JsonResponse
    {
        $actualMqAccounting = $this->mqAccountingRepository->getListFromAIByStore($storeId, $request->query());
        $expectedMqAccounting = $this->mqAccountingRepository->getListByStore($storeId, $request->query());

        return response()->json([
            'actual_mq_accounting' => $actualMqAccounting,
            'expected_mq_accounting' => $expectedMqAccounting,
        ]);
    }

    public function downloadTemplateCsv(Request $request)
    {
        return Storage::disk('local')->download('mq_accounting_template.csv');
    }

    public function downloadMqAccountingCsv(Request $request, $storeId)
    {
        return response()->stream($this->mqAccountingRepository->streamCsvFile($request->query(), $storeId), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=mq_accounting.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

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
}
