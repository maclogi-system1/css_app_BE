<?php

namespace App\WebServices\AI;

use App\Models\KpiRealData\AccessKeywords;
use App\Models\KpiRealData\ItemsData;
use App\Models\KpiRealData\KeysearchRanking;
use App\Support\Traits\HasMqDateTimeHandler;
use App\WebServices\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportSearchService extends Service
{
    use HasMqDateTimeHandler;

    /**
     * Get daily data of top 10 keywords to display on graph.
     */
    public function getDataReportSearch(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataYearMonthReportSearch($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $topKeywords = AccessKeywords::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select('keyword', DB::raw('SUM(val) as total_count'))
            ->groupBy('keyword')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get();
        $topKeywords = ! is_null($topKeywords) ? $topKeywords->toArray() : [];
        $listTopKeywords = collect($topKeywords)->pluck('keyword')->toArray();

        $dailyData = AccessKeywords::where('store_id', $storeId)
                    ->where('date', '>=', $fromDateStr)
                    ->where('date', '<=', $toDateStr)
                    ->whereIn('keyword', $listTopKeywords)
                    ->select('date', 'keyword', DB::raw('SUM(val) as total_count'))
                    ->orderBy('date')
                    ->groupBy('date', 'keyword')
                    ->orderByDesc('total_count')
                    ->get();
        $dailyData = ! is_null($dailyData) ? $dailyData->groupBy('date')->toArray() : [];

        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            [
                'format' => 'Y/m/d',
                'step' => '1 day',
            ]
        );

        $data = collect();

        foreach ($dateTimeRange as $date) {
            $result = [
                'store_id' => $storeId,
                'date' => $date,
            ];
            $dateString = str_replace('/', '', $date);
            $dailyItem = Arr::get($dailyData, $dateString);
            if (! is_null($dailyItem)) {
                foreach ($dailyItem as $item) {
                    $result = array_merge($result, [
                        Arr::get($item, 'keyword') => Arr::get($item, 'total_count'),
                    ]);
                }
                if (count($result) - 2 < count($listTopKeywords)) {
                    $remainingKeywords = array_diff($listTopKeywords, array_keys($result));
                    foreach ($remainingKeywords as $item) {
                        $result = array_merge($result, [
                            $item => 0,
                        ]);
                    }
                }
                $data->add($result);
            }
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get data of keywords table by date.
     */
    public function getRankingReportSearch(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getRankingYearMonthReportSearch($storeId, $filters);
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

        $lastYearStart = $dateRangeFilter['from_date']->subYear()->format('Y-m-d');
        $lastYearEnd = $dateRangeFilter['to_date']->subYear()->format('Y-m-d');
        $lastYearStartStr = str_replace('-', '', date('Ymd', strtotime($lastYearStart)));
        $lastYearEndStr = str_replace('-', '', date('Ymd', strtotime($lastYearEnd)));

        $results = AccessKeywords::where('store_id', $storeId)
            ->whereRaw('
                (date >= ? AND date <= ? ) 
                OR (date >= ? AND date <= ? )
                OR (date >= ? AND date <= ? )
                ', [$fromDateStr, $toDateStr, $lastMonthStartStr, $lastMonthEndStr, $lastYearStartStr, $lastYearEndStr])
            ->selectRaw('keyword,
                    SUM(CASE WHEN date >= ? AND date <= ? THEN val ELSE 0 END) as current_month_count,
                    SUM(CASE WHEN date >= ? AND date <= ? THEN val ELSE 0 END) as previous_month_count,
                    SUM(CASE WHEN date >= ? AND date <= ? THEN val ELSE 0 END) as previous_year_month_count
            ', [$fromDateStr, $toDateStr, $lastMonthStartStr, $lastMonthEndStr, $lastYearStartStr, $lastYearEndStr])
            ->groupBy('keyword')
            ->orderByDesc('current_month_count')
            ->havingRaw('current_month_count > 0')
            ->get();
        $results = ! is_null($results) ? $results->toArray() : [];

        $totalAccess = AccessKeywords::where('store_id', $storeId)
                            ->where('date', '>=', $fromDateStr)
                            ->where('date', '<=', $toDateStr)
                            ->groupBy('store_id', 'date')
                            ->sum('val');

        $tableReport = [];
        foreach ($results as $result) {
            $keyword = Arr::get($result, 'keyword');
            $currentMonthCount = Arr::get($result, 'current_month_count', 0);
            $previousMonthCount = Arr::get($result, 'previous_month_count', 0);
            $previousYearMonthCount = Arr::get($result, 'previous_year_month_count', 0);

            $diffVsPreviousMonth = $currentMonthCount - $previousMonthCount;
            $previousMonthRate = $previousMonthCount > 0 ?
                                    round(($diffVsPreviousMonth / $previousMonthCount) * 100, 2) :
                                    $diffVsPreviousMonth * 100;
            $diffVsPreviousYearMonth = $currentMonthCount - $previousYearMonthCount;
            $previousYearRate = $previousYearMonthCount > 0 ?
                                    round(($diffVsPreviousYearMonth / $previousYearMonthCount) * 100, 2) :
                                    $diffVsPreviousYearMonth * 100;

            $tableReport[] = [
                'display_name' => $keyword,
                'keyword' => $keyword,
                'value' => intval($currentMonthCount),
                'rate' => $totalAccess > 0 ? round(($currentMonthCount / $totalAccess) * 100, 2) : 0,
                'compare_previous_month' => $previousMonthRate,
                'compare_previous_year' => $previousYearRate,
            ];
        }

        $data = collect();

        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'table_report_search' => collect($tableReport),
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    public function getDataReportSearchByProduct(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));
        $page = 1;
        $perPage = 5;

        /*
        WIP - Updating query product keywords
        $aggregatedData = KeysearchRanking::select(
                'itemid',
                'date',
                'keyword',
                DB::raw('SUM(access_num) as total_access')
            )
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->from(DB::raw("(
                SELECT itemid, date, keyword1 AS keyword, keywordnum1 AS access_num
                FROM keysearch_ranking
                WHERE date >= '".$fromDateStr."' AND date <= '".$toDateStr."'
                UNION ALL
                SELECT itemid, date, keyword2, keywordnum2
                FROM keysearch_ranking
                WHERE date >= '".$fromDateStr."' AND date <= '".$toDateStr."'
                UNION ALL
                SELECT itemid, date, keyword3, keywordnum3
                FROM keysearch_ranking
                WHERE date >= '".$fromDateStr."' AND date <= '".$toDateStr."'
                UNION ALL
                SELECT itemid, date, keyword4, keywordnum4
                FROM keysearch_ranking
                WHERE date >= '".$fromDateStr."' AND date <= '".$toDateStr."'
                UNION ALL
                SELECT itemid, date, keyword5, keywordnum5
                FROM keysearch_ranking
                WHERE date >= '".$fromDateStr."' AND date <= '".$toDateStr."'
            ) as subquery"))
            ->groupBy('itemid', 'date', 'keyword')
            ->get();

        $topKeywords = KeysearchRanking::where('keysearch_ranking.store_id', $storeId)
            ->where('keysearch_ranking.date', '>=', $fromDateStr)
            ->where('keysearch_ranking.date', '<=', $toDateStr)
            ->select(
                'keysearch_ranking.store_id',
                'keysearch_ranking.date',
                'keysearch_ranking.itemid',
                'keyword1',
                'keywordnum1',
                'keyword2',
                'keywordnum2',
                'keyword3',
                'keywordnum3',
                'keyword4',
                'keywordnum4',
                'keyword5',
                'keywordnum5'
            );

        $productResult = ItemsData::where('items_data.store_id', $storeId)
            ->where('items_data.date', '>=', $fromDateStr)
            ->where('items_data.date', '<=', $toDateStr)
            ->distinct('item_id')
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->joinSub($topKeywords, 'keysearch_ranking', function ($join) {
                $join->on('items_data.item_id', '=', 'keysearch_ranking.itemid');
            })
            ->select(
                'item_id',
                'mng_number',
                'item_name',
                'items_data.date',
                DB::raw('MAX(keysearch_ranking.keyword1) as keyword1'),
                DB::raw('MAX(keysearch_ranking.keywordnum1) as keywordnum1'),
                DB::raw('MAX(keysearch_ranking.keyword2) as keyword2'),
                DB::raw('MAX(keysearch_ranking.keywordnum2) as keywordnum2'),
                DB::raw('MAX(keysearch_ranking.keyword3) as keyword3'),
                DB::raw('MAX(keysearch_ranking.keywordnum3) as keywordnum3'),
                DB::raw('MAX(keysearch_ranking.keyword4) as keyword4'),
                DB::raw('MAX(keysearch_ranking.keywordnum4) as keywordnum4'),
                DB::raw('MAX(keysearch_ranking.keyword5) as keyword5'),
                DB::raw('MAX(keysearch_ranking.keywordnum5) as keywordnum5'),
                DB::raw('MAX(visit_all) as max_visit_all'),
            )
            ->groupBy('item_id', 'mng_number', 'item_name', 'items_data.date')
            ->orderByDesc('max_visit_all')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        $productResult = ! is_null($productResult) ? $productResult->toArray() : [];

        $data = collect();
        foreach ($productResult as $index => $item) {
            $totalAccess = intval(Arr::get($item, 'visit_all', 0));

            $data->add([
                'store_id' => $storeId,
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'product' => [
                    'total_access' => $totalAccess,
                    'item_id' => Arr::get($item, 'item_id', ''),
                    'item_name' => Arr::get($item, 'item_name', ''),
                    'ranking' => ($index + 1),
                    'table_report_search_by_product' => collect([
                        [
                            'display_name' => Arr::get($item, 'keyword1', ''),
                            'keyword' => Arr::get($item, 'keyword1', ''),
                            'value' => intval(Arr::get($item, 'keywordnum1', 0)),
                            'rate' => $totalAccess > 0 ? round(Arr::get($item, 'keywordnum1', 0) / $totalAccess * 100, 2) : 0,
                        ],
                        [
                            'display_name' => Arr::get($item, 'keyword2', ''),
                            'keyword' => Arr::get($item, 'keyword2', ''),
                            'value' => intval(Arr::get($item, 'keywordnum2', 0)),
                            'rate' => $totalAccess > 0 ? round(Arr::get($item, 'keywordnum2', 0) / $totalAccess * 100, 2) : 0,
                        ],
                        [
                            'display_name' => Arr::get($item, 'keyword3', ''),
                            'keyword' => Arr::get($item, 'keyword3', ''),
                            'value' => intval(Arr::get($item, 'keywordnum3', 0)),
                            'rate' => $totalAccess > 0 ? round(Arr::get($item, 'keywordnum3', 0) / $totalAccess * 100, 2) : 0,
                        ],
                        [
                            'display_name' => Arr::get($item, 'keyword4', ''),
                            'keyword' => Arr::get($item, 'keyword4', ''),
                            'value' => intval(Arr::get($item, 'keywordnum4', 0)),
                            'rate' => $totalAccess > 0 ? round(Arr::get($item, 'keywordnum4', 0) / $totalAccess * 100, 2) : 0,
                        ],
                        [
                            'display_name' => Arr::get($item, 'keyword5', ''),
                            'keyword' => Arr::get($item, 'keyword5', ''),
                            'value' => intval(Arr::get($item, 'keywordnum5', 0)),
                            'rate' => $totalAccess > 0 ? round(Arr::get($item, 'keywordnum5', 0) / $totalAccess * 100, 2) : 0,
                        ],
                    ]),
                    'chart_report_search_by_product' => '$chartByProduct',
                ],
            ]);
        }
        */

        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            [
                'format' => 'Y/m/d',
                'step' => '1 day',
            ]
        );

        $chartByProduct = collect();
        $dataFake = collect();

        foreach ($dateTimeRange as $date) {
            $chartByProduct->add([
                'date' => $date,
                'keyword_1' => rand(1000, 5000),
                'keyword_2' => rand(1000, 5000),
                'keyword_3' => rand(1000, 5000),
                'keyword_4' => rand(1000, 5000),
                'keyword_5' => rand(1000, 5000),
                'keyword_6' => rand(1000, 5000),
                'keyword_7' => rand(1000, 5000),
                'keyword_8' => rand(1000, 5000),
                'keyword_9' => rand(1000, 5000),
                'keyword_10' => rand(1000, 5000),
            ]);
        }

        for ($i = 0; $i < 15; $i++) {
            $dataFake->add([
                'store_id' => $storeId,
                'from_date' => Arr::get($filters, 'from_date'),
                'to_date' => Arr::get($filters, 'to_date'),
                'product' => [
                    'total_access' => rand(1000, 5000),
                    'item_id' => rand(1000, 5000),
                    'item_name' => '商品名1テキス',
                    'ranking' => 1,
                    'table_report_search_by_product' => collect([
                        [
                            'display_name' => 'キーワード1',
                            'keyword' => 'keyword_1',
                            'value' => rand(1000, 5000),
                            'rate' => rand(3, 20),
                            'conversion_rate' => rand(3, 20),
                        ],
                        [
                            'display_name' => 'キーワード2',
                            'keyword' => 'keyword_2',
                            'value' => rand(1000, 5000),
                            'rate' => rand(3, 20),
                            'conversion_rate' => rand(3, 20),
                        ],
                        [
                            'display_name' => 'キーワード3',
                            'keyword' => 'keyword_3',
                            'value' => rand(1000, 5000),
                            'rate' => rand(3, 20),
                            'conversion_rate' => rand(3, 20),
                        ],
                        [
                            'display_name' => 'キーワード4',
                            'keyword' => 'keyword_4',
                            'value' => rand(1000, 5000),
                            'rate' => rand(3, 20),
                            'conversion_rate' => rand(3, 20),
                        ],
                        [
                            'display_name' => 'キーワード5',
                            'keyword' => 'keyword_5',
                            'value' => rand(1000, 5000),
                            'rate' => rand(3, 20),
                            'conversion_rate' => rand(3, 20),
                        ],
                    ]),
                    'chart_report_search_by_product' => $chartByProduct,
                ],
            ]);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $dataFake,
        ]);
    }

    /**
     * Query origanic inflows data by date
     * オーガニック流入数 = 商品ページ分析のアクセス合計人数-参照元・検索キーワード分析のアクセス人数です。
     */
    public function getDataChartOrganicInflows(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataYearMonthChartOrganicInflows($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $topKeywords = AccessKeywords::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select('keyword', DB::raw('SUM(val) as total_count'))
            ->groupBy('keyword')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get();
        $topKeywords = ! is_null($topKeywords) ? $topKeywords->toArray() : [];
        $listTopKeywords = collect($topKeywords)->pluck('keyword')->toArray();

        $dailyData = AccessKeywords::where('store_id', $storeId)
                    ->where('date', '>=', $fromDateStr)
                    ->where('date', '<=', $toDateStr)
                    ->whereIn('keyword', $listTopKeywords)
                    ->select('date', 'keyword', DB::raw('SUM(val) as total_count'))
                    ->orderBy('date')
                    ->groupBy('date', 'keyword')
                    ->orderByDesc('total_count')
                    ->get();
        $dailyData = ! is_null($dailyData) ? $dailyData->groupBy('date')->toArray() : [];

        $totalAccessResults = ItemsData::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select('date', DB::raw('SUM(item_all.visit_all) as total_access'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        $totalAccessResults = ! is_null($totalAccessResults) ? $totalAccessResults->toArray() : [];

        $data = collect();
        foreach ($dailyData as $date => $keywords) {
            $accessItem = collect($totalAccessResults)->filter(function ($item) use ($date) {
                return $date == Arr::get($item, 'date');
            })->first();
            $dailyCount = Arr::get($accessItem, 'total_access', 0);
            $result = [
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2).'/'.substr($date, 6, 2),
            ];
            foreach ($keywords as $item) {
                $result = array_merge($result, [
                    Arr::get($item, 'keyword') => $dailyCount - Arr::get($item, 'total_count', 0),
                ]);
            }
            $data->add($result);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query data chart inflows via specific words by date.
     */
    public function getDataChartInflowsViaSpecificWords(string $storeId, array $filters = [], bool $isMonthQuery = false): Collection
    {
        if ($isMonthQuery) {
            return $this->getDataYearMonthChartInflowsViaSpecificWords($storeId, $filters);
        }

        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m-d');
        $toDate = $dateRangeFilter['to_date']->format('Y-m-d');
        $fromDateStr = str_replace('-', '', date('Ymd', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ymd', strtotime($toDate)));

        $topKeywords = AccessKeywords::where('store_id', $storeId)
            ->where('date', '>=', $fromDateStr)
            ->where('date', '<=', $toDateStr)
            ->select('keyword', DB::raw('SUM(val) as total_count'))
            ->groupBy('keyword')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get();
        $topKeywords = ! is_null($topKeywords) ? $topKeywords->toArray() : [];
        $listTopKeywords = collect($topKeywords)->pluck('keyword')->toArray();

        $dailyData = AccessKeywords::where('store_id', $storeId)
                    ->where('date', '>=', $fromDateStr)
                    ->where('date', '<=', $toDateStr)
                    ->whereIn('keyword', $listTopKeywords)
                    ->select('date', 'keyword', DB::raw('SUM(val) as total_count'))
                    ->orderBy('date')
                    ->groupBy('date', 'keyword')
                    ->orderByDesc('total_count')
                    ->get();
        $dailyData = ! is_null($dailyData) ? $dailyData->groupBy('date')->toArray() : [];

        $dateTimeRange = $this->getDateTimeRange(
            $dateRangeFilter['from_date'],
            $dateRangeFilter['to_date'],
            [
                'format' => 'Y/m/d',
                'step' => '1 day',
            ]
        );

        $data = collect();

        foreach ($dateTimeRange as $date) {
            $result = [
                'store_id' => $storeId,
                'date' => $date,
            ];
            $dateString = str_replace('/', '', $date);
            $dailyItem = Arr::get($dailyData, $dateString);
            if (! is_null($dailyItem)) {
                foreach ($dailyItem as $item) {
                    $result = array_merge($result, [
                        Arr::get($item, 'keyword') => Arr::get($item, 'total_count'),
                    ]);
                }
                if (count($result) - 2 < count($listTopKeywords)) {
                    $remainingKeywords = array_diff($listTopKeywords, array_keys($result));
                    foreach ($remainingKeywords as $item) {
                        $result = array_merge($result, [
                            $item => 0,
                        ]);
                    }
                }
                $data->add($result);
            }
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get daily data of top 10 keywords to display on graph by year-month.
     */
    private function getDataYearMonthReportSearch(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $topKeywords = AccessKeywords::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->select('keyword', DB::raw('SUM(val) as total_count'))
            ->groupBy('keyword')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get()->toArray();
        $listTopKeywords = collect($topKeywords)->pluck('keyword')->toArray();

        $searchResults = AccessKeywords::where('store_id', $storeId)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
                    ->whereIn('keyword', $listTopKeywords)
                    ->select(DB::raw('SUBSTRING(date, 1, 6) as date'), 'keyword', DB::raw('SUM(val) as total_count'))
                    ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'))
                    ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'keyword')
                    ->orderByDesc('total_count')
                    ->get();
        $searchResults = ! is_null($searchResults) ? $searchResults->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($searchResults as $key => $searchKeywords) {
            $result = [
                'store_id' => $storeId,
                'date' => substr($key, 0, 4).'/'.substr($key, 4, 2),
            ];
            foreach ($searchKeywords as $item) {
                $result = array_merge($result, [
                    Arr::get($item, 'keyword') => Arr::get($item, 'total_count', 0),
                ]);
            }
            $data->add($result);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Get data of keywords table by year-month.
     */
    private function getRankingYearMonthReportSearch(string $storeId, array $filters = []): Collection
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

        $lastYearStart = $dateRangeFilter['from_date']->subYear()->format('Y-m');
        $lastYearEnd = $dateRangeFilter['to_date']->subYear()->format('Y-m');
        $lastYearStartStr = str_replace('-', '', date('Ym', strtotime($lastYearStart)));
        $lastYearEndStr = str_replace('-', '', date('Ym', strtotime($lastYearEnd)));

        $results = AccessKeywords::where('store_id', $storeId)
            ->whereRaw('
                (SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? ) 
                OR (SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? )
                OR (SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? )
                ', [$fromDateStr, $toDateStr, $lastMonthStartStr, $lastMonthEndStr, $lastYearStartStr, $lastYearEndStr])
            ->selectRaw('keyword,
                    SUM(CASE WHEN SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? THEN val ELSE 0 END) as current_month_count,
                    SUM(CASE WHEN SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? THEN val ELSE 0 END) as previous_month_count,
                    SUM(CASE WHEN SUBSTRING(date, 1, 6) >= ? AND SUBSTRING(date, 1, 6) <= ? THEN val ELSE 0 END) as previous_year_month_count
            ', [$fromDateStr, $toDateStr, $lastMonthStartStr, $lastMonthEndStr, $lastYearStartStr, $lastYearEndStr])
            ->groupBy('keyword')
            ->orderByDesc('current_month_count')
            ->havingRaw('current_month_count > 0')
            ->get();
        $results = ! is_null($results) ? $results->toArray() : [];

        $totalAccess = AccessKeywords::where('store_id', $storeId)
                            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
                            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
                            ->groupBy('store_id', 'date')
                            ->sum('val');

        $tableReport = [];
        foreach ($results as $result) {
            $keyword = Arr::get($result, 'keyword');
            $currentMonthCount = Arr::get($result, 'current_month_count', 0);
            $previousMonthCount = Arr::get($result, 'previous_month_count', 0);
            $previousYearMonthCount = Arr::get($result, 'previous_year_month_count', 0);

            $diffVsPreviousMonth = $currentMonthCount - $previousMonthCount;
            $previousMonthRate = $previousMonthCount > 0 ?
                                    round(($diffVsPreviousMonth / $previousMonthCount) * 100, 2) :
                                    $diffVsPreviousMonth * 100;
            $diffVsPreviousYearMonth = $currentMonthCount - $previousYearMonthCount;
            $previousYearRate = $previousYearMonthCount > 0 ?
                                    round(($diffVsPreviousYearMonth / $previousYearMonthCount) * 100, 2) :
                                    $diffVsPreviousYearMonth * 100;

            $tableReport[] = [
                'display_name' => $keyword,
                'keyword' => $keyword,
                'value' => intval($currentMonthCount),
                'rate' => $totalAccess > 0 ? round(($currentMonthCount / $totalAccess) * 100, 2) : 0,
                'compare_previous_month' => $previousMonthRate,
                'compare_previous_year' => $previousYearRate,
            ];
        }

        $data = collect();

        $data->add([
            'store_id' => $storeId,
            'from_date' => Arr::get($filters, 'from_date'),
            'to_date' => Arr::get($filters, 'to_date'),
            'table_report_search' => collect($tableReport),
        ]);

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query origanic inflows data by year-month
     * オーガニック流入数 = 商品ページ分析のアクセス合計人数-参照元・検索キーワード分析のアクセス人数です。
     */
    private function getDataYearMonthChartOrganicInflows(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $topKeywords = AccessKeywords::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->select('keyword', DB::raw('SUM(val) as total_count'))
            ->groupBy('keyword')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get();
        $topKeywords = ! is_null($topKeywords) ? $topKeywords->toArray() : [];
        $listTopKeywords = collect($topKeywords)->pluck('keyword')->toArray();

        $dailyData = AccessKeywords::where('store_id', $storeId)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
                    ->whereIn('keyword', $listTopKeywords)
                    ->select(DB::raw('SUBSTRING(date, 1, 6) as date'), 'keyword', DB::raw('SUM(val) as total_count'))
                    ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'))
                    ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'keyword')
                    ->orderByDesc('total_count')
                    ->get();
        $dailyData = ! is_null($dailyData) ? $dailyData->groupBy('date')->toArray() : [];

        $totalAccessResults = ItemsData::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->join('items_data_all as item_all', 'item_all.items_data_all_id', '=', 'items_data.items_data_all_id')
            ->select(DB::raw('SUBSTRING(date, 1, 6) as date'), DB::raw('SUM(item_all.visit_all) as total_access'))
            ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'))
            ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'))
            ->get();
        $totalAccessResults = ! is_null($totalAccessResults) ? $totalAccessResults->toArray() : [];

        $data = collect();
        foreach ($dailyData as $date => $keywords) {
            $accessItem = collect($totalAccessResults)->filter(function ($item) use ($date) {
                return $date == Arr::get($item, 'date');
            })->first();
            $dailyCount = Arr::get($accessItem, 'total_access', 0);
            $result = [
                'store_id' => $storeId,
                'date' => substr($date, 0, 4).'/'.substr($date, 4, 2),
            ];
            foreach ($keywords as $item) {
                $result = array_merge($result, [
                    Arr::get($item, 'keyword') => $dailyCount - Arr::get($item, 'total_count', 0),
                ]);
            }
            $data->add($result);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }

    /**
     * Query data chart inflows via specific words by year-month.
     */
    private function getDataYearMonthChartInflowsViaSpecificWords(string $storeId, array $filters = []): Collection
    {
        $dateRangeFilter = $this->getDateRangeFilter($filters);
        $fromDate = $dateRangeFilter['from_date']->format('Y-m');
        $toDate = $dateRangeFilter['to_date']->format('Y-m');
        $fromDateStr = str_replace('-', '', date('Ym', strtotime($fromDate)));
        $toDateStr = str_replace('-', '', date('Ym', strtotime($toDate)));

        $topKeywords = AccessKeywords::where('store_id', $storeId)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
            ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
            ->select('keyword', DB::raw('SUM(val) as total_count'))
            ->groupBy('keyword')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get()->toArray();
        $listTopKeywords = collect($topKeywords)->pluck('keyword')->toArray();

        $searchResults = AccessKeywords::where('store_id', $storeId)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '>=', $fromDateStr)
                    ->where(DB::raw('SUBSTRING(date, 1, 6)'), '<=', $toDateStr)
                    ->whereIn('keyword', $listTopKeywords)
                    ->select(DB::raw('SUBSTRING(date, 1, 6) as date'), 'keyword', DB::raw('SUM(val) as total_count'))
                    ->orderBy(DB::raw('SUBSTRING(date, 1, 6)'))
                    ->groupBy(DB::raw('SUBSTRING(date, 1, 6)'), 'keyword')
                    ->orderByDesc('total_count')
                    ->get();
        $searchResults = ! is_null($searchResults) ? $searchResults->groupBy('date')->toArray() : [];

        $data = collect();
        foreach ($searchResults as $key => $searchKeywords) {
            $result = [
                'store_id' => $storeId,
                'date' => substr($key, 0, 4).'/'.substr($key, 4, 2),
            ];
            foreach ($searchKeywords as $item) {
                $result = array_merge($result, [
                    Arr::get($item, 'keyword') => Arr::get($item, 'total_count', 0),
                ]);
            }
            $data->add($result);
        }

        return collect([
            'success' => true,
            'status' => 200,
            'data' => $data,
        ]);
    }
}
