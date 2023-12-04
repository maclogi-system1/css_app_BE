<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\MqSheetRepository;
use App\WebServices\OSS\ShopService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class GetInferenceStorePred36m extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-inference-store-pred36m
        {--generate-data= : Generate data for mq from AI results}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute 36-month sales forecast or insert data for mq from execution results';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('generate-data')) {
            $shops = $this->getShops();

            if (empty($shops)) {
                logger()->info('Command "GetInferenceStorePred36m" was executed but there were no stores.');

                return Command::FAILURE;
            }

            /** @var \App\Repositories\Contracts\MqSheetRepository */
            $mqSheetRepository = app(MqSheetRepository::class);

            foreach ($shops as $shop) {
                $storeId = Arr::get($shop, 'store_id');
                logger()->info("Creating data for {$storeId} store.");
                $mqSheetRepository->createDefault($storeId);
            }
        } else {
            $env = app()->environment('production') ? 'production' : 'staging';
            $url = config("ai.api_url.{$env}.store_pred_36m");
            $shops = $this->getShops();
            $totalShop = count($shops);

            logger()->info("Total forecast execution for {$totalShop} stores");

            foreach ($shops as $index => $shop) {
                $storeId = Arr::get($shop, 'store_id');
                $currentIndex = $index + 1;
                logger()->info("({$currentIndex}/{$totalShop}) Execute 36 months forecast for {$storeId} store.");

                $response = Http::post($url, [
                    'store_id' => $storeId,
                ]);

                $result = $response->json();

                if (Arr::get($result, 'statusCode', 400) == 200) {
                    logger()->info("36 month forecast execution for {$storeId} store completed.");
                } else {
                    logger()->error(
                        "36 month forecast execution for {$storeId} store failed.\n["
                        .Arr::get($result, 'errorMessage', '').']'
                    );
                }
            }
        }

        return Command::SUCCESS;
    }

    private function getShops(): array
    {
        /** @var \App\WebServices\OSS\ShopService */
        $shopService = app(ShopService::class);
        $shopResult = $shopService->getList([
            'per_page' => -1,
        ]);

        if ($shopResult->get('success')) {
            return $shopResult->get('data')->get('shops');
        }

        return [];
    }
}
