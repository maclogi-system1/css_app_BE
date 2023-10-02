<?php

namespace App\Support;

use App\WebServices\AI\AccessAnalysisService;
use Closure;
use Illuminate\Support\Arr;

class KpiAccessCategoryReportCsv
{
    public const HEADING = [
        'display_name' => ['title' => '実績'],
        'click_num' => ['title' => 'アクセス人数'],
        'ctr_rate' => ['title' => '比率'],
        'new_user_sales_num' => ['title' => '新規'],
        'new_user_sales_rate' => ['title' => '新規割合'],
        'exist_user_sales_num' => ['title' => 'リピート'],
        'exist_user_sales_rate' => ['title' => 'リピート割合'],
    ];

    public function __construct(
        protected AccessAnalysisService $accessAnalysisService
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
        $result = $this->accessAnalysisService->getDataTableAccessAnalysis($storeId, $filters);
        $accessResults = collect();
        if (! is_null($result->get('data'))) {
            $accessResults = Arr::get($result->get('data')->first(), 'table_report_search', []);
        }

        return function () use ($header, $accessResults) {
            $file = fopen('php://output', 'w');
            fputcsv($file, convert_fields_to_sjis(array_values($header)));
            foreach ($accessResults as $accessItem) {
                $row = [];
                foreach (static::HEADING as $field => $heading) {
                    $row[] = Arr::get($accessItem, $field);
                }
                fputcsv($file, convert_fields_to_sjis($row));
            }
            fclose($file);
        };
    }
}
