<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ShopRepository;
use App\Repositories\Contracts\ValueChainRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ValueChainController extends Controller
{
    public function __construct(
        private ValueChainRepository $valueChainRepository,
        private ShopRepository $shopRepository,
    ) {
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
}
