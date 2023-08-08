<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PolicyCsv
{
    public const HEADING = [
        'コントロールカラム',
        'ステータス',
        '施策種別',
        'ジョブグループコード',
        'ジョブグループタイトル',
        '説明',
        '担当者',
        'テンプレート選択',
        'ジョブタイトル',
        '即時反映',
        '反映日',
        '反映時間',
        '戻し日',
        '戻し時間',
        '対象商品種別',
        '商品管理番号',
        'バナーテンプレート',
        'バナー制作指示書',
        'PC用キャッチコピー：文字列',
        'PC用キャッチコピー：エラー処理',
        'モバイル用 キャッチコピー：文字列',
        'モバイル用 キャッチコピー：エラー処理',
        '商品名：文字列',
        '商品名：エラー処理',
        'ポイント：ポイント変倍率',
        'ポイント：反映日',
        'ポイント：反映時間',
        'ポイント：戻し日',
        'ポイント：戻し時間',
        'ポイント：エラー処理',
        'ポイント：運用型ポイント変倍率',
        '直値引き：反映',
        '直値引き：割引率(％)',
        '直値引き：割引額(円)',
        '直値引き：戻し',
        '直値引き：エラー処理',
        '直値引き：表示価格',
        '直値引き：二重価格文言番号',
        '直値引き：送料',
        '直値引き：倉庫指定',
        '直値引き：販売期間指定 開始日',
        '直値引き：販売期間指定 開始時間',
        '直値引き：販売期間指定 終了日',
        '直値引き：販売期間指定 終了時間',
        '直値引き：サーチ非表示',
        '商品ページ修正：PC用商品説明文',
        '商品ページ修正：SP用商品説明文',
        '商品ページ修正：PC用販売説明文',
    ];

    /**
     * Return a callback handle stream csv file.
     */
    public function streamCsvFile(): Closure
    {
        return function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, convert_fields_to_sjis(static::HEADING));
            fclose($file);
        };
    }
}