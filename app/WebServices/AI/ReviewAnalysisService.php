<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\ItemsData;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReviewAnalysisService extends Service
{
    use HasMqDateTimeHandler;

    /**
     * Get review analysis summary by store_id.
     */
    public function getReviewSummary($storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getYearMonthReviewSummary($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $lastMonthStart = $dateRangeFilter['from_date']->subMonth()->format('Y-m-d');
        $lastMonthEnd = $dateRangeFilter['to_date']->subMonth()->format('Y-m-d');
        $lastMonthStartStr = str_replace('-', '', date('Ymd', strtotime($lastMonthStart)));
        $lastMonthEndStr = str_replace('-', '', date('Ymd', strtotime($lastMonthEnd)));

        $totalReview = ItemsData::where('store_id', $storeId)
            ->whereRaw('
            (date >= ? AND date <= ? ) 
            OR (date >= ? AND date <= ? )
            ', [$fromDateStr, $toDateStr, $lastMonthStartStr, $lastMonthEndStr])
            ->selectRaw('
                    SUM(CASE WHEN date >= ? AND date <= ? THEN review_all ELSE 0 END) as current_month_count,
                    SUM(CASE WHEN date >= ? AND date <= ? THEN review_all ELSE 0 END) as previous_month_count
            ', [$fromDateStr, $toDateStr, $lastMonthStartStr, $lastMonthEndStr])
            ->groupBy('store_id')
            ->first();
        $totalReview = ! is_null($totalReview) ? $totalReview->toArray() : [];
        $currentMonthReview = intval(Arr::get($totalReview, 'current_month_count', 0));
        $previousMonthReview = intval(Arr::get($totalReview, 'previous_month_count', 0));

        $totalPurchase = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->selectRaw('SUM(item_all.all_purchaser_all) as total_purchase')
            ->groupBy('store_id')
            ->first();
        $totalPurchase = ! is_null($totalPurchase) ? $totalPurchase->toArray() : [];
        $totalPurchaseNum = Arr::get($totalPurchase, 'total_purchase', 0);

        $data = collect();
        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'cumulative_review_all' => $currentMonthReview,
            'review_writing_rate' => $totalPurchaseNum > 0 ? round($currentMonthReview / $totalPurchaseNum, 2) : 0,
            'new_review_all' => ($currentMonthReview - $previousMonthReview) > 0 ? ($currentMonthReview - $previousMonthReview) : 0,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get data chart reviews trend.
     */
    public function getChartReviewsTrends($storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getChartYearMonthReviewsTrends($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $result = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->selectRaw('date, SUM(review_all) as total_review, SUM(item_all.all_purchaser_all) as total_purchase')
            ->groupBy('store_id', 'date')
            ->get();
        $result = ! is_null($result) ? $result->groupBy('date')->toArray() : [];

        $data = collect();

        foreach ($result as $date => $reviewDataItem) {
            $totalReview = intval(Arr::get($reviewDataItem[0] ?? [], 'total_review', 0));
            $totalPurchase = intval(Arr::get($reviewDataItem[0] ?? [], 'total_purchase', 0));
            $data->add([
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
                'review_all' => $totalReview,
                'review_writing_rate' => $totalPurchase > 0 ? round($totalReview / $totalPurchase, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get review analysis summary by store_id.
     */
    private function getYearMonthReviewSummary($storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $lastMonthStart = $dateRangeFilter['from_date']->subMonth()->format('Y-m');
        $lastMonthEnd = $dateRangeFilter['to_date']->subMonth()->format('Y-m');
        $lastMonthStartStr = str_replace('-', '', date('Ym', strtotime($lastMonthStart)));
        $lastMonthEndStr = str_replace('-', '', date('Ym', strtotime($lastMonthEnd)));

        $totalReview = ItemsData::where('store_id', $storeId)
            ->whereRaw('
            (SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? ) 
            OR (SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? )
            ', [$fromDateStr, $toDateStr, $lastMonthStartStr, $lastMonthEndStr])
            ->selectRaw('
                    SUM(CASE WHEN SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? THEN review_all ELSE 0 END) as current_month_count,
                    SUM(CASE WHEN SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? THEN review_all ELSE 0 END) as previous_month_count
            ', [$fromDateStr, $toDateStr, $lastMonthStartStr, $lastMonthEndStr])
            ->groupBy('store_id')
            ->first();
        $totalReview = ! is_null($totalReview) ? $totalReview->toArray() : [];
        $currentMonthReview = intval(Arr::get($totalReview, 'current_month_count', 0));
        $previousMonthReview = intval(Arr::get($totalReview, 'previous_month_count', 0));

        $totalPurchase = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->selectRaw('SUM(item_all.all_purchaser_all) as total_purchase')
            ->groupBy('store_id')
            ->first();
        $totalPurchase = ! is_null($totalPurchase) ? $totalPurchase->toArray() : [];
        $totalPurchaseNum = Arr::get($totalPurchase, 'total_purchase', 0);

        $data = collect();
        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'cumulative_review_all' => $currentMonthReview,
            'review_writing_rate' => $totalPurchaseNum > 0 ? round($currentMonthReview / $totalPurchaseNum, 2) : 0,
            'new_review_all' => ($currentMonthReview - $previousMonthReview) > 0 ? ($currentMonthReview - $previousMonthReview) : 0,
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get data chart reviews trend by year-month.
     */
    public function getChartYearMonthReviewsTrends($storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $result = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->selectRaw('SUBSTRING(date, 1, 6) as date, SUM(review_all) as total_review, SUM(item_all.all_purchaser_all) as total_purchase')
            ->groupBy('store_id', DB::raw('SUBSTRING(date, 1, 6)'))
            ->get();
        $result = ! is_null($result) ? $result->groupBy('date')->toArray() : [];

        $data = collect();

        foreach ($result as $date => $reviewDataItem) {
            $totalReview = intval(Arr::get($reviewDataItem[0] ?? [], 'total_review', 0));
            $totalPurchase = intval(Arr::get($reviewDataItem[0] ?? [], 'total_purchase', 0));
            $data->add([
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
                'review_all' => $totalReview,
                'review_writing_rate' => $totalPurchase > 0 ? round($totalReview / $totalPurchase, 2) : 0,
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }
}
