<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DownloadShopSettingRankingRequest;
use App\Http\Requests\DownloadShopSettingSearchRankingRequest;
use App\Http\Requests\GetShopSettingRankingRequest;
use App\Http\Requests\GetShopSettingSearchRankingRequest;
use App\Http\Requests\UploadShopSettingAwardPointCsvRequest;
use App\Http\Requests\UploadShopSettingMqAccountingCsvRequest;
use App\Http\Requests\UploadShopSettingRankingCsvRequest;
use App\Http\Requests\UploadShopSettingSearchRankingCsvRequest;
use App\Repositories\Contracts\ShopSettingAwardPointRepository;
use App\Repositories\Contracts\ShopSettingMqAccountingRepository;
use App\Repositories\Contracts\ShopSettingRankingRepository;
use App\Repositories\Contracts\ShopSettingSearchRankingRepository;
use App\Support\ShopSettingAwardPointCsv;
use App\Support\ShopSettingMqAccountingCsv;
use App\Support\ShopSettingRankingCsv;
use App\Support\ShopSettingSearchRankingCsv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShopSettingController extends Controller
{
    public function __construct(
        protected ShopSettingMqAccountingCsv $shopSettingMqAccountingCsv,
        protected ShopSettingRankingCsv $shopSettingRankingCsv,
        protected ShopSettingAwardPointCsv $shopSettingAwardPointCsv,
        protected ShopSettingSearchRankingCsv $shopSettingSearchRankingCsv,
    ) {
    }

    /**
     * Handle download MQAccounting template csv file.
     */
    public function downloadTemplateMQAccountingCsv(): StreamedResponse
    {
        return response()->stream(callback: $this->shopSettingMqAccountingCsv->streamCsvFile(), headers: [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=shop_setting_mq_accounting_template.csv',
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

    public function getMQAccountingSettings(Request $request): JsonResponse
    {
        /** @var ShopSettingMqAccountingRepository $shopSettingMqAccountingRepo */
        $shopSettingMqAccountingRepo = resolve(ShopSettingMqAccountingRepository::class);

        return response()->json([
            'shop_mq_accounting_settings' => $shopSettingMqAccountingRepo->getList($request->all(), ['shop_setting_mq_accounting.*']),
        ]);
    }

    /**
     * Handle download Ranking template csv file.
     */
    public function downloadTemplateRankingCsv(DownloadShopSettingRankingRequest $request): StreamedResponse
    {
        $isCompetitiveRanking = (bool) $request->get('is_competitive_ranking', true);

        return response()->stream(callback: $this->shopSettingRankingCsv->streamCsvFile($isCompetitiveRanking), headers: [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=shop_setting_ranking_template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    /**
     * Handle uploaded csv file and save it to the database.
     */
    public function uploadRankingCsv(string $storeId, UploadShopSettingRankingCsvRequest $request): JsonResponse
    {
        $isCompetitiveRanking = (bool) $request->get('is_competitive_ranking', true);
        [$results, $errors] = $this->shopSettingRankingCsv
            ->importRankingSettingCSV($storeId, $isCompetitiveRanking, $request->file('file'));

        $numberFailures = count($errors);

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'data' => $results,
            'errors' => $errors,
        ]);
    }

    public function getRankingsSettings(GetShopSettingRankingRequest $request): JsonResponse
    {
        /** @var ShopSettingRankingRepository $shopSettingRankingRepo */
        $shopSettingRankingRepo = resolve(ShopSettingRankingRepository::class);

        return response()->json([
            'shop_ranking_settings' => $shopSettingRankingRepo->getList($request->all(), ['shop_setting_rankings.*']),
        ]);
    }

    public function downloadTemplateAwardPointCsv(): StreamedResponse
    {
        return response()->stream(callback: $this->shopSettingAwardPointCsv->streamCsvFile(), headers: [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=shop_setting_award_point_template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    /**
     * Handle uploaded csv file and save it to the database.
     */
    public function uploadAwardPointCsv(string $storeId, UploadShopSettingAwardPointCsvRequest $request): JsonResponse
    {
        [$results, $errors] = $this->shopSettingAwardPointCsv
            ->importAwardPointSettingCSV($storeId, $request->file('file'));

        $numberFailures = count($errors);

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'data' => $results,
            'errors' => $errors,
        ]);
    }

    public function getAwardPointSettings(Request $request): JsonResponse
    {
        /** @var ShopSettingAwardPointRepository $shopSettingAwardPointRepo */
        $shopSettingAwardPointRepo = resolve(ShopSettingAwardPointRepository::class);

        return response()->json([
            'shop_award_point_settings' => $shopSettingAwardPointRepo->getList($request->all(), ['shop_setting_award_points.*']),
        ]);
    }

    /**
     * Handle download Search Ranking template csv file.
     */
    public function downloadTemplateSearchRankingCsv(DownloadShopSettingSearchRankingRequest $request): StreamedResponse
    {
        $isCompetitiveRanking = (bool) $request->get('is_competitive_ranking', true);

        return response()->stream(callback: $this->shopSettingSearchRankingCsv->streamCsvFile($isCompetitiveRanking), headers: [
            'Content-Type' => 'text/csv; charset=shift_jis',
            'Content-Disposition' => 'attachment; filename=shop_setting_search_ranking_template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => 0,
        ]);
    }

    /**
     * Handle uploaded csv file and save it to the database.
     */
    public function uploadSearchRankingCsv(string $storeId, UploadShopSettingSearchRankingCsvRequest $request): JsonResponse
    {
        $isCompetitiveRanking = (bool) $request->get('is_competitive_ranking', true);
        [$results, $errors] = $this->shopSettingSearchRankingCsv
            ->importSearchRankingSettingCSV($storeId, $isCompetitiveRanking, $request->file('file'));

        $numberFailures = count($errors);

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'data' => $results,
            'errors' => $errors,
        ]);
    }

    public function getSearchRankingsSettings(GetShopSettingSearchRankingRequest $request): JsonResponse
    {
        /** @var ShopSettingSearchRankingRepository $shopSettingSearchRankingRepo */
        $shopSettingSearchRankingRepo = resolve(ShopSettingSearchRankingRepository::class);

        return response()->json([
            'shop_search_ranking_settings' => $shopSettingSearchRankingRepo->getList($request->all(), ['shop_setting_search_rankings.*']),
        ]);
    }
}
