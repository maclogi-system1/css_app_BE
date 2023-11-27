<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\AlertRepository;
use App\Support\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AlertController extends Controller
{
    public function __construct(
        private AlertRepository $alertRepository
    ) {
    }

    /**
     * Get a listing of the alert from oss api.
     */
    public function index(Request $request)
    {
        $params = $request->query();
        $params = PermissionHelper::getDataViewShopsWithPermission($request->user(), $params);
        $result = $this->alertRepository->getList($params);

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    public function markAsRead(int $alertId)
    {
        $result = $this->alertRepository->markAsRead($alertId);

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }

    public function createAlert(Request $request)
    {
        $result = $this->alertRepository->createAlert($request->all());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }
}
