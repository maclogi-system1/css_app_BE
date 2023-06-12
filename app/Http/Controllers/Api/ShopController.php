<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ShopRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(
        private ShopRepository $shopRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->shopRepository->getList($request->query());

        return response()->json($result->get('data'), $result->get('status', Response::HTTP_OK));
    }
}
