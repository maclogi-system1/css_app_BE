<?php

namespace App\Console\Commands;

use App\Jobs\CreateDefaultValueChains;
use App\WebServices\OSS\ShopService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CreateDefaultValueChainsForShops extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-default-value-chains-for-shops
        {--year-month= : YYYY-MM}
        {--storeid=* : Store id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create default data for each store's value chain on a monthly basis";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $storeIds = $this->option('storeid') ?? [];

        /** @var \App\WebServices\OSS\ShopService */
        $shopService = app(ShopService::class);
        $shopResult = $shopService->getList([
            'per_page' => -1,
            'filters' => [
                'shop_url' => implode(',', $storeIds),
            ],
        ]);
        $shops = [];

        if ($shopResult->get('success')) {
            $shops = $shopResult->get('data')->get('shops');
        }

        if (empty($shops)) {
            logger()->info('Command "CreateDefaultValueChainsForShops" was executed but there were no stores.');

            return Command::SUCCESS;
        }

        foreach ($shops as $shop) {
            if ($yearMonth = $this->option('year-month')) {
                $date = Carbon::create($yearMonth);
                CreateDefaultValueChains::dispatch($shop, $date);
                continue;
            }

            CreateDefaultValueChains::dispatch($shop);
        }
    }
}
