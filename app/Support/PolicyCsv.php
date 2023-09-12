<?php

namespace App\Support;

use App\Repositories\Contracts\PolicyRepository;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PolicyCsv
{
    public const HEADING = [
        'single_job.job_group.status_id' => 'ステータス',
        'category' => '施策種別',
        'single_job.job_group.code' => 'ジョブグループコード',
        'single_job.job_group.title' => 'ジョブグループタイトル',
        'single_job.job_group.explanation' => '説明',
        'single_job.job_group.managers' => '担当者',
        'single_job.template_id' => 'テンプレート選択',
        'single_job.title' => 'ジョブタイトル',
        'immediate_reflection' => '即時反映',
        'single_job.execution_date' => '反映日',
        'single_job.execution_time' => '反映時間',
        'single_job.undo_date' => '戻し日',
        'single_job.undo_time' => '戻し時間',
        'type_item_url' => '対象商品種別',
        'single_job.item_urls' => '商品管理番号',
        'single_job.has_banner' => 'バナーテンプレート',
        'single_job.remark' => 'バナー制作指示書',
        'single_job.catch_copy_pc_text' => 'PC用キャッチコピー：文字列',
        'single_job.catch_copy_pc_text_error' => 'PC用キャッチコピー：エラー処理',
        'single_job.catch_copy_sp_text' => 'モバイル用 キャッチコピー：文字列',
        'single_job.catch_copy_sp_text_error' => 'モバイル用 キャッチコピー：エラー処理',
        'single_job.item_name_text' => '商品名：文字列',
        'single_job.item_name_text_error' => '商品名：エラー処理',
        'single_job.point_magnification' => 'ポイント：ポイント変倍率',
        'single_job.point_start_date' => 'ポイント：反映日',
        'single_job.point_start_time' => 'ポイント：反映時間',
        'single_job.point_end_date' => 'ポイント：戻し日',
        'single_job.point_end_time' => 'ポイント：戻し時間',
        'single_job.point_error' => 'ポイント：エラー処理',
        'single_job.point_operational' => 'ポイント：運用型ポイント変倍率',
        'single_job.discount_type' => '直値引き：反映',
        'single_job.discount_rate' => '直値引き：割引率(％)',
        'single_job.discount_price' => '直値引き：割引額(円)',
        'single_job.discount_undo_type' => '直値引き：戻し',
        'single_job.discount_error' => '直値引き：エラー処理',
        'single_job.discount_display_price' => '直値引き：表示価格',
        'single_job.double_price_text' => '直値引き：二重価格文言番号',
        'single_job.shipping_fee' => '直値引き：送料',
        'single_job.stock_specify' => '直値引き：倉庫指定',
        'single_job.time_sale_start_date' => '直値引き：販売期間指定 開始日',
        'single_job.time_sale_start_time' => '直値引き：販売期間指定 開始時間',
        'single_job.time_sale_end_date' => '直値引き：販売期間指定 終了日',
        'single_job.time_sale_end_time' => '直値引き：販売期間指定 終了時間',
        'single_job.is_unavailable_for_search' => '直値引き：サーチ非表示',
        'single_job.description_for_pc' => '商品ページ修正：PC用商品説明文',
        'single_job.description_for_smart_phone' => '商品ページ修正：SP用商品説明文',
        'single_job.description_by_sales_method' => '商品ページ修正：PC用販売説明文',
    ];

    public function __construct(
        protected PolicyRepository $policyRepository
    ) {
    }

    /**
     * Return a callback handle stream csv file.
     */
    public function streamCsvFile($storeId, array $filters = []): Closure
    {
        $policies = $this->policyRepository->getListByStore(
            $storeId,
            $filters + ['per_page' => -1],
        );

        return function () use ($policies) {
            $file = fopen('php://output', 'w');
            fputcsv($file, convert_fields_to_sjis(array_values(static::HEADING)));

            foreach ($policies->toArray() as $policy) {
                $row = [];
                foreach (static::HEADING as $field => $heading) {
                    if ($field == 'single_job.job_group.managers') {
                        $managers = Arr::pluck(Arr::get($policy, $field, []), 'id');
                        $row[] = implode(', ', $managers);
                        continue;
                    }

                    if ($field == 'single_job.execution_date') {
                        $executionTime = Arr::get($policy, 'single_job.execution_time');
                        $row[] = Carbon::create($executionTime)->format('Y-m-d');
                        continue;
                    }

                    if ($field == 'single_job.execution_time') {
                        $executionTime = Arr::get($policy, 'single_job.execution_time');
                        $row[] = Carbon::create($executionTime)->format('G:i');
                        continue;
                    }

                    if ($field == 'single_job.undo_date') {
                        $undoTime = Arr::get($policy, 'single_job.undo_time');
                        $row[] = Carbon::create($undoTime)->format('Y-m-d');
                        continue;
                    }

                    if ($field == 'single_job.undo_time') {
                        $undoTime = Arr::get($policy, 'single_job.undo_time');
                        $row[] = Carbon::create($undoTime)->format('G:i');
                        continue;
                    }

                    if ($field == 'single_job.point_start_date') {
                        $pointStart = Arr::get($policy, 'single_job.point_start');
                        $row[] = Carbon::create($pointStart)->format('Y-m-d');
                        continue;
                    }

                    if ($field == 'single_job.point_start_time') {
                        $pointStart = Arr::get($policy, 'single_job.point_start');
                        $row[] = Carbon::create($pointStart)->format('G');
                        continue;
                    }

                    if ($field == 'single_job.point_end_date') {
                        $pointEnd = Arr::get($policy, 'single_job.point_end');
                        $row[] = Carbon::create($pointEnd)->format('Y-m-d');
                        continue;
                    }

                    if ($field == 'single_job.point_end_time') {
                        $pointEnd = Arr::get($policy, 'single_job.point_end');
                        $row[] = Carbon::create($pointEnd)->format('G');
                        continue;
                    }

                    if ($field == 'single_job.time_sale_start_date') {
                        $saleStartDateTime = Arr::get($policy, 'single_job.time_sale_start_date_time');
                        $row[] = Carbon::create($saleStartDateTime)->format('Y-m-d');
                        continue;
                    }

                    if ($field == 'single_job.time_sale_start_time') {
                        $saleStartDateTime = Arr::get($policy, 'single_job.time_sale_start_date_time');
                        $row[] = Carbon::create($saleStartDateTime)->format('G:i');
                        continue;
                    }

                    if ($field == 'single_job.time_sale_end_date') {
                        $saleEndDateTime = Arr::get($policy, 'single_job.time_sale_end_date_time');
                        $row[] = Carbon::create($saleEndDateTime)->format('Y-m-d');
                        continue;
                    }

                    if ($field == 'single_job.time_sale_end_time') {
                        $saleEndDateTime = Arr::get($policy, 'single_job.time_sale_end_date_time');
                        $row[] = Carbon::create($saleEndDateTime)->format('G:i');
                        continue;
                    }

                    $row[] = Arr::get($policy, $field);
                }

                fputcsv($file, convert_fields_to_sjis($row));
            }
            fclose($file);
        };
    }
}
