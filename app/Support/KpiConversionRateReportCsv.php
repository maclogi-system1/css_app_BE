<?php

namespace App\Support;

use App\WebServices\AI\StoreChartService;
use Closure;
use Illuminate\Support\Arr;

class KpiConversionRateReportCsv
{
    public const HEADING = [
        'date' => ['title' => '日付'],
        'total' => ['title' => '転換率(すべて)'],
        'pc' => ['title' => '転換率(PC)'],
        'app' => ['title' => '転換率(アプリ)'],
        'phone' => ['title' => '転換率(スマートフォン)'],
    ];

    public function __construct(
        protected StoreChartService $storeChartService
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
    public function streamCsvFile(string $storeId, array $filters = []): Closure
    {
        $header = $this->getFields('title');
        // Check if the input matches the 'yyyy-MM' format
        $isMonthQuery = false;
        if (Arr::has($filters, ['from_date', 'to_date'])) {
            if (
                preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'from_date'))
                && preg_match('/^\d{4}-\d{2}$/', Arr::get($filters, 'to_date'))
            ) {
                $isMonthQuery = true;
            }
        }
        $result = $this->storeChartService->getDataTableConversionRateAnalysis($storeId, $filters, $isMonthQuery)->get('data');
        $conversionResults = Arr::get($result, 'table_conversion_rate', []);

        return function () use ($header, $conversionResults) {
            $file = fopen('php://output', 'w');
            fputcsv($file, convert_fields_to_sjis(array_values($header)));
            foreach ($conversionResults as $conversionItem) {
                $row = [];
                foreach (static::HEADING as $field => $heading) {
                    $row[] = Arr::get($conversionItem, $field);
                }
                fputcsv($file, convert_fields_to_sjis($row));
            }
            fclose($file);
        };
    }
}
