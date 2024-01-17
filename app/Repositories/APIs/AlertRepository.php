<?php

namespace App\Repositories\APIs;

use App\Constants\ShopConstant;
use App\Repositories\Contracts\AlertRepository as AlertRepositoryContract;
use App\Repositories\Contracts\LinkedUserInfoRepository;
use App\Repositories\Repository;
use App\WebServices\OSS\AlertService;
use App\WebServices\OSS\ShopService;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class AlertRepository extends Repository implements AlertRepositoryContract
{
    public function __construct(
        private AlertService $alertService,
        private ShopService $shopService,
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return '';
    }

    /**
     * Get the list of the alert from oss api.
     */
    public function getList(array $filters = [], array $columns = ['*'])
    {
        return $this->alertService->getList($filters);
    }

    public function markAsRead(int $alertId)
    {
        return $this->alertService->markAsRead($alertId);
    }

    public function createAlert(array $params)
    {
        $storeId = Arr::get($params, 'store_id');

        if ($storeId == ShopConstant::SHOP_ALL_OPTION) {
            $shopResult = $this->shopService->getList(['per_page' => -1]);

            if ($shopResult->get('success')) {
                $shops = $shopResult->get('data')->get('shops');
                $failedAlerts = $this->handleCreateMultipleAlerts(Arr::pluck($shops, 'store_id'), $params);
                $result = collect([
                    'data' => $failedAlerts,
                    'status' => Response::HTTP_OK,
                ]);

                if (count($failedAlerts)) {
                    $result->put('status', Response::HTTP_BAD_REQUEST);
                }

                return $result;
            }
        } elseif ($storeId == ShopConstant::SHOP_OWNER_OPTION) {
            $shopResult = $this->shopService->getList([
                'per_page' => -1,
                'own_manager' => app(LinkedUserInfoRepository::class)->getOssUserIdByCssUserId(auth()->id()),
            ]);

            if ($shopResult->get('success')) {
                $shops = $shopResult->get('data')->get('shops');
                $failedAlerts = $this->handleCreateMultipleAlerts(Arr::pluck($shops, 'store_id'), $params);
                $result = collect([
                    'data' => $failedAlerts,
                    'status' => Response::HTTP_OK,
                ]);

                if (count($failedAlerts)) {
                    $result->put('status', Response::HTTP_BAD_REQUEST);
                }

                return $result;
            }
        }

        return $this->alertService->createAlert($params);
    }

    private function handleCreateMultipleAlerts(array $storeIds, array $data): array
    {
        $data['store_ids'] = $storeIds;
        unset($data['store_id']);

        $result = $this->alertService->createAlertMultiple($data);
        if (! $result->get('success')) {
            return $result->get('data')->get('data');
        }

        return [];
    }
}
