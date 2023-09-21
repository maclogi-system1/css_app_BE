<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadShopSettingMqAccountingCsvRequest;
use App\Repositories\Contracts\ShopSettingMqAccountingRepository;
use App\Support\ShopSettingMqAccountingCsv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShopSettingController extends Controller
{
    public function __construct(
        protected ShopSettingMqAccountingCsv $shopSettingMqAccountingCsv
    ) {
    }

    /**
     * Handle download template csv file.
     */
    public function downloadTemplateMQAccountingCsv(): StreamedResponse
    {
        return response()->stream(callback: $this->shopSettingMqAccountingCsv->streamCsvFile(), headers: [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=mq_accounting_shop_setting_template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    /**
     * Handle uploaded csv file and save it to the database.
     */
    public function uploadMQAccountingCsv(string $storeId, UploadShopSettingMqAccountingCsvRequest $request): JsonResponse
    {
        [$results, $errors] = $this->shopSettingMqAccountingCsv
            ->importMqAccountingSettingCSV($storeId, $request->file('file'));

        $numberFailures = count($errors);

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'data' => $results,
            'errors' => $errors,
        ]);
    }

    public function getMQAccountingSettings(Request $request)
    {
        /** @var ShopSettingMqAccountingRepository $shopSettingMqAccountingRepo */
        $shopSettingMqAccountingRepo = resolve(ShopSettingMqAccountingRepository::class);

        return $shopSettingMqAccountingRepo->getList($request->all(), ['shop_setting_mq_accounting.*']);
    }
}
