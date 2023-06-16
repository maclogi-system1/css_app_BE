<?php

namespace App\Services\OSS;

use App\Services\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class TaskService extends Service
{
    /**
     * Get a listing of the task using the OSS api.
     */
    public function getList(array $filter = []): Collection
    {
        return $this->fakeTasks();
        // return $this->toResponse(Http::oss()->get(OSSService::getApiUri('tasks.list'), $filter));
    }

    /**
     * Create a fake response list of tasks.
     *
     * @deprecated Temporary method to get list of tasks while waiting for OSS to provide api.
     * This method is for temporary use only during development and will be removed when OSS provides the api.
     */
    private function fakeTasks(): Collection
    {
        return collect([
            'status' => 200,
            'data' => [
                'tasks' => [
                    [
                        'id' => 3,
                        'title' => '【テンプレあり】バナー作成',
                        'description' => null,
                        'status_id' => -10,
                        'status_name' => '提案前',
                        'key' => 'SP OFOH-18',
                        'start_date' => '2023-06-16 16:59:16',
                        'shop' => [
                            'id' => 2,
                            'name' => 'shop for test',
                            'created_at' => '2023-02-21T08:31:46.000000Z',
                            'updated_at' => '2023-04-10T04:49:26.000000Z'
                        ],
                        'created_at' => '2023-03-23T03:17:46.000000Z',
                        'updated_at' => '2023-03-27T10:17:43.000000Z'
                    ],
                    [
                        'id' => 27,
                        'title' => '【shop for test】dsdsd【テンプレあり】バナー作成',
                        'description' => 'ジョブグループURL: http://task-management.test:8081/shop/2/job-group/17\n対象ジョブ名: dsdsd',
                        'status_id' => -10,
                        'status_name' => '提案前',
                        'key' => 'SHOP FO-40',
                        'start_date' => '2023-06-16 17:13:00',
                        'shop' => [
                            'id' => 2,
                            'name' => 'shop for test',
                            'created_at' => '2023-02-21T08:31:46.000000Z',
                            'updated_at' => '2023-04-10T04:49:26.000000Z'
                        ],
                        'created_at' => '2023-05-29T04:29:54.000000Z',
                        'updated_at' => '2023-06-09T10:59:12.000000Z'
                    ],
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'to' => 2,
                    'total' => 2
                ],
            ],
        ]);
    }
}
