<?php

namespace App\Support;

use App\Repositories\Contracts\ShopSettingAwardPointRepository;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShopSettingAwardPointCsv
{
    public const HEADING = [
        'purchase_date' => ['title' => '購入日付', 'validation' => ['nullable', 'date_format:Y/m/d']],
        'order_number' => ['title' => '注文番号', 'validation' => ['nullable', 'max:255']],
        'points_awarded' => ['title' => 'ポイント付与数', 'validation' => ['nullable', 'integer', 'between:-2000000000,2000000000']],
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
                '2023/06/01', '252628-20230601-0004926638', 19,
            ]));
            fclose($file);
        };
    }

    public function importAwardPointSettingCSV(string $storeId, UploadedFile $file): array
    {
        $header = [];
        $count = 0;
        $results = [];
        $errors = [];
        $titles = $this->getFields('title');
        $validateRules = $this->getFields('validation');
        $stream = fopen($file->getPathname(), 'r');

        /** @var ShopSettingAwardPointRepository $shopSettingAwardPointRepo */
        $shopSettingAwardPointRepo = resolve(ShopSettingAwardPointRepository::class);
        DB::beginTransaction();
        try {
            $shopSettingAwardPointRepo->deleteAllByStoreId($storeId);
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
                        if ($result = $shopSettingAwardPointRepo->create($data + ['store_id' => $storeId])?->refresh()) {
                            $results[] = $result;
                        }
                    }
                }

                $count++;
            }

            fclose($stream);

            if (! $shopSettingAwardPointRepo->checkExistAnyRecord()) {
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
