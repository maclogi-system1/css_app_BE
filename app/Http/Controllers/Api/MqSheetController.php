<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetMqSheetRequest;
use App\Http\Requests\StoreMqSheetRequest;
use App\Http\Requests\UpdateMqSheetRequest;
use App\Http\Resources\MqSheetResource;
use App\Models\MqSheet;
use App\Repositories\Contracts\MqSheetRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class MqSheetController extends Controller
{
    public function __construct(
        protected MqSheetRepository $mqSheetRepository
    ) {
    }

    /**
     * Display a listing of the mq sheet.
     */
    public function index(GetMqSheetRequest $request): JsonResource
    {
        $mqSheets = MqSheetResource::collection($this->mqSheetRepository->getListByStore(
            $request->query('store_id'),
            $request->query()
        ));
        $mqSheets->wrap('mq_sheets');

        return $mqSheets;
    }

    /**
     * Store a newly created mq sheet in storage.
     */
    public function store(StoreMqSheetRequest $request): JsonResource|JsonResponse
    {
        $mqSheet = $this->mqSheetRepository->create($request->validated());

        return $mqSheet
            ? new MqSheetResource($mqSheet)
            : response()->json([
                'message' => __('Created failure.'),
            ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Display the specified mq sheet.
     */
    public function show(MqSheet $mqSheet): JsonResource
    {
        return new MqSheetResource($mqSheet);
    }

    /**
     * Update the specified mq sheet in storage.
     */
    public function update(UpdateMqSheetRequest $request, MqSheet $mqSheet): JsonResource|JsonResponse
    {
        $mqSheet = $this->mqSheetRepository->update($request->validated(), $mqSheet);

        return $mqSheet
            ? new MqSheetResource($mqSheet)
            : response()->json([
                'message' => __('Updated failure.'),
            ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified mq sheet from storage.
     */
    public function destroy(MqSheet $mqSheet): JsonResource|JsonResponse
    {
        $mqSheet = $this->mqSheetRepository->delete($mqSheet);

        return $mqSheet
            ? new MqSheetResource($mqSheet)
            : response()->json([
                'message' => __('Deleted failure.'),
            ], Response::HTTP_BAD_REQUEST);
    }
}
