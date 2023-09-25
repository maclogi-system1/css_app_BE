<?php

namespace App\Support;

use App\Repositories\Contracts\ShopSettingRankingRepository;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShopSettingRankingCsv
{
    public const HEADING = [
        'store_competitive_id' => ['title' => '店舗ID', 'validation' => ['nullable', 'max:255']],
        'merchandise_control_number' => ['title' => '商品管理番号', 'validation' => ['nullable', 'max:255']],
        'directory_id' => ['title' => 'ディレクトリID', 'validation' => ['nullable', 'integer', 'between:-2000000000,2000000000']],
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
        $sampleData = ['futtonda', 'hurikake-3set', '502936'];

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

    public function importRankingSettingCSV(string $storeId, bool $isCompetitiveRanking, UploadedFile $file): array
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

        /** @var ShopSettingRankingRepository $shopSettingRankingRepo */
        $shopSettingRankingRepo = resolve(ShopSettingRankingRepository::class);
        DB::beginTransaction();
        try {
            $shopSettingRankingRepo->deleteAllByStoreId($storeId, $isCompetitiveRanking);
            while (($row = fgetcsv($stream)) !== false) {
                if ($count == 0) {
                    $header = convert_sjis_to_utf8($row);
                } else {
                    $data = [];
                    $temp = array_combine($header, $row);
                    foreach ($titles as $field => $title) {
                        $data[$field] = isset($temp[$title])
                            ? preg_replace('/[\s\%]+/', '', $temp[$title])
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
                        $results[] = $shopSettingRankingRepo->create($data + ['store_id' => $storeId])?->refresh();
                    }
                }

                $count++;
            }

            fclose($stream);
            if (! $shopSettingRankingRepo->checkExistAnyRecord()) {
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
