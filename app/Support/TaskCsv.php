<?php

namespace App\Support;

use Closure;

class TaskCsv
{
    public const HEADING = [
        'title' => ['title' => 'タイトル'],
        'job_group_code' => ['title' => 'ジョブグループコード'],
        'issue_type' => ['title' => '種別'],
        'category' => ['title' => 'カテゴリ'],
        'status' => ['title' => 'ステータス'],
        'assignees' => ['title' => '担当者'],
        'start_date' => ['title' => '開始日'],
        'start_time' => ['title' => '開始時間'],
        'due_date' => ['title' => '期限日'],
        'due_time' => ['title' => '期限時間'],
        'description' => ['title' => '説明'],
        'priority' => ['title' => '優先度'],

    ];

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
    public function streamCsvFile(): Closure
    {
        return function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, convert_fields_to_sjis(array_values($this->getFields('title'))));
            fputcsv($file, convert_fields_to_sjis([
                'タイムセール',
                'jg-12345',
                '1',
                '1',
                '1',
                '1,2',
                '2023/05/14',
                '00:00',
                '2023/05/16',
                '23:59',
                '期間限定ポイント5倍 2023/5/14 00:00～2023/5/14 23:59',
                'medium',
            ]));
            fclose($file);
        };
    }
}
