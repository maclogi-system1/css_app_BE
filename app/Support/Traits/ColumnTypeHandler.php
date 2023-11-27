<?php

namespace App\Support\Traits;

use Illuminate\Support\Facades\Schema;

trait ColumnTypeHandler
{
    /**
     * Convert the type of the column in the database to the typescript.
     */
    public function convertColumnType($type): string
    {
        if (in_array($type, ['integer', 'bigint', 'smallint', 'decimal', 'boolean'])) {
            return 'number';
        } elseif (in_array($type, ['datetime', 'date'])) {
            return 'date';
        }

        return 'string';
    }

    /**
     * Get column data type from input string 'table.column'.
     * Return empty when exception table not found has been thrown.
     */
    public function getColumnDataType(string $tableColumnStr, bool $original = false): string
    {
        try {
            $columnType = Schema::getColumnType(explode('.', $tableColumnStr)[0], explode('.', $tableColumnStr)[1]);

            return $original ? $columnType : $this->convertColumnType($columnType);
        } catch(\Exception $e) {
            return '';
        }
    }
}
