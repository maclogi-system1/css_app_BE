<?php

namespace App\WebServices\AI;

use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ReviewAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    /**
     * Get review analysis summary by store_id.
     */
    public function getReviewSummary($storeId, array $filters = []): Collection
    {
        $dataFake = collect();
        $totalReview = rand(10000, 1000000);
        $dataFake->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'cumulative_review_all' => $totalReview,
            'review_writing_rate' => rand(1, 99),
            'new_review_all' => rand(10000, $totalReview),
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Get data chart reviews trend.
     */
    public function getChartReviewsTrends($storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            [
                'format' => 'Y/m/d',
                'step' => '1 day',
            ]
        );

        $dataFake = collect();

        foreach ($dateTimeRange as $date) {
            $dataFake->add([
                'date' => $date,
                'review_all' => rand(10000000, 99999999),
                'review_writing_rate' => rand(1, 99),
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }
}
