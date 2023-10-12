<?php

namespace App\Support\Traits;

use Illuminate\Support\Arr;

trait ShopSettingUpdateRequest
{
    /**
     * @param array $fields
     * @param string $keyName title, validation
     * @param string $paramName
     * @return array
     */
    protected function getProperties(array $fields, string $keyName, string $table, string $paramName = 'settings'): array
    {
        $properties = [];
        foreach ($fields as $key => $field) {
            $keyField = "$paramName.*.$key";
            $value = Arr::get($field, $keyName);
            if (! empty($value)) {
                $properties[$keyField] = $value;
            }
        }

        if ($keyName == 'validation') {
            $properties[$paramName] = ['required'];
            $properties["$paramName.*.id"] = ['nullable', "exists:$table,id"];
        }

        return $properties;
    }
}
