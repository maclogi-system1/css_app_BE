<?php

namespace App\Console\Commands;

use App\Jobs\CreateDefaultValueChains;
use App\WebServices\OSS\ShopService;
use Illuminate\Console\Command;

class CreateDefaultValueChainsForShops extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-default-value-chains-for-shops';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var \App\WebServices\OSS\ShopService */
        $shopService = app(ShopService::class);
        $shopResult = $shopService->getList(['per_page' => -1]);
        $shops = [];

        if ($shopResult->get('success')) {
            $shops = $shopResult->get('data')->get('shops');
        }

        foreach ($shops as $shop) {
            CreateDefaultValueChains::dispatch($shop);
        }
    }
}
