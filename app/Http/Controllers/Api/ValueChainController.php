<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetListValueChainRequest;
use App\Models\ValueChain;
use App\Repositories\Contracts\ShopRepository;
use App\Repositories\Contracts\ValueChainRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ValueChainController extends Controller
{
    public function __construct(
        private ValueChainRepository $valueChainRepository,
        private ShopRepository $shopRepository,
    ) {
    }

    public function getListByStore(GetListValueChainRequest $request, string $storeId)
    {
        $valueChainCollection = $this->valueChainRepository->getListByStore(
            $storeId,
            $request->validated() + ['format_detail' => true]
        );

        $result = $this->valueChainRepository->checkAndSupplementData($valueChainCollection, $request->validated());

        return response()->json($result);
    }

    public function monthlyEvaluation(Request $request, string $storeId): JsonResponse
    {
        $filters = $request->query();
        $currentDate = $request->query('current_date', now()->format('Y-m'));
        $lastMonth = Carbon::create($currentDate)->subMonth()->format('Y-m');

        $chartData = $this->valueChainRepository->monthlyEvaluation($storeId, $filters);
        $chartDataLastMonth = $this->valueChainRepository->monthlyEvaluation(
            $storeId,
            ['current_date' => $lastMonth] + $filters,
        );

        $shopResult = $this->shopRepository->find($storeId);
        $shop = [];
        if ($shopResult->get('success')) {
            $shop = $shopResult->get('data')->get('data');
        }

        $contractDate = Carbon::create(Arr::get($shop, 'contract_date'))->format('Y-m');
        $chartDataContractDate = $this->valueChainRepository->monthlyEvaluation(
            $storeId,
            ['current_date' => $contractDate] + $filters,
        );

        $comprehensiveEvaluation = array_reduce($chartData, function ($carry, $item) {
            $carry += Arr::get($item, 'average', 1);

            return $carry;
        }, 0) / count($chartData);
        $comprehensiveEvaluationLastMonth = array_reduce($chartDataLastMonth, function ($carry, $item) {
            $carry += Arr::get($item, 'average', 1);

            return $carry;
        }, 0) / count($chartDataLastMonth);
        $comprehensiveEvaluationContractDate = array_reduce($chartDataContractDate, function ($carry, $item) {
            $carry += Arr::get($item, 'average', 1);

            return $carry;
        }, 0) / count($chartDataContractDate);

        return response()->json([
            'chart_monthly_evaluation' => $chartData,
            'chart_monthly_evaluation_last_month' => $chartDataLastMonth,
            'chart_monthly_evaluation_contract_date' => $chartDataContractDate,
            'comprehensive_evaluation' => round($comprehensiveEvaluation, 2),
            'comprehensive_evaluation_last_month' => round($comprehensiveEvaluationLastMonth, 2),
            'comprehensive_evaluation_contract_date' => round($comprehensiveEvaluationContractDate, 2),
        ]);
    }

    /**
     * Get the list of monthly evaluation scores for the chart.
     */
    public function chartEvaluate(Request $request, string $storeId)
    {
        $chart = $this->valueChainRepository->chartEvaluate($storeId, $request->query());

        return response()->json([
            'chart' => $chart,
        ]);
    }

    public function getOptions()
    {
        return response()->json($this->valueChainRepository->getOptions());
    }

    public function update(Request $request)
    {
        $numberFailures = 0;
        $errors = [];
        $status = Response::HTTP_OK;

        foreach ($request->post() as $index => $data) {
            $validated = $this->valueChainRepository->handleValidation($data, $index);

            if (isset($validated['error'])) {
                $errors[] = $validated['error'];
                $status = $status != Response::HTTP_BAD_REQUEST
                    ? Response::HTTP_UNPROCESSABLE_ENTITY
                    : Response::HTTP_BAD_REQUEST;
                $numberFailures++;

                continue;
            }

            if ($validated['id']) {
                $result = $this->valueChainRepository->update($validated, ValueChain::find(Arr::get($data, 'id')));
            } else {
                $result = $this->valueChainRepository->create($validated);
            }

            if (is_null($result)) {
                $errors[] = [
                    'index' => $index,
                    'row' => $index + 1,
                    'messages' => [
                        'record' => "Something went wrong! Can't edit value chain.",
                    ],
                ];
                $status = Response::HTTP_BAD_REQUEST;
                $numberFailures++;
            }
        }

        return response()->json([
            'message' => $numberFailures > 0 ? 'There are a few failures.' : 'Success.',
            'number_of_failures' => $numberFailures,
            'errors' => $errors,
        ], $status);
    }
}
