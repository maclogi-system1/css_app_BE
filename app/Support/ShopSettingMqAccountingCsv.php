<?php

namespace App\Support;

use App\Repositories\Contracts\ShopSettingMqAccountingRepository;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ShopSettingMqAccountingCsv
{
    public const HEADING = [
        'date' => ['title' => '日付', 'validation' => ['required', 'date_format:Y/m/d']],
        'estimated_management_agency_expenses' => ['title' => '予:運営代行費', 'validation' => ['nullable', 'integer', 'between:-2000000000,2000000000']],
        'estimated_cost_rate' => ['title' => '予:原価率', 'validation' => ['nullable', 'decimal:0,6', 'between:-999999,999999']],
        'estimated_shipping_fee' => ['title' => '予:送料', 'validation' => ['nullable', 'integer', 'between:-2000000000,2000000000']],
        'estimated_commission_rate' => ['title' => '予:手数料率', 'validation' => ['nullable', 'decimal:0,6', 'between:-999999,999999']],
        'estimated_csv_usage_fee' => ['title' => '予:CSV利用料', 'validation' => ['nullable', 'integer', 'between:-2000000000,2000000000']],
        'estimated_store_opening_fee' => ['title' => '予:出店料', 'validation' => ['nullable', 'integer', 'between:-2000000000,2000000000']],
        'actual_management_agency_expenses' => ['title' => '実:運営代行費', 'validation' => ['nullable']],
        'actual_cost_rate' => ['title' => '実:原価率', 'validation' => ['nullable', 'decimal:0,6', 'between:-999999,999999']],
        'actual_shipping_fee' => ['title' => '実:送料', 'validation' => ['nullable', 'integer', 'between:-2000000000,2000000000']],
        'actual_commission_rate' => ['title' => '実:手数料率', 'validation' => ['nullable', 'decimal:0,6', 'between:-999999,999999']],
        'actual_csv_usage_fee' => ['title' => '実:CSV利用料', 'validation' => ['nullable', 'integer', 'between:-2000000000,2000000000']],
        'actual_store_opening_fee' => ['title' => '実:出店料', 'validation' => ['nullable', 'integer', 'between:-2000000000,2000000000']],
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
                '2023/08/08',
                '500000',
                '10.00%',
                '550',
                '5.00%',
                '50000',
                '50000',
                '500000',
                '10.00%',
                '550',
                '5.00%',
                '50000',
                '50000',
            ]));
            fclose($file);
        };
    }

    /**
     * @param string $storeId
     * @param UploadedFile $file
     * @return array
     */
    public function importMqAccountingSettingCSV(string $storeId, UploadedFile $file): array
    {
        $header = [];
        $count = 0;
        $results = [];
        $errors = [];
        $titles = $this->getFields('title');
        $validateRules = $this->getFields('validation');
        $stream = fopen($file->getPathname(), 'r');

        /** @var ShopSettingMqAccountingRepository $shopSettingMqAccountingRepo */
        $shopSettingMqAccountingRepo = resolve(ShopSettingMqAccountingRepository::class);
        DB::beginTransaction();
        try {
            $shopSettingMqAccountingRepo->deleteAllByStoreId($storeId);
            while (($row = fgetcsv($stream)) !== false) {
                $row = convert_sjis_to_utf8($row);

                if ($count == 0) {
                    $header = $row;
                } else {
                    $data = [];
                    $temp = array_combine($header, $row);
                    foreach ($titles as $field => $title) {
                        $value = null;

                        if (isset($temp[$title])) {
                            $value = trim($temp[$title]);
                            if (Str::endsWith($field, '_rate')) {
                                $value = preg_replace('/[\s\%]+/', '', $value);
                            }
                        }

                        $data[$field] = $value;
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
                        if ($result = $shopSettingMqAccountingRepo->create($data + ['store_id' => $storeId])?->refresh()) {
                            $results[] = $result;
                        }
                    }
                }

                $count++;
            }

            fclose($stream);
            if (! $shopSettingMqAccountingRepo->checkExistAnyRecord()) {
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
