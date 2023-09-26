<?php

namespace App\Support;

use App\WebServices\AI\ProductAnalysisService;
use Closure;
use Illuminate\Support\Arr;

class KpiProductsAnalysisCsv
{
    public const HEADING = [
        'rank' => ['title' => '順位'],
        'management_number' => ['title' => '商品管理番号'],
        'item_name' => ['title' => '商品名'],
        'sales_all' => ['title' => '売上'],
        'visit_all' => ['title' => 'アクセス人数'],
        'conversion_rate' => ['title' => '転換率'],
        'sales_amnt_per_user' => ['title' => '客単価'],
    ];

    public function __construct(
        protected ProductAnalysisService $productAnalysisService
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
    public function streamCsvFile($storeId, array $filters = []): Closure
    {
        $header = $this->getFields('title');
        $result = $this->productAnalysisService->getProductSummary($storeId, $filters)->get('data');
        $productResults = collect();
        if (! is_null($result)) {
            $productResults = Arr::get($result, 'products');
        }

        return function () use ($header, $productResults) {
            $file = fopen('php://output', 'w');
            fputcsv($file, convert_fields_to_sjis(array_values($header)));
            foreach ($productResults as $productItem) {
                $row = [];
                foreach (static::HEADING as $field => $heading) {
                    $row[] = Arr::get($productItem, $field);
                }
                fputcsv($file, convert_fields_to_sjis($row));
            }
            fclose($file);
        };
    }
}
