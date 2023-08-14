<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\AlertRepository;
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
        $result = $this->alertRepository->getList($request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }
}
