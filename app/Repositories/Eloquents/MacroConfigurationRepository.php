<?php

namespace App\Repositories\Eloquents;

use App\Constants\DateTimeConstant;
use App\Constants\MacroConstant;
use App\Models\MacroConfiguration;
use App\Repositories\Contracts\MacroConfigurationRepository as MacroConfigurationRepositoryContract;
use App\Repositories\Contracts\MacroGraphRepository;
use App\Repositories\Contracts\MacroTemplateRepository;
use App\Repositories\Contracts\ShopRepository;
use App\Repositories\Contracts\TeamRepository;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Repository;
use App\Support\Traits\ColumnTypeHandler;
use App\WebServices\AI\MqAccountingService;
use App\WebServices\MacroService;
use App\WebServices\OSS\SchemaService;
use App\WebServices\OSS\ShopService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MacroConfigurationRepository extends Repository implements MacroConfigurationRepositoryContract
{
    use ColumnTypeHandler;

    public function __construct(
        protected MacroService $macroService,
        protected ShopService $shopService,
        protected MacroGraphRepository $macroGraphRepository,
        protected MacroTemplateRepository $macroTemplateRepository,
        protected SchemaService $schemaService,
        protected MqAccountingService $mqAccountingService,
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
        $filterConditions = [];
        $perPage = Arr::get($filters, 'per_page', 10);

        $owner = Arr::get($filters, 'owner', '');
        if (! empty($owner)) {
            $filters['filters'] = ['created_by' => $owner];
        }
        $shopFilters = [];
        $shopFilterKeys = [
            'projects.parent_id',
            'projects.is_contract',
            'projects.created_by',
        ];
        if (isset($filters['filters'])) {
            if (! is_null(Arr::get($filters['filters'], 'projects.parent_id'))) {
                $shopFilters['projects.parent_id'] = Arr::get($filters['filters'], 'projects.parent_id');
            }
            if (! is_null(Arr::get($filters['filters'], 'projects.is_contract'))) {
                $shopFilters['projects.is_contract'] = Arr::get($filters['filters'], 'projects.is_contract');
            }
            if (! is_null(Arr::get($filters['filters'], 'projects.created_by'))) {
                $shopFilters['projects.created_by'] = Arr::get($filters['filters'], 'projects.created_by');
            }

            $macroFilters = collect($filters['filters'])->except($shopFilterKeys)->toArray();
            $filters['filters'] = $macroFilters;
        }

        $macroConfigurations = parent::getList($filters, $columns);

        $storeIds = collect($macroConfigurations->items())
            ->pluck('store_ids')
            ->join(',');
        $storeIdCondition = Arr::get($filters, 'store_id', '');
        if (! empty($storeIdCondition)) {
            $storeIds .= ','.$storeIdCondition;
            $filterConditions += ['store_id' => $storeIdCondition];
        }

        $shopFilters['shop_url'] = $storeIds;
        $shopResponse = $this->shopService->getList([
            'per_page' => -1,
            'filters' => $shopFilters,
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
            if (MacroConstant::DESCRIPTION_TABLES[$tableName][MacroConstant::TABLE_TYPE] == MacroConstant::TYPE_EXTERNAL) {
                continue;
            }

            $tables[$tableName] = $this->getAllColumnOfTableAndRelativeTable(
                $tableName,
                Arr::get($relativeTable, MacroConstant::RELATIVE_TABLES)
            );
            if ($tableName === 'mq_accounting') {
                $tables[$tableName] = $this->buildAccountingTableColumns($tables[$tableName]);
                $tables[$tableName]->push([
                    'table' => 'mq_accounting',
                    'column' => 'year_month',
                    'type' => 'string',
                    'label' => trans('macro-labels.mq_accounting.year_month'),
                ]);
            }
        }

        $ossColumns = $this->macroService->getListTableOSS();
        if ($ossColumns->get('success')) {
            $tables = $ossColumns->get('data')->merge($tables)->toArray();
        }

        return $tables;
    }

    /**
     * @return array
     */
    public function getTableLabels(): array
    {
        $tableLabels = [];
        foreach (MacroConstant::LIST_RELATIVE_TABLE as $tableName => $relativeTable) {
            $tableLabels[$tableName] = trans("macro-labels.$tableName.table_label");
        }

        return $tableLabels;
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
                'label' => trans("macro-labels.$tableName.$columnName"),
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
        $macroConfiguration = $this->queryBuilder()
            ->with(['graph', 'templates', 'users', 'teams'])
            ->where('id', $id)
            ->first($columns);

        if (is_null($macroConfiguration)) {
            return null;
        }

        $shopResponse = $this->shopService->getList([
            'per_page' => -1,
            'filters' => ['shop_url' => $macroConfiguration?->store_ids],
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

            $usersAndTeams = explode(',', preg_replace('/ *\, */', ',', Arr::get($data, 'users_teams')));
            $users = array_map(
                fn ($user) => str_replace('user@', '', $user),
                array_filter($usersAndTeams, fn ($item) => str($item)->startsWith('user@'))
            );
            $teams = array_map(
                fn ($team) => str_replace('team@', '', $team),
                array_filter($usersAndTeams, fn ($item) => str($item)->startsWith('team@'))
            );
            $macroConfiguration->users()->sync($users);
            $macroConfiguration->teams()->sync($teams);

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

            $usersAndTeams = explode(',', preg_replace('/ *\, */', ',', Arr::get($data, 'users_teams')));
            $users = array_map(
                fn ($user) => str_replace('user@', '', $user),
                array_filter($usersAndTeams, fn ($item) => str($item)->startsWith('user@'))
            );
            $teams = array_map(
                fn ($team) => str_replace('team@', '', $team),
                array_filter($usersAndTeams, fn ($item) => str($item)->startsWith('team@'))
            );
            $macroConfiguration->users()->sync($users);
            $macroConfiguration->teams()->sync($teams);

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
    public function getOptions(?string $storeId = null): array
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
            'shops' => $this->getShopsForSelect(),
            'teams_users' => $this->getUserAndTeamForSelect($storeId),
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
            'alert_types' => [
                ['label' => '店舗アラート', 'value' => 9],
                ['label' => 'タスクアラート', 'value' => 10],
            ],
        ];
    }

    /**
     * Get a list of shops for selection.
     */
    private function getShopsForSelect()
    {
        $result = $this->shopService->getList([
            'per_page' => -1,
        ]);

        if ($result->get('success')) {
            $additions = [
                ['value' => '__all__', 'label' => '全店舗'],
                ['value' => '__shop_owner__', 'label' => '担当店舗'],
            ];

            return array_merge($additions, array_map(fn ($shop) => [
                'value' => $shop['store_id'],
                'label' => $shop['name'],
            ], $result->get('data')->get('shops')));
        }

        return [];
    }

    /**
     * Get the list of teams and users for the selection.
     */
    private function getUserAndTeamForSelect(?string $storeId = null)
    {
        /** @var \App\Repositories\Contracts\TeamRepository */
        $teamRepository = app(TeamRepository::class);
        $teams = $teamRepository->getList(['per_page' => -1])->map(fn ($team) => [
            'value' => 'team@'.$team->id,
            'label' => $team->name,
            'type' => 'team',
        ]);

        if ($storeId) {
            /** @var \App\Repositories\Contracts\ShopRepository */
            $shopRepository = app(ShopRepository::class);
            $users = array_map(fn ($user) => [
                'value' => 'user@'.$user['value'],
                'label' => $user['label'],
                'email' => $user['email'],
                'type' => 'user',
            ], Arr::get($shopRepository->getUsers(['store_id' => $storeId, 'per_page' => -1]), 'users', []));
        } else {
            /** @var \App\Repositories\Contracts\UserRepository */
            $userRepository = app(UserRepository::class);
            $users = $userRepository->getList(['per_page' => -1])->map(fn ($user) => [
                'value' => 'user@'.$user->id,
                'label' => $user->name,
                'email' => $user->email,
                'type' => 'user',
            ]);
        }

        return $teams->merge($users)->toArray();
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

        if (in_array($value, array_keys(DateTimeConstant::TIMELINE))) {
            $value = $this->getValueFromTimeLine($value);
        }

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
        $field = Arr::get($condition, 'field');
        $operator = Arr::get($condition, 'operator');
        $value = Arr::get($condition, 'value');
        $dateCondition = Arr::get($condition, 'date_condition');
        $now = now()->toImmutable();
        $newValue = $now;

        if (in_array($value, array_keys(DateTimeConstant::TIMELINE))) {
            $newValue = $this->getValueFromTimeLine($value, $dateCondition['day']);

            return [
                $field,
                $operator,
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
                $field,
                $direction,
                $newValue,
            ];
        } elseif ($value == 'specify_date_time') {
            $value = $dateCondition['value'];

            if ($this->getColumnDataType($field, true) == 'datetime' && $operator == '=') {
                $operator = 'between';
                $value = [
                    Carbon::create($value)->startOfDay()->format('Y-m-d H:i:s'),
                    Carbon::create($value)->endOfDay()->format('Y-m-d H:i:s'),
                ];

                return [
                    $field,
                    $value,
                    $operator,
                ];
            }
        }

        return [
            $field,
            $operator,
            $value,
        ];
    }

    private function getValueFromTimeLine($value, ?int $day = 1)
    {
        $newValue = now();
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
            $newValue = $newValue->weekday($day);
        } elseif (str($splitValue[1])->contains('month')) {
            $newValue = $newValue->day($day);
        }

        return $newValue;
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
        $storeId = Arr::get($filterConditions, 'store_id', '');
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

        if (! empty($storeId)) {
            $result = $macroConfigurations->filter(function ($item) use ($storeId) {
                $storeIds = explode(',', $item->store_ids);

                return in_array($storeId, $storeIds);
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
                $axisXCol = explode('.', $axisX)[1];

                $axisYArr = explode(',', $macroGraphItem->axis_y);
                $axisY = collect($axisYArr)->map(function ($item) {
                    return [
                        'field' => $item,
                        'type' => $this->getColumnDataType($item),
                        'label' => trans("macro-labels.$item"),
                    ];
                });

                foreach ($axisYArr as $item) {
                    $axisYItemTable = explode('.', $item)[0];
                    if ($axisYItemTable == 'mq_accounting') {
                        $axisY->add([
                            'field' => $item.MacroConstant::ACCOUNTING_ACTUAL_COLUMN,
                            'type' => $this->getColumnDataType($item),
                            'label' => trans("macro-labels.$item").trans('macro-labels'.MacroConstant::ACCOUNTING_ACTUAL_COLUMN),
                        ]);
                        $axisY->add([
                            'field' => $item.MacroConstant::ACCOUNTING_DIFF_COLUMN,
                            'type' => $this->getColumnDataType($item),
                            'label' => trans("macro-labels.$item").trans('macro-labels'.MacroConstant::ACCOUNTING_DIFF_COLUMN),
                        ]);
                    }
                }

                $macroConfiguration = $this->queryBuilder()->where('id', $macroGraphItem->macro_configuration_id)->first();
                $dataResult = $this->getQueryResults($macroConfiguration)->get('values');
                $graphData = [];
                foreach ($dataResult as $item) {
                    $dataX = $this->getNestedProperty($axisXCol, $item);
                    $dataY = [];
                    foreach ($axisY as $axisYItem) {
                        $parts = explode('.', Arr::get($axisYItem, 'field'));
                        $axisYItemCol = isset($parts[1]) ? trim($parts[1]) : '';
                        $axisYItemCol .= isset($parts[2]) ? '.'.trim($parts[2]) : '';
                        $dataYVal = $this->getNestedProperty($axisYItemCol, $item);
                        if (! is_null(Arr::get($dataYVal, $axisYItemCol))) {
                            $dataY[] = [
                                Arr::get($axisYItem, 'field') => Arr::get($dataYVal, $axisYItemCol),
                            ];
                        }
                    }
                    if (
                        ! is_null(Arr::get($dataX, $axisXCol, ''))
                        && ! empty($dataY)
                    ) {
                        $graphData[] = [
                            'axis_x' =>  Arr::get($dataX, $axisXCol, ''),
                            'axis_y' => $dataY,
                        ];
                    }
                }

                $result[$positionItem] = [
                    'title' => $macroGraphItem->title,
                    'graph_type' => $macroGraphItem->graph_type,
                    'axis_x' => [
                        'field' => $axisX,
                        'type' => $this->getColumnDataType($axisX),
                        'label' => trans("macro-labels.$axisX"),
                    ],
                    'axis_y' => $axisY,
                    'data' => $graphData,
                ];
            } else {
                $result[$positionItem] = [];
            }
        }

        return collect($result);
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
        $labelArr = [];

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
            foreach ($columns as $column) {
                $labelArr[explode('.', $column)[1]] = trans('macro-labels.'.$column);
            }
            $labelArr['store_id'] = trans('macro-labels.'.$table.'.store_id');

            $query = DB::table($table)
                ->select('store_id', ...$columns)
                ->whereIn('store_id', $storeIds);
            if ($table == 'mq_accounting') {
                $labelArr['year_month'] = trans('macro-labels.'.$table.'.year_month');
                $query->addSelect(DB::raw("CONCAT(`{$table}`.`year`, '-', LPAD(`{$table}`.`month`, 2, '0'), '-01') as `year_month`"));
            }

            foreach ($relativeTables as $tableName => $relativeTable) {
                $columnsRelativeTable = $this->getAllColumnOfTable($tableName)
                        ->map(fn ($item) => "{$item['table']}.{$item['column']}")
                        ->toArray();

                $this->handleJoinRelation($query, $table, $relativeTable);

                foreach ($columnsRelativeTable as $column) {
                    $labelArr[explode('.', $column)[1]] = trans('macro-labels.'.$column);
                    if ($table == 'mq_accounting') {
                        $columnName = explode('.', $column)[1];
                        $labelArr[$columnName.MacroConstant::ACCOUNTING_ACTUAL_COLUMN] = trans('macro-labels.'.$column).trans('macro-labels'.MacroConstant::ACCOUNTING_ACTUAL_COLUMN);
                        $labelArr[$columnName.MacroConstant::ACCOUNTING_DIFF_COLUMN] = trans('macro-labels.'.$column).trans('macro-labels'.MacroConstant::ACCOUNTING_DIFF_COLUMN);
                    }
                }
                $query->addSelect($columnsRelativeTable);
            }

            foreach ($conditionItems as $conditionItem) {
                $params = $this->handleWhereParams($conditionItem);

                if (empty($params)) {
                    continue;
                }

                $method = $conditionItem['operator'] == 'in' ? 'whereIn' : 'where';

                if (Arr::last($params) == 'between') {
                    $method = 'whereBetween';
                    array_pop($params);
                }

                $params[] = $boolean; // and|or

                $query->{$method}(...$params);
            }

            $result = $query->get();
            if ($table == 'mq_accounting') {
                $actualFilter = $result->map(function ($item) {
                    return [
                        'store_id' => $item->store_id,
                        'year' => $item->year,
                        'month' => $item->month,
                    ];
                })->toArray();

                $actualResult = $this->mqAccountingService->getMacroQueryResult($actualFilter)->get('data');
                $accountingResult = $result->map(function ($resultItem) use ($actualResult) {
                    $actualItem = $actualResult->filter(function ($item) use ($resultItem) {
                        return Arr::get($item, 'store_id') == $resultItem->store_id
                                && Arr::get($item, 'year') == $resultItem->year
                                && Arr::get($item, 'month') == $resultItem->month;
                    })->first();

                    $itemAttributes = get_object_vars($resultItem);
                    $newItem = [];
                    foreach ($itemAttributes as $key => $value) {
                        if (
                            $key != 'store_id'
                            && $key != 'year'
                            && $key != 'month'
                            && $key != 'year_month'
                        ) {
                            $different = '';
                            if (is_numeric($value)) {
                                $different = Arr::get($actualItem, $key) - $value;
                            }
                            $newItem[$key] = $value;
                            $newItem[$key.MacroConstant::ACCOUNTING_ACTUAL_COLUMN] = Arr::get($actualItem, $key);
                            $newItem[$key.MacroConstant::ACCOUNTING_DIFF_COLUMN] = $different;
                        } else {
                            $newItem[$key] = $value;
                        }
                    }

                    return (object) $newItem;
                });

                foreach ($columns as $column) {
                    $columnName = explode('.', $column)[1];
                    if (
                        $columnName != 'year'
                        && $columnName != 'month'
                    ) {
                        $labelArr[$columnName.MacroConstant::ACCOUNTING_ACTUAL_COLUMN] = trans('macro-labels.'.$column).trans('macro-labels'.MacroConstant::ACCOUNTING_ACTUAL_COLUMN);
                        $labelArr[$columnName.MacroConstant::ACCOUNTING_DIFF_COLUMN] = trans('macro-labels.'.$column).trans('macro-labels'.MacroConstant::ACCOUNTING_DIFF_COLUMN);
                    }
                }

                return collect([
                    'values' => $accountingResult,
                    'labels' => $labelArr,
                ]);
            }

            return collect([
                'values' => $result,
                'labels' => $labelArr,
            ]);
        } else {
            $conditions['conditions'] = array_map(function ($conditionItem) {
                if (Arr::has($conditionItem, 'date_condition')) {
                    $newDateCondition = $this->handleConditionContainingDate($conditionItem);
                    $conditionItem['field'] = $newDateCondition[0];
                    $conditionItem['operator'] = $newDateCondition[1];
                    $conditionItem['value'] = Carbon::create($newDateCondition[2])->format('Y/m/d');
                }

                return $conditionItem;
            }, $conditionItems);
            array_push($conditions['conditions'], [
                'field' => 'store_id',
                'operator' => 'in',
                'value' => implode(',', $storeIds),
            ]);

            $result = $this->schemaService->getQueryConditionsResult($conditions);
            $data = $result->get('data');

            $ossColumns = $this->macroService->getListTableOSS();
            if ($ossColumns->get('success')) {
                $tables = $ossColumns->get('data');
                foreach ($tables as $tableItem) {
                    foreach ($tableItem as $tableColumn) {
                        $table = Arr::get($tableColumn, 'table');
                        $column = Arr::get($tableColumn, 'column');
                        $labelArr[$table.'.'.$column] = Arr::get($tableColumn, 'label');
                    }
                }
            }

            return collect([
                'values' => $data,
                'labels' => $labelArr,
            ]);
        }

        return collect([
            'values' => [],
            'labels' => [],
        ]);
    }

    /**
     * Build accounting table, add actual, different to each column.
     */
    private function buildAccountingTableColumns($tableColumns): Collection
    {
        $result = $tableColumns;
        foreach ($tableColumns as $item) {
            $table = Arr::get($item, 'table');
            $column = Arr::get($item, 'column');
            if (
                $column != 'store_id'
                && $column != 'year'
                && $column != 'month'
                && $column != 'year_month'
            ) {
                $result->add([
                    'table' => $table,
                    'column' => $column.MacroConstant::ACCOUNTING_ACTUAL_COLUMN,
                    'type' => 'date',
                    'label' => trans('macro-labels.'.$table.'.'.$column).trans('macro-labels'.MacroConstant::ACCOUNTING_ACTUAL_COLUMN),
                ]);
                $result->add([
                    'table' => $table,
                    'column' => $column.MacroConstant::ACCOUNTING_DIFF_COLUMN,
                    'type' => 'date',
                    'label' => trans('macro-labels.'.$table.'.'.$column).trans('macro-labels'.MacroConstant::ACCOUNTING_DIFF_COLUMN),
                ]);
            }
        }

        return $result;
    }

    /**
     * Get object property value.
     */
    private function getNestedProperty($property, $object)
    {
        $itemAttributes = get_object_vars($object);

        return array_filter(
            $itemAttributes,
            fn ($key) => $key === $property,
            ARRAY_FILTER_USE_KEY
        );
    }
}
