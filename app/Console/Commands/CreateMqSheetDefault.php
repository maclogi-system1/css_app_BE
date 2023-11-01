<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\MqSheetRepository;
use App\WebServices\OSS\ShopService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class CreateMqSheetDefault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-mq-sheet-default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a default mq_sheet for each shop';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $storeIds = $this->getStoreIds();
        $this->handleCreateDefaultMqSheets($storeIds);

        return Command::SUCCESS;
    }

    /**
     * Get a list of all shops from OSS, then return a list of store_id.
     */
    private function getStoreIds(): array
    {
        /** @var \App\WebServices\OSS\ShopService */
        $shopService = app(ShopService::class);

        $result = $shopService->getList([
            'with' => ['shopCredential'],
            'per_page' => -1,
        ]);

        if ($result->get('success')) {
            $shops = $result->get('data')->get('shops');
            $storeIds = Arr::pluck($shops, 'store_id');
        }

        $storeIds = [];

        return $storeIds;
    }

    /**
     * Handles the creation of new default mq_sheets from the storeIds list.
     */
    private function handleCreateDefaultMqSheets(array $storeIds): void
    {
        /** @var \App\Repositories\Contracts\MqSheetRepository */
        $mqSheetRepository = app(MqSheetRepository::class);

        foreach ($storeIds as $storeId) {
            $mqSheet = $mqSheetRepository->createDefault($storeId);

            if (is_null($mqSheet)) {
                logger()->error("Created default sheet for {$storeId} failed.");
            }
        }
    }
}
