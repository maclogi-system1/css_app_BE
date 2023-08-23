<?php

namespace App\Repositories\Eloquents;

use App\Constants\DateTimeConstant;
use App\Constants\MacroConstant;
use App\Models\MacroConfiguration;
use App\Repositories\Contracts\MacroConfigurationRepository as MacroConfigurationRepositoryContract;
use App\Repositories\Repository;
use App\WebServices\MacroService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class MacroConfigurationRepository extends Repository implements MacroConfigurationRepositoryContract
{
    public function __construct(
        protected MacroService $macroService
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
        $columns = $this->getAllCollumnOfTable($tableName);

        foreach ($relativeTables as $relativeTable) {
            $columns = $columns->merge($this->getAllCollumnOfTable($relativeTable['table_name']));
        }

        return $columns;
    }

    /**
     * Get all columns of a table.
     */
    private function getAllCollumnOfTable($tableName): Collection
    {
        if (MacroConstant::DESCRIPTION_TABLES[$tableName][MacroConstant::TABLE_TYPE] == MacroConstant::TYPE_EXTERNAL) {
            return $this->getAllCollumnOfTableOSS($tableName);
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
    private function getAllCollumnOfTableOSS($tableName): Collection
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
}
