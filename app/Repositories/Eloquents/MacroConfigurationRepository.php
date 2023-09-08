<?php

namespace App\Repositories\Eloquents;

use App\Constants\DateTimeConstant;
use App\Constants\MacroConstant;
use App\Models\MacroConfiguration;
use App\Repositories\Contracts\MacroConfigurationRepository as MacroConfigurationRepositoryContract;
use App\Repositories\Contracts\MacroGraphRepository;
use App\Repositories\Contracts\MacroTemplateRepository;
use App\Repositories\Repository;
use App\WebServices\MacroService;
use App\WebServices\OSS\ShopService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MacroConfigurationRepository extends Repository implements MacroConfigurationRepositoryContract
{
    public function __construct(
        protected MacroService $macroService,
        protected ShopService $shopService,
        protected MacroGraphRepository $macroGraphRepository,
        protected MacroTemplateRepository $macroTemplateRepository,
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
     * Get the list of the macro configuration with pagination and handle filter.
     */
    public function getList(array $filters = [], array $columns = ['*'])
    {
        $perPage = Arr::get($filters, 'per_page', 10);

        $owner = Arr::get($filters, 'owner', '');
        if (! empty($owner)) {
            $filters['filters'] = ['created_by' => $owner];
        }

        $macroConfigurations = parent::getList($filters, $columns);

        $storeIds = collect($macroConfigurations->items())
            ->pluck('store_ids')
            ->join(',');
        $shopResponse = $this->shopService->getList([
            'per_page' => -1,
            'filters' => ['shop_url' => $storeIds],
        ]);

        if ($shopResponse->get('success')) {
            $shops = $shopResponse->get('data')->get('shops');
            $items = $perPage < 0 ? $macroConfigurations : $macroConfigurations->items();

            foreach ($items as $item) {
                $shopMatches = array_filter(
                    $shops,
                    function ($shop) use ($item) {
                        $storeIds = explode(',', $item->store_ids);

                        return in_array(Arr::get($shop, 'store_id'), $storeIds);
                    },
                );
                $item->stores = $shopMatches;
            }
        }

        $filterConditions = [];
        $keyword = Arr::get($filters, 'keyword', '');
        if (! empty($keyword)) {
            $filterConditions += ['keyword' => $keyword];
        }

        $shopName = Arr::get($filters, 'shop_name', '');
        if (! empty($shopName)) {
            $filterConditions += ['shop_name' => $shopName];
        }

        if (count($filterConditions) > 0) {
            $macroConfigurations = $this->filterMacroConfig($macroConfigurations, $shopResponse, $filterConditions);
        }

        return $macroConfigurations;
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
                Arr::get($relativeTable, MacroConstant::RELATIVE_TABLES)
            );
            if ($tableName === 'mq_accounting') {
                $tables[$tableName]->push([
                    'table' => 'mq_accounting',
                    'column' => 'year_month',
                    'type' => 'string'
                ]);
            }
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
        $macroConfiguration = $this->queryBuilder()->with(['graph', 'templates'])->where('id', $id)->first($columns);

        $shopResponse = $this->shopService->getList([
            'per_page' => -1,
            'filters' => ['shop_url' => $macroConfiguration->store_ids],
        ]);

        if ($shopResponse->get('success')) {
            $shops = $shopResponse->get('data')->get('shops');
            $macroConfiguration->stores = $shops;
        }

        return $macroConfiguration;
    }

    /**
     * Handle create a new macro configuration.
     */
    public function create(array $data): ?MacroConfiguration
    {
        return $this->handleSafely(function () use ($data) {
            $storeIds = preg_replace('/ *\, */', ',', Arr::get($data, 'store_ids'));
            $data['store_ids'] = $storeIds;
            $data['conditions'] = json_encode($data['conditions']);
            $data['time_conditions'] = json_encode($data['time_conditions']);
            $data['status'] = MacroConstant::MACRO_STATUS_NOT_READY;
            $macroConfiguration = $this->model()->fill($data);
            $macroConfiguration->save();

            if (
                Arr::get($data, 'macro_type') == MacroConstant::MACRO_TYPE_GRAPH_DISPLAY
                && Arr::has($data, 'graph')
            ) {
                $graphData = Arr::get($data, 'graph');
                if (
                    ! empty(Arr::get($graphData, 'axis_x'))
                    && ! empty(Arr::get($graphData, 'axis_y'))
                    && ! empty(Arr::get($graphData, 'graph_type'))
                    && ! empty(Arr::get($graphData, 'position_display'))
                ) {
                    $this->saveMacroGraph($macroConfiguration->id, $graphData);
                }
            } elseif (
                Arr::get($data, 'macro_type') == MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION
                && Arr::has($data, 'simulation')
            ) {
                $this->macroTemplateRepository->create($macroConfiguration->id, [
                    'type' => MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION,
                    'payload' => Arr::get($data, 'simulation'),
                ]);
            } elseif (
                Arr::get($data, 'macro_type') == MacroConstant::MACRO_TYPE_POLICY_REGISTRATION
                && Arr::has($data, 'policies')
            ) {
                $this->createPolicyTemplates($macroConfiguration->id, Arr::get($data, 'policies', []));
            } elseif (
                Arr::get($data, 'macro_type') == MacroConstant::MACRO_TYPE_TASK_ISSUE
                && Arr::has($data, 'tasks')
            ) {
                $this->createTaskTemplates($macroConfiguration->id, Arr::get($data, 'tasks', []));
            }

            return $macroConfiguration->refresh();
        }, 'Create macroConfiguration');
    }

    /**
     * Handle update the specified team.
     */
    public function update(array $data, MacroConfiguration $macroConfiguration): ?MacroConfiguration
    {
        return $this->handleSafely(function () use ($data, $macroConfiguration) {
            $storeIds = preg_replace('/ *\, */', ',', Arr::get($data, 'store_ids'));
            $data['store_ids'] = $storeIds;
            $data['conditions'] = json_encode($data['conditions']);
            $data['time_conditions'] = json_encode($data['time_conditions']);
            unset($macroConfiguration->stores);
            $macroConfiguration->fill(Arr::except($data, 'macro_type'));
            $macroConfiguration->save();

            // Save graph configuration
            $hasGraphConfig = false;
            if (
                $macroConfiguration->macro_type == MacroConstant::MACRO_TYPE_GRAPH_DISPLAY
                && Arr::has($data, 'graph')
            ) {
                $graphData = Arr::get($data, 'graph');
                if (
                    ! empty(Arr::get($graphData, 'axis_x'))
                    && ! empty(Arr::get($graphData, 'axis_y'))
                    && ! empty(Arr::get($graphData, 'graph_type'))
                    && ! empty(Arr::get($graphData, 'position_display'))
                ) {
                    $hasGraphConfig = true;
                    $this->saveMacroGraph($macroConfiguration->id, $graphData);
                }
            } elseif (
                $macroConfiguration->macro_type == MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION
                && Arr::has($data, 'simulation')
            ) {
                $this->macroTemplateRepository->updateOrCreate([
                    'macro_configuration_id' => $macroConfiguration->id,
                ], [
                    'type' => MacroConstant::MACRO_TYPE_AI_POLICY_RECOMMENDATION,
                    'payload' => Arr::get($data, 'simulation'),
                ]);
            } elseif (
                $macroConfiguration->macro_type == MacroConstant::MACRO_TYPE_POLICY_REGISTRATION
                && Arr::has($data, 'policies')
            ) {
                $this->macroTemplateRepository->deleteByMacroConfigId($macroConfiguration->id);
                $this->createPolicyTemplates($macroConfiguration->id, Arr::get($data, 'policies', []));
            } elseif (
                $macroConfiguration->macro_type == MacroConstant::MACRO_TYPE_TASK_ISSUE
                && Arr::has($data, 'tasks')
            ) {
                $this->macroTemplateRepository->deleteByMacroConfigId($macroConfiguration->id);
                $this->createTaskTemplates($macroConfiguration->id, Arr::get($data, 'tasks', []));
            }

            if (
                $hasGraphConfig == false
                && ! is_null($macroConfiguration->graph)
            ) {
                $this->macroGraphRepository->delete($macroConfiguration->graph);
            }

            return $macroConfiguration->refresh();
        }, 'Update macroConfiguration');
    }

    /**
     * Create a new list of policy templates for macro configuration.
     */
    public function createPolicyTemplates(int $macroConfigurationId, array $policies): void
    {
        foreach ($policies as $policy) {
            $this->macroTemplateRepository->create($macroConfigurationId, [
                'type' => MacroConstant::MACRO_TYPE_POLICY_REGISTRATION,
                'payload' => $policy,
            ]);
        }
    }

    /**
     * Create a new list of task templates for macro configuration.
     */
    public function createTaskTemplates(int $macroConfigurationId, array $tasks): void
    {
        foreach ($tasks as $task) {
            $this->macroTemplateRepository->create($macroConfigurationId, [
                'type' => MacroConstant::MACRO_TYPE_TASK_ISSUE,
                'payload' => $task,
            ]);
        }
    }

    /**
     * Handle delete the specified macroConfiguration.
     */
    public function delete(MacroConfiguration $macroConfiguration): ?MacroConfiguration
    {
        $macroConfiguration->deleted_by = auth()->id();
        unset($macroConfiguration->stores);
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
        $graphTypes = collect(MacroConstant::MACRO_GRAPH_TYPES)
            ->map(fn ($label, $value) => compact('value', 'label'))
            ->values();
        $positionDisplay = collect(MacroConstant::MACRO_POSITION_DISPLAY)
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
            'graph_types' => $graphTypes,
            'position_display' => $positionDisplay,
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
        $storeIds = explode(',', $macroConfiguration->store_ids);

        return $this->buildQueryAndExecute($conditions, $storeIds);
    }

    /**
     * Handles joining relational tables.
     */
    private function handleJoinRelation(Builder $query, string $tableName, array $relativeTable): void
    {
        if (
            $relativeTable[MacroConstant::RELATIVE_TABLE_FOREIGN_KEY_TYPE] == MacroConstant::RELATIVE_TABLE_TYPE_OUTBOUND
        ) {
            $query->join(
                $relativeTable[MacroConstant::TABLE_NAME],
                $relativeTable[MacroConstant::TABLE_NAME].'.id',
                '=',
                $tableName.'.'.$relativeTable[MacroConstant::RELATIVE_TABLE_FOREIGN_KEY]
            );
        } else {
            $query->leftJoin(
                $relativeTable[MacroConstant::TABLE_NAME],
                $relativeTable[MacroConstant::TABLE_NAME].'.'.$relativeTable[MacroConstant::RELATIVE_TABLE_FOREIGN_KEY],
                '=',
                $tableName.'.id'
            );
        }
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
        $field = Arr::get($condition, 'field');
        $value = Arr::get($condition, 'value');
        $value = match ($operator) {
            'in' => explode(',', $value),
            'like' => '%'.$value.'%',
            'not_like' => '%'.$value.'%',
            default => $value
        };

        if ($operator == 'in') {
            return [
                $field,
                $value,
            ];
        }

        return [
            $field,
            str_replace('_', ' ', $operator),
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
            $direction = $dateCondition['direction'] == 'forward' ? '<=' : '>=';

            $newValue = match ($dateCondition['time_range']) {
                'day' => now(),
                'week' => $direction == '<=' ? now()->startOfWeek() : now()->endOfWeek(),
                'month' => $direction == '<=' ? now()->startOfMonth() : now()->endOfMonth(),
                'year' => $direction == '<=' ? now()->startOfYear() : now()->endOfYear(),
            };

            return [
                Arr::get($condition, 'field'),
                $direction,
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
        $listMacroName = $this->queryBuilder()
            ->select('name')
            ->when(! empty($keyword), function ($query) use ($keyword) {
                $query->where('name', 'like', '%'.$keyword.'%');
            })
            ->get()
            ->pluck('name');

        $listShop = $this->shopService->getList([])->get('data')->get('shops');
        $listShopName = collect($listShop)->filter(function ($item) use ($keyword) {
            return false !== stristr($item['name'], $keyword);
        })->pluck('name');
        $listStoreId = collect($listShop)->filter(function ($item) use ($keyword) {
            return false !== stristr($item['store_id'], $keyword);
        })->pluck('store_id');

        $result = new Collection();
        $result = $result->merge($listMacroName);
        $result = $result->merge($listShopName);
        $result = $result->merge($listStoreId);
        $result = $result->map(function ($item) {
            return ['label' => $item, 'value' => $item];
        });

        return $result->toArray();
    }

    /**
     * Save macro's graph configuration.
     *
     * @param int $macroConfigId
     * @param array $graphData
     * @return void
     */
    private function saveMacroGraph($macroConfigId, array $graphData)
    {
        $macroConfig = MacroConfiguration::with('graph')->findOrFail($macroConfigId);
        if (is_null($macroConfig->graph)) {
            $this->macroGraphRepository->create($macroConfigId, $graphData);
        } else {
            $this->macroGraphRepository->update($graphData, $macroConfig->graph);
        }
    }

    /**
     * Filter MacroConfigurations by conditions.
     *
     * @param  \Illuminate\Support\Collection  $macroConfigurations
     * @param  \Illuminate\Support\Collection  $shopResponse
     * @param  array  $filterConditions
     * @return \Illuminate\Support\Collection
     */
    private function filterMacroConfig($macroConfigurations, Collection $shopResponse, array $filterConditions)
    {
        $keyword = Arr::get($filterConditions, 'keyword', '');
        $shopName = Arr::get($filterConditions, 'shop_name', '');
        $result = $macroConfigurations;

        if (! empty($keyword)) {
            $filteredShopStoreIds = [];
            if ($shopResponse->get('success')) {
                $shops = $shopResponse->get('data')->get('shops');
                $filteredShopStoreIds = collect($shops)->filter(function ($item) use ($keyword) {
                    return false !== stristr($item['store_id'], $keyword)
                            || false !== stristr($item['name'], $keyword);
                })->pluck('store_id')->toArray();
            }

            $result = $macroConfigurations->filter(function ($item) use ($filteredShopStoreIds, $keyword) {
                if (count($filteredShopStoreIds) > 0) {
                    $storeIds = explode(',', $item->store_ids);

                    return ! empty(array_intersect($filteredShopStoreIds, $storeIds))
                            || false !== stristr($item->name, $keyword);
                }

                return false !== stristr($item->name, $keyword);
            });
        }

        if (! empty($shopName)) {
            $filteredShopStoreIds = [];
            if ($shopResponse->get('success')) {
                $shops = $shopResponse->get('data')->get('shops');
                $filteredShopStoreIds = collect($shops)->filter(function ($item) use ($shopName) {
                    return false !== stristr($item['name'], $shopName);
                })->pluck('store_id')->toArray();
            }

            $result = $macroConfigurations->filter(function ($item) use ($filteredShopStoreIds) {
                $storeIds = explode(',', $item->store_ids);

                return ! empty(array_intersect($filteredShopStoreIds, $storeIds));
            });
        }

        return $result;
    }

    /**
     * Get chart data to display macro graph on kpi screen.
     */
    public function getDataChartMacroGraph(string $storeId): Collection
    {
        $positionDisplay = collect(MacroConstant::MACRO_POSITION_DISPLAY)->keys();

        $macroGraphData = $this->model()
                        ->join('macro_graphs as mg', 'macro_configurations.id', '=', 'mg.macro_configuration_id')
                        ->where('macro_configurations.store_ids', 'LIKE', '%'.$storeId.'%')
                        ->where('macro_configurations.macro_type', MacroConstant::MACRO_TYPE_GRAPH_DISPLAY)
                        ->orderBy('macro_configurations.updated_at', 'desc')
                        ->get();

        $result = [];
        foreach ($positionDisplay as $positionItem) {
            $macroGraphItem = $macroGraphData->filter(function ($item) use ($positionItem) {
                return $item->position_display == $positionItem;
            })->first();

            if (! is_null($macroGraphItem)) {
                $axisX = $macroGraphItem->axis_x;
                $axisY = $macroGraphItem->axis_y;
                $axisXCol = explode('.', $axisX)[1];
                $axisYCol = explode('.', $axisY)[1];

                $macroConfiguration = $this->queryBuilder()->where('id', $macroGraphItem->macro_configuration_id)->first();
                $dataResult = $this->getQueryResults($macroConfiguration);
                $graphData = [];
                foreach ($dataResult as $item) {
                    $itemAttributes = get_object_vars($item);
                    $dataX = array_filter(
                        $itemAttributes,
                        fn ($key) => $key === $axisXCol,
                        ARRAY_FILTER_USE_KEY
                    );
                    $dataY = array_filter(
                        $itemAttributes,
                        fn ($key) => $key === $axisYCol,
                        ARRAY_FILTER_USE_KEY
                    );
                    $graphData[] = [
                        'axis_x' =>  Arr::get($dataX, $axisXCol, ''),
                        'axis_y' => Arr::get($dataY, $axisYCol, ''),
                    ];
                }

                $result[$positionItem] = [
                    'title' => $macroGraphItem->title,
                    'graph_type' => $macroGraphItem->graph_type,
                    'axis_x' => [
                        'field' => $axisX,
                        'type' => $this->getChartDataType($axisX),
                    ],
                    'axis_y' => [
                        'field' => $axisY,
                        'type' => $this->getChartDataType($axisY),
                    ],
                    'data' => $graphData,
                ];
            } else {
                $result[$positionItem] = [];
            }
        }

        return collect($result);
    }

    /**
     * Get column data type from input string 'table.column'.
     * Return empty when exception table not found has been thrown.
     */
    private function getChartDataType(string $tableColumnStr): string
    {
        try {
            $columnType = Schema::getColumnType(explode('.', $tableColumnStr)[0], explode('.', $tableColumnStr)[1]);

            return $this->convertColumnType($columnType);
        } catch(\Exception $e) {
            return '';
        }
    }

    /**
     * Build query from conditions of a specified json conditions.
     */
    public function getQueryConditionsResults(array $requestConditions)
    {
        $conditions = Arr::get($requestConditions, 'conditions');
        $storeIdsStr = Arr::get($requestConditions, 'store_ids', '');
        $storeIds = explode(',', $storeIdsStr);

        return $this->buildQueryAndExecute($conditions, $storeIds);
    }

    /**
     * Build and execute macro conditions query.
     */
    private function buildQueryAndExecute(array $conditions, array $storeIds): ?Collection
    {
        $table = Arr::get($conditions, 'table');
        $boolean = Arr::get($conditions, 'operator', 'and');
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

            $query = DB::table($table)
                ->select('store_id', ...$columns)
                ->whereIn('store_id', $storeIds);
            if ($table == 'mq_accounting') {
                $query->addSelect(DB::raw("CONCAT(`{$table}`.`year`, '-', LPAD(`{$table}`.`month`, 2, '0'), '-01') as `year_month`"));
            }

            foreach ($relativeTables as $tableName => $relativeTable) {
                $columnsRelativeTable = $this->getAllColumnOfTable($tableName)
                        ->map(fn ($item) => "{$item['table']}.{$item['column']}")
                        ->toArray();

                $this->handleJoinRelation($query, $table, $relativeTable);

                $query->addSelect($columnsRelativeTable);
            }

            foreach ($conditionItems as $conditionItem) {
                $params = $this->handleWhereParams($conditionItem);

                if (empty($params)) {
                    continue;
                }

                $method = $conditionItem['operator'] == 'in' ? 'whereIn' : 'where';
                $params[] = $boolean; // and|or

                $query->{$method}(...$params);
            }

            return $query->get();
        }

        return collect();
    }
}
