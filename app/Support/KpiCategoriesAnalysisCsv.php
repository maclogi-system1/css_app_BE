<?php

namespace App\Support;

use App\WebServices\AI\CategoryAnalysisService;
use Closure;
use Illuminate\Support\Arr;

class KpiCategoriesAnalysisCsv
{
    public const HEADING = [
        'rank' => ['title' => '順位'],
        'item_id' => ['title' => 'カテゴリID'],
        'item_name' => ['title' => 'カテゴリ名'],
        'sales_all' => ['title' => '売上'],
        'visit_all' => ['title' => 'アクセス人数'],
        'conversion_rate' => ['title' => '転換率'],
        'sales_amnt_per_user' => ['title' => '客単価'],
    ];

    public function __construct(
        protected CategoryAnalysisService $categoryAnalysisService
    ) {
    }

    public function getFields(string $key = 'title'): array
    {
        $header = [];
        foreach (static::HEADING as $field => $item) {
            $header[$field] = static::HEADING[$field][$key];
        }

        return $header;
    }

    /**
     * Return a callback handle stream csv file.
     */
    public function streamCsvFile(array $filters = []): Closure
    {
        $header = $this->getFields('title');
        $storeId = Arr::get($filters, 'store_id');
        $result = $this->categoryAnalysisService->getCategorySummary($storeId, $filters)->get('data');
        $categoryResults = collect();
        if (! is_null($result)) {
            $categoryResults = Arr::get($result, 'categories');
        }

        return function () use ($header, $categoryResults) {
            $file = fopen('php://output', 'w');
            fputcsv($file, convert_fields_to_sjis(array_values($header)));
            foreach ($categoryResults as $categoryItem) {
                $row = [];
                foreach (static::HEADING as $field => $heading) {
                    $row[] = Arr::get($categoryItem, $field);
                }
                fputcsv($file, convert_fields_to_sjis($row));
            }
            fclose($file);
        };
    }
}
