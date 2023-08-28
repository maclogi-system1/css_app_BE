<?php

namespace App\Repositories\Eloquents;

use App\Constants\DateTimeConstant;
use App\Constants\MacroConstant;
use App\Models\MacroConfiguration;
use App\Repositories\Contracts\MacroConfigurationRepository as MacroConfigurationRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\MacroService;
use App\WebServices\OSS\ShopService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MacroConfigurationRepository extends Repository implements MacroConfigurationRepositoryContract
{
    public function __construct(
        protected MacroService $macroService,
        protected ShopService $shopService
    ) {
    }

    /**
     * Get full name of model.
     */
    public function getModelName(): string
    {
        return MacroConfiguration::class;
    }

    /**
     * Get list table.
     */
    public function getListTable(): array
    {
        $tables = [];

        foreach (MacroConstant::LIST_RELATIVE_TABLE as $tableName => $relativeTable) {
            $tables[$tableName] = $this->getAllColumnOfTableAndRelativeTable(
                $tableName,
                Arr::get($relativeTable, 'relative_tables')
            );
        }

        return $tables;
    }

    /**
     * Get all the columns of a table and of the tables that are related to it.
     */
    private function getAllColumnOfTableAndRelativeTable($tableName, $relativeTables): Collection
    {
        $columns = $this->getAllColumnOfTable($tableName);

        foreach ($relativeTables as $relativeTable) {
            $columns = $columns->merge($this->getAllColumnOfTable($relativeTable['table_name']));
        }

        return $columns;
    }

    /**
     * Get all columns of a table.
     */
    private function getAllColumnOfTable($tableName): Collection
    {
        if (MacroConstant::DESCRIPTION_TABLES[$tableName][MacroConstant::TABLE_TYPE] == MacroConstant::TYPE_EXTERNAL) {
            return $this->getAllColumnOfTableOSS($tableName);
        }

        $columns = collect(Schema::getColumnListing($tableName));
        $hiddenColumns = Arr::get(MacroConstant::DESCRIPTION_TABLES, $tableName.'.'.MacroConstant::REMOVE_COLUMNS);
        $convertColumnType = fn ($type) => $this->convertColumnType($type);

        return $columns->filter(function ($columnName) use ($hiddenColumns) {
            return ! in_array($columnName, $hiddenColumns);
        })->map(function ($columnName) use ($tableName, $convertColumnType) {
            return [
                'table' => $tableName,
                'column' => $columnName,
                'type' => $convertColumnType(Schema::getColumnType($tableName, $columnName)),
            ];
        })->values();
    }

    /**
     * Get all columns of a table (in OSS).
     */
    private function getAllColumnOfTableOSS($tableName): Collection
    {
        $result = $this->macroService->getColumnTableOSS($tableName);

        if (! $result->get('success')) {
            return collect();
        }

        $hiddenColumns = Arr::get(MacroConstant::DESCRIPTION_TABLES, $tableName.'.'.MacroConstant::REMOVE_COLUMNS);
        $convertColumnType = fn ($type) => $this->convertColumnType($type);

        return collect($result->get('data'))->filter(function ($type, $columnName) use ($hiddenColumns) {
            return ! in_array($columnName, $hiddenColumns);
        })->map(function ($type, $columnName) use ($tableName, $convertColumnType) {
            return [
                'table' => $tableName,
                'column' => $columnName,
                'type' => $convertColumnType($type),
            ];
        })->values();
    }

    /**
     * Find a specified macro configuration.
     */
    public function find($id, array $columns = ['*']): ?MacroConfiguration
    {
        return $this->queryBuilder()->where('id', $id)->first($columns);
    }

    /**
     * Handle create a new macro configuration.
     */
    public function create(array $data): ?MacroConfiguration
    {
        return $this->handleSafely(function () use ($data) {
            $data['conditions'] = json_encode($data['conditions']);
            $data['time_conditions'] = json_encode($data['time_conditions']);
            $macroConfiguration = $this->model()->fill($data);
            $macroConfiguration->save();

            return $macroConfiguration;
        }, 'Create macroConfiguration');
    }

    /**
     * Handle update the specified team.
     */
    public function update(array $data, MacroConfiguration $macroConfiguration): ?MacroConfiguration
    {
        return $this->handleSafely(function () use ($data, $macroConfiguration) {
            $data['conditions'] = json_encode($data['conditions']);
            $data['time_conditions'] = json_encode($data['time_conditions']);
            $macroConfiguration->fill($data);
            $macroConfiguration->save();

            return $macroConfiguration->refresh();
        }, 'Update macroConfiguration');
    }

    /**
     * Handle delete the specified macroConfiguration.
     */
    public function delete(MacroConfiguration $macroConfiguration): ?MacroConfiguration
    {
        $macroConfiguration->deleted_by = auth()->id();
        $macroConfiguration->save();
        $macroConfiguration->delete();

        return $macroConfiguration;
    }

    /**
     * Get a list of the option for select.
     */
    public function getOptions(): array
    {
        $macroTypes = collect(MacroConstant::MACRO_TYPES)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();
        $comparisonOperators = collect(array_combine(
            MacroConstant::MACRO_OPERATORS,
            MacroConstant::MACRO_OPERATOR_LABELS
        ));
        $typeOperators = [];

        foreach (MacroConstant::MACRO_OPERATORS_OF_TYPES as $type => $operators) {
            $typeOperators[$type] = $comparisonOperators->only($operators)
                ->map(fn ($label, $value) => compact('value', 'label'))
                ->values();
        }

        $dateConditions = collect([
            'specify_date_time' => '日時を指定',
            'from_today' => '今日から',
        ] + DateTimeConstant::TIMELINE)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();

        $daysOfWeek = collect([-1 => '全ての曜日'] + DateTimeConstant::DAYS_OF_WEEK)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();

        $timeRange = collect(DateTimeConstant::TIME_UNITS)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();
        $timeConditions = collect(MacroConstant::MACRO_TIME_CONDITIONS)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();

        return [
            'macro_types' => $macroTypes,
            'comparison_operators' => $typeOperators,
            'date_condition' => $dateConditions,
            'days_of_week' => $daysOfWeek,
            'time_range' => $timeRange,
            'direction' => [
                ['label' => '前', 'value' => 'forward'],
                ['label' => '後', 'value' => 'back'],
            ],
            'time_conditions' => $timeConditions,
        ];
    }

    /**
     * Convert the type of the column in the database to the typescript.
     */
    private function convertColumnType($type): string
    {
        if (in_array($type, ['integer', 'bigint', 'smallint', 'decimal', 'boolean'])) {
            return 'number';
        } elseif (in_array($type, ['datetime', 'date'])) {
            return 'date';
        }

        return 'string';
    }

    /**
     * Build query from conditions of a specified macro configuration.
     */
    public function getQueryResults(MacroConfiguration $macroConfiguration)
    {
        $conditions = $macroConfiguration->conditions_decode;
        $table = Arr::get($conditions, 'table');
        $operator = Arr::get($conditions, 'operator', 'and');
        $conditionItems = Arr::get($conditions, 'conditions', []);

        if (is_null($table)) {
            return null;
        }

        if (MacroConstant::DESCRIPTION_TABLES[$table][MacroConstant::TABLE_TYPE] == MacroConstant::TYPE_INTERNAL) {
            $relativeTables = Arr::get(
                MacroConstant::LIST_RELATIVE_TABLE,
                $table.'.'.MacroConstant::RELATIVE_TABLES
            );

            $columns = $this->getAllColumnOfTable($table)
                ->map(fn ($item) => "{$item['table']}.{$item['column']}")
                ->toArray();

            $query = DB::table($table)->select('store_id', ...$columns);

            foreach ($relativeTables as $tableName => $relativeTable) {
                if (
                    $relativeTable[MacroConstant::RELATIVE_TABLE_FOREIGN_KEY_TYPE]
                    == MacroConstant::RELATIVE_TABLE_TYPE_OUTBOUND
                ) {
                    $columnsRelativeTable = $this->getAllColumnOfTable($tableName)
                        ->map(fn ($item) => "{$item['table']}.{$item['column']}")
                        ->toArray();
                    $query->leftJoin(
                        $tableName,
                        $tableName.'.id',
                        '=',
                        $table.'.'.$relativeTable[MacroConstant::RELATIVE_TABLE_FOREIGN_KEY]
                    )->addSelect($columnsRelativeTable);
                }
            }

            foreach ($conditionItems as $conditionItem) {
                $params = $this->handleWhereParams($conditionItem);

                if (empty($params)) {
                    continue;
                }

                $method = $conditionItem['operator'] == 'in' ? 'whereIn' : 'where';

                if ($operator == 'or') {
                    $method = str($method)->title()->prepend('or')->toString();
                }

                $query->{$method}(...$params);
            }

            return $query->get();
        }

        return collect();
    }

    /**
     * Handle the parameters for the where clause.
     */
    private function handleWhereParams(array $condition): array
    {
        if (Arr::has($condition, 'date_condition')) {
            return $this->handleConditionContainingDate($condition);
        }

        $operator = Arr::get($condition, 'operator');
        $value = Arr::get($condition, 'value');
        $value = match ($operator) {
            'in' => explode(',', $value),
            'like' => '%'.$value.'%',
            default => $value
        };

        if ($operator == 'in') {
            return [
                Arr::get($condition, 'field'),
                $value,
            ];
        }

        return [
            Arr::get($condition, 'field'),
            Arr::get($condition, 'operator'),
            $value,
        ];
    }

    /**
     * Handle the parameters for the where clause provided that the data is a date.
     */
    private function handleConditionContainingDate(array $condition): array
    {
        $value = Arr::get($condition, 'value');
        $dateCondition = Arr::get($condition, 'date_condition');
        $now = now()->toImmutable();
        $newValue = $now;

        if (in_array($value, array_keys(DateTimeConstant::TIMELINE))) {
            $splitValue = explode('_', $value);
            $carbonMethod = match ($splitValue[0]) {
                'last', 'yesterday' => 'sub',
                'this', 'today' => '',
                'next', 'tomorrow' => 'add',
            };

            if (! empty($carbonMethod)) {
                $carbonMethod .= count($splitValue) > 1 ? str($splitValue[1])->title()->toString() : 'Day';
                $newValue = now()->{$carbonMethod}();
            }

            if (str($splitValue[1])->contains('week')) {
                $newValue = $newValue->weekday($dateCondition['day']);
            } elseif (str($splitValue[1])->contains('month')) {
                $newValue = $newValue->day($dateCondition['day']);
            }

            return [
                Arr::get($condition, 'field'),
                Arr::get($condition, 'operator'),
                $newValue,
            ];
        } elseif ($value == 'from_today') {
            $newValue = match ($dateCondition['time_range']) {
                'day' => now(),
                'week' => now()->nextWeekendDay(),
                'month' => now()->endOfMonth(),
                'year' => now()->endOfYear(),
            };

            return [
                Arr::get($condition, 'field'),
                $dateCondition['direction'] == 'forward' ? '<=' : '>=',
                $newValue,
            ];
        }
    }

    /**
     * Updates the ready state for the specified macro to execute it on schedule.
     */
    public function executeMacro(MacroConfiguration $macroConfiguration): bool
    {
        if (! in_array($macroConfiguration->macro_type, MacroConstant::MACRO_SCHEDULABLE_TYPES)) {
            return false;
        }

        $macroConfiguration->status = MacroConstant::MACRO_STATUS_READY;
        $macroConfiguration->save();

        return true;
    }

    /**
     * Search LIKE macro name, store name, store id by keyword.
     */
    public function getKeywords(string $keyword): array
    {
        // Query macro name from DB
        $listMacroName = $this->queryBuilder()
            ->select('name')
            ->when(! empty($keyword), function ($query) use ($keyword) {
                $query->where('name', 'like', '%'.$keyword.'%');
            })
            ->get()
            ->pluck('name');

        // Get store from OSS service
        $listShop = $this->shopService->getList([])->get('data')->get('shops');
        $listShopName = collect($listShop)->filter(function ($item) use ($keyword) {
            return false !== stristr($item['name'], $keyword);
        })->pluck('name');
        $listStoreId = collect($listShop)->filter(function ($item) use ($keyword) {
            return false !== stristr($item['store_id'], $keyword);
        })->pluck('store_id');

        // Merge result from DB with OSS service
        $result = new Collection();
        $result = $result->merge($listMacroName);
        $result = $result->merge($listShopName);
        $result = $result->merge($listStoreId);
        $result = $result->map(function ($item) {
            return ['label' => $item, 'value' => $item];
        });

        return $result->toArray();
    }
}
