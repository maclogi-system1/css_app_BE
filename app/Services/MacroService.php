<?php

namespace App\Services;

use App\Constants\MacroConstant;
use App\Services\OSS\OSSService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class MacroService extends Service
{
    /**
     * Get list table by storeId.
     */
    public function getListTableByStoreId(string $storeId): array
    {
        $tables = [];
        $projectId = null;
        $getProject = false;
        foreach (MacroConstant::LIST_RELATIVE_TABLE as $tableName => $relativeTable) {
            if (MacroConstant::DESCRIPTION_TABLES[$tableName][MacroConstant::TABLE_TYPE] == MacroConstant::TYPE_INTERNAL) {
                // Check data for internal system
                $dataExist = DB::table($tableName)->where('store_id', '=', $storeId)->exists();
            } else {
                // Check shop exist in OSS system
                $dataExist = false;
                if (! $getProject) {
                    $projectId = $this->getProjectIdFromStoreIdOSSSystem($storeId);
                    $getProject = true;
                }
                if ($projectId) {
                    $dataExist = $this->checkProjectIdExistInTableOSSSystem($projectId, $tableName);
                }
            }

            if ($dataExist) {
                $tables[$tableName] = $this->getAllColumnOfTableAndRelativeTable($relativeTable);
            }
        }

        return $tables;
    }

    /**
     * Get all column of table and relative table.
     */
    private function getAllColumnOfTableAndRelativeTable(array $relativeTable, bool $removeUnusedColumn = true): array
    {
        $result = [];
        $tableName = $relativeTable[MacroConstant::TABLE_NAME];
        $result[MacroConstant::TABLE_NAME] = $relativeTable[MacroConstant::TABLE_NAME];
        $result[MacroConstant::TABLE_COLUMNS] = $this->getColumnAndTypeOfTable(
            $relativeTable[MacroConstant::TABLE_NAME],
            MacroConstant::DESCRIPTION_TABLES[$tableName][MacroConstant::TABLE_TYPE] == MacroConstant::TYPE_INTERNAL,
        );

        $this->copyAttribute($result, MacroConstant::DESCRIPTION_TABLES[$tableName], MacroConstant::TABLE_TYPE);
        $this->copyAttribute($result, $relativeTable, MacroConstant::RELATIVE_TABLE_FOREIGN_KEY);
        $this->copyAttribute($result, $relativeTable, MacroConstant::RELATIVE_TABLE_FOREIGN_KEY_TYPE);
        $this->copyAttribute($result, MacroConstant::DESCRIPTION_TABLES[$tableName], MacroConstant::REMOVE_COLUMNS);

        // Remove unused column
        if ($removeUnusedColumn) {
            $this->removeUnusedColumn($result);
        }

        // Get for relative table
        if (isset($relativeTable[MacroConstant::RELATIVE_TABLES])) {
            foreach ($relativeTable[MacroConstant::RELATIVE_TABLES] as $relativeTable) {
                $relativeTableResult = $this->getAllColumnOfTableAndRelativeTable($relativeTable);
                $result[MacroConstant::RELATIVE_TABLES][$relativeTable[MacroConstant::TABLE_NAME]] = $relativeTableResult;
            }
        }

        return $result;
    }

    /**
     * Remove unused column.
     */
    private function removeUnusedColumn(array &$table): void
    {
        if (isset($table[MacroConstant::REMOVE_COLUMNS]) && isset($table[MacroConstant::TABLE_COLUMNS])) {
            foreach ($table[MacroConstant::TABLE_COLUMNS] as $columnName => $type) {
                if (in_array($columnName, $table[MacroConstant::REMOVE_COLUMNS])) {
                    unset($table[MacroConstant::TABLE_COLUMNS][$columnName]);
                }
            }
        }
    }

    /**
     * Copy attribute from inputArr to outputArr.
     */
    private function copyAttribute(array &$outputArr, array $inputArr, string $attribute): void
    {
        if (isset($inputArr[$attribute])) {
            $outputArr[$attribute] = $inputArr[$attribute];
        }
    }

    /**
     * Get column name and type of table.
     */
    private function getColumnAndTypeOfTable(string $tableName, bool $cssSystem = true): array
    {
        $columnDetails = [];
        if ($cssSystem) {
            $columns = Schema::getColumnListing($tableName);
            foreach ($columns as $column) {
                $columnType = Schema::getColumnType($tableName, $column);
                $columnDetails[$column] = $columnType;
            }
        } else {
            $columnDetails = json_decode(Http::oss()->get(OSSService::getApiUri('schema.get_columns'), ['table_name' => $tableName]), true);
        }

        return $columnDetails;
    }

    /**
     * Get project data from storeId.
     */
    private function getProjectInfoFromStoreIdOSSSystem(string $storeId): ?array
    {
        $response = Http::oss()->get(OSSService::getApiUri('shops.detail', $storeId), ['is_load_relation' => 0]);

        if (isset($response['data'])) {
            return $response['data'];
        }

        return null;
    }

    /**
     * Get projectId form storeId in OSS system.
     */
    private function getProjectIdFromStoreIdOSSSystem(string $storeId): ?string
    {
        $project = $this->getProjectInfoFromStoreIdOSSSystem($storeId);

        if ($project) {
            return $project['id'];
        }

        return null;
    }

    /**
     * Check data exist with projectId in table OSS system.
     */
    private function checkProjectIdExistInTableOSSSystem(string $projectId, string $tableName): bool
    {
        $response = Http::oss()->get(
            OSSService::getApiUri('schema.check_exist_with_store'),
            ['project_id' => $projectId, 'table_name' => $tableName],
        );

        if (isset($response['data'])) {
            return $response['data']['is_existed'];
        }

        return false;
    }
}
