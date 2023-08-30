<?php

namespace App\WebServices;

use App\WebServices\OSS\OSSService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class MacroService extends Service
{
    /**
     * Get project data from storeId.
     */
    public function getProjectInfoFromStoreIdOSSSystem(string $storeId): Collection
    {
        return $this->toResponse(Http::oss()->get(
            OSSService::getApiUri('shops.detail', $storeId),
            ['is_load_relation' => 0]
        ));
    }

    /**
     * Check data exist with projectId in table OSS system.
     */
    public function checkProjectIdExistInTableOSSSystem(string $projectId, string $tableName): Collection
    {
        return $this->toResponse(Http::oss()->get(
            OSSService::getApiUri('schema.check_exist_with_store'),
            ['project_id' => $projectId, 'table_name' => $tableName],
        ));
    }

    public function getColumnTableOSS(string $tableName)
    {
        return $this->toResponse(Http::oss()->get(
            OSSService::getApiUri('schema.get_columns'),
            ['table_name' => $tableName]
        ));
    }
}
