<?php

namespace App\Support;

use App\Repositories\Contracts\ShopSettingSearchRankingRepository;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShopSettingSearchRankingCsv
{
    public const HEADING = [
        'store_competitive_id' => ['title' => '店舗ID', 'validation' => ['nullable', 'max:255']],
        'merchandise_control_number' => ['title' => '商品管理番号', 'validation' => ['nullable', 'max:255']],
        'keyword_1' => ['title' => 'キーワード1', 'validation' => ['nullable', 'max:255']],
        'keyword_2' => ['title' => 'キーワード2', 'validation' => ['nullable', 'max:255']],
        'keyword_3' => ['title' => 'キーワード3', 'validation' => ['nullable', 'max:255']],
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
    public function streamCsvFile(bool $isCompetitiveRanking): Closure
    {
        $header = $this->getFields('title');
        $sampleData = ['hurikake-3set', '40000001', 'ふりかけ昆布', 'ご飯のお供', 'ふりかけ'];

        if (! $isCompetitiveRanking) {
            unset($header['store_competitive_id'], $sampleData[0]);
        }

        return function () use ($header, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, convert_fields_to_sjis(array_values($header)));
            fputcsv($file, convert_fields_to_sjis($sampleData));
            fclose($file);
        };
    }

    public function importSearchRankingSettingCSV(string $storeId, bool $isCompetitiveRanking, UploadedFile $file): array
    {
        $header = [];
        $count = 0;
        $results = [];
        $errors = [];
        $titles = $this->getFields('title');
        $validateRules = $this->getFields('validation');
        $stream = fopen($file->getPathname(), 'r');

        if (! $isCompetitiveRanking) {
            unset($titles['store_competitive_id'], $validateRules['store_competitive_id']);
        }

        /** @var ShopSettingSearchRankingRepository $shopSettingSearchRankingRepo */
        $shopSettingSearchRankingRepo = resolve(ShopSettingSearchRankingRepository::class);
        DB::beginTransaction();
        try {
            $shopSettingSearchRankingRepo->deleteAllByStoreId($storeId, $isCompetitiveRanking);
            while (($row = fgetcsv($stream)) !== false) {
                $row = convert_sjis_to_utf8($row);

                if ($count == 0) {
                    $header = $row;
                } else {
                    $data = [];
                    $temp = array_combine($header, $row);
                    foreach ($titles as $field => $title) {
                        $data[$field] = isset($temp[$title])
                            ? trim($temp[$title])
                            : null;
                    }

                    $validator = Validator::make(
                        data: $data,
                        rules: $validateRules,
                        attributes: $titles
                    );

                    if ($validator->fails()) {
                        $errors[] = [
                            'index' => $count,
                            'row' => $count + 1,
                            'messages' => $validator->getMessageBag()->toArray(),
                        ];
                    } else {
                        if ($result = $shopSettingSearchRankingRepo->create($data + ['store_id' => $storeId])?->refresh()) {
                            $results[] = $result;
                        }
                    }
                }

                $count++;
            }

            fclose($stream);
            if (! $shopSettingSearchRankingRepo->checkExistAnyRecord()) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            fclose($stream);
            DB::rollBack();

            logger()->error("Process: {$e->getMessage()}");
            chatwork_log($e->getMessage(), 'error');
        }

        return [$results, $errors];
    }
}
