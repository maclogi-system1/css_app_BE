<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;

class MqAccountingImport implements ToArray
{
    use Importable;

    public function array(array $rows)
    {
        $result = [];

        foreach ($rows as $index => $row) {
            foreach ($row as $column) {
                $result[$index][] = str($column)->beforeLast(' (')->toString();
            }
        }

        return $result;
    }
}
